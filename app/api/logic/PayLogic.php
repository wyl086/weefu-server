<?php

namespace app\api\logic;

use app\admin\logic\distribution\DistributionLevelLogic;
use app\common\basics\Logic;
use app\common\enum\NoticeEnum;
use app\common\enum\OrderEnum;
use app\common\enum\OrderLogEnum;
use app\common\enum\PayEnum;
use app\common\logic\GoodsVirtualLogic;
use app\common\logic\IntegralLogic;
use app\common\logic\OrderLogLogic;
use app\common\logic\PayNotifyLogic;
use app\common\model\Client_;
use app\common\model\distribution\DistributionGoods;
use app\common\model\distribution\DistributionLevel;
use app\common\model\distribution\DistributionOrderGoods;
use app\common\model\goods\Goods;
use app\common\model\AccountLog;
use app\common\logic\AccountLogLogic;
use app\common\model\integral\IntegralOrder;
use app\common\model\order\OrderGoods;
use app\common\model\order\OrderTrade;
use app\common\model\order\Order;
use app\common\model\RechargeOrder;
use app\common\model\shop\Shop;
use app\common\model\user\User;
use app\common\server\JsonServer;
use app\common\server\WeChatPayServer;
use app\common\server\ConfigServer;
use think\Exception;
use think\facade\Db;
use app\common\server\AliPayServer;

/**
 * Class PayLogic
 * @package app\api\logic
 */
class PayLogic extends Logic
{
    /**
     * @notes 检验支付状态
     * @param $trade_id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/13 6:24 下午
     */
    public static function checkPayStatus($trade_id)
    {

        $where = [
            'trade_id' => $trade_id,
            'pay_status' => PayEnum::ISPAID,
            'del' => 0
        ];

        $check = Order::where($where)->find();
        if ($check) {
            return true;
        }
        return false;

    }

    /**
     * @notes 余额支付
     * @param $order_id
     * @param $form
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\PDOException
     * @author suny
     * @date 2021/7/13 6:24 下午
     */
    public static function balancePay($order_id, $form)
    {

        switch ($form) {
            case "trade":
                $order = OrderTrade::find($order_id);
                if (self::checkPayStatus($order_id)) {
                    $order['pay_status'] = PayEnum::ISPAID;
                }
                $order_mobiles = Order::where('trade_id', $order_id)->column('mobile');
                break;
            case "order":
                $order = Order::where([
                    ['del', '=', 0],
                    ['id', '=', $order_id]
                ])->find();
                $order_mobiles = [ $order['mobile'] ?? '' ];
                break;
            case "integral":
                $order = IntegralOrder::where(['del' => 0, 'id' => $order_id])->find();
                $order_mobiles = [ $order['mobile'] ?? '' ];
                break;
        }
        if (empty($order)) {
            return JsonServer::error('订单不存在');
        }
        if (isset($order['pay_status']) && $order['pay_status'] == PayEnum::ISPAID) {
            return JsonServer::error('订单已支付');
        }
        $user_balance = User::where(['id' => $order['user_id']])->value('user_money');
        if ($user_balance < $order['order_amount']) {
            return JsonServer::error('余额不足');
        }

        // 积分订单
        if($form == 'integral') {
            $result = self::payIntegralOrder($order);
            if (true === $result) {
                return JsonServer::success('支付成功', [], 20001);
            }
            return JsonServer::error($result);
        }

        Db::startTrans();
        try {
            $User = new User();
            if ($order['order_amount'] != 0) {
                $user_balance_dec = User::where(['id' => $order['user_id']])
                    ->dec('user_money', $order['order_amount'])
                    ->update();
                if (!$user_balance_dec) {
                    Db::rollback();
                    return JsonServer::error('余额扣除失败');
                }
            }

            //记录余额
            $acountLogResult = AccountLogLogic::AccountRecord($order['user_id'], $order['order_amount'], 2, AccountLog::balance_pay_order);
            if ($acountLogResult === false) {
                Db::rollback();
                return JsonServer::error('账户明细记录添加失败');
            }

            if ($form == "trade") {
                $order_id = Order::where('trade_id', $order_id)->column('id');
            }
            $orderStatusChange = self::changOrderStatus($order_id);
            if ($orderStatusChange == false) {
                Db::rollback();
                return JsonServer::error('子订单状态改变失败');
            }

            // 生成分销订单
            self::distributionOrderGoods($order_id);
            // 更新分销会员等级
            DistributionLevelLogic::updateDistributionLevel($order['user_id']);

            if($form == "trade"){
                $orders = Order::where([['id','in',$order_id]])->select();
                foreach ($orders as $item) {
                    // 增加商品销量
                    $order_goods = OrderGoods::where('order_id', $item['id'])->select()->toArray();
                    foreach ($order_goods as $order_good) {
                        Goods::where('id', $order_good['goods_id'])
                            ->inc('sales_actual', $order_good['goods_num'])
                            ->update();
                    }

                    //修改用户消费累计额度
                    $user = User::find($item['user_id']);
                    $user->total_order_amount = ['inc', $item['order_amount']];
                    $user->save();

                    //赠送成长值
                    $growth_ratio = ConfigServer::get('transaction', 'money_to_growth', 0);
                    if ($growth_ratio > 0) {
                        $able_get_growth = floor($item['total_amount'] / $growth_ratio);
                        $user->where('id', $item['user_id'])
                            ->inc('user_growth', $able_get_growth)
                            ->update();
                        AccountLogLogic::AccountRecord($item['user_id'], $able_get_growth, 1, AccountLog::order_give_growth, '', $item['id'], $item['order_sn']);
                    }


                    //赠送积分
                    $open_award = ConfigServer::get('order_award', 'open_award', 0);
                    if ($open_award == 1) {
                        $award_event = ConfigServer::get('order_award', 'award_event', 0);
                        $award_ratio = ConfigServer::get('order_award', 'award_ratio', 0);
                        if ($award_ratio > 0) {
                            $award_integral = floor($item['order_amount'] * ($award_ratio / 100));
                        }
                    }
                    Order::update(['award_integral_status' => $award_event ?? 0,'award_integral' => $award_integral ?? 0],['id'=>$item['id']]);

                    //通知用户
                    foreach ($order_mobiles ?? [] as $order_mobile) {
                        event('Notice', [
                            'scene' => NoticeEnum::ORDER_PAY_NOTICE,
                            'mobile' => $order_mobile,
                            'params' => ['order_id' => $item['id'], 'user_id' => $item['user_id']]
                        ]);
                    }
                    

                    //通知商家
                    if (!empty($item['shop']['mobile'])) {
                        event('Notice', [
                            'scene' => NoticeEnum::USER_PAID_NOTICE_SHOP,
                            'mobile' => $item['shop']['mobile'],
                            'params' => ['order_id' => $item['id'], 'user_id' => $item['user_id']]
                        ]);
                    }
                    // 打印小票
                    event('Printer', [
                        'order_id' => $item['id'],
                    ]);

                    OrderLogLogic::record(
                        OrderLogEnum::TYPE_USER,
                        OrderLogEnum::USER_PAID_ORDER,
                        $item['id'],
                        $item['user_id'],
                        OrderLogEnum::getLogDesc(OrderLogEnum::USER_PAID_ORDER)
                    );
                }

                // 虚拟商品更新订单信息
                GoodsVirtualLogic::afterPayVirtualDelivery($order_id);

            } else{
                // 增加商品销量
                $order_goods = OrderGoods::where('order_id', $order['id'])->select();
                foreach ($order_goods as $order_good) {
                    Goods::where('id', $order_good['goods_id'])
                        ->inc('sales_actual', $order_good['goods_num'])
                        ->update();
                }

                //修改用户消费累计额度
                $user = User::find($order['user_id']);
                $user->total_order_amount = ['inc', $order['order_amount']];
                $user->save();

                //赠送成长值
                $growth_ratio = ConfigServer::get('transaction', 'money_to_growth', 0);
                if ($growth_ratio > 0) {
                    $able_get_growth = floor($order['total_amount'] / $growth_ratio);
                    $user->where('id', $order['user_id'])
                        ->inc('user_growth', $able_get_growth)
                        ->update();
                    AccountLogLogic::AccountRecord($order['user_id'], $able_get_growth, 1, AccountLog::order_give_growth, '', $order['id'], $order['order_sn']);
                }


                //赠送积分
                $open_award = ConfigServer::get('order_award', 'open_award', 0);
                if ($open_award == 1) {
                    $award_event = ConfigServer::get('order_award', 'award_event', 0);
                    $award_ratio = ConfigServer::get('order_award', 'award_ratio', 0);
                    if ($award_ratio > 0) {
                        $award_integral = floor($order['order_amount'] * ($award_ratio / 100));
                    }
                }
                Order::update(['award_integral_status' => $award_event ?? 0,'award_integral' => $award_integral ?? 0],['id'=>$order['id']]);

                // 虚拟商品更新订单信息
                GoodsVirtualLogic::afterPayVirtualDelivery($order['id']);

                //通知用户
                foreach ($order_mobiles ?? [] as $order_mobile) {
                    event('Notice', [
                        'scene' => NoticeEnum::ORDER_PAY_NOTICE,
                        'mobile' => $order_mobile,
                        'params' => ['order_id' => $order['id'], 'user_id' => $order['user_id']]
                    ]);
                }

                //通知商家
                if (!empty($order['shop']['mobile'])) {
                    event('Notice', [
                        'scene' => NoticeEnum::USER_PAID_NOTICE_SHOP,
                        'mobile' => $order['shop']['mobile'],
                        'params' => ['order_id' => $order['id'], 'user_id' => $order['user_id']]
                    ]);
                }

                // 打印小票
                event('Printer', [
                    'order_id' => $order['id'],
                ]);

                OrderLogLogic::record(
                    OrderLogEnum::TYPE_USER,
                    OrderLogEnum::USER_PAID_ORDER,
                    $order['id'],
                    $order['user_id'],
                    OrderLogEnum::getLogDesc(OrderLogEnum::USER_PAID_ORDER)
                );
            }

            Db::commit();
            return JsonServer::success('支付成功', [], 20001);
        } catch (\Exception $e) {
            Db::rollback();
            return JsonServer::error($e->getMessage());
        }
    }

    /**
     * @notes 微信支付
     * @param $order_id
     * @param $form
     * @param $client
     * @return \think\response\Json
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/13 6:24 下午
     */
    public static function wechatPay($order_id, $form, $client)
    {
        switch ($form) {
            case "trade":
                $order = OrderTrade::find($order_id);
                if (self::checkPayStatus($order_id)) {
                    $order['pay_status'] = PayEnum::ISPAID;
                }
                break;
            case "order":
                $order = Order::where([
                    ['del', '=', 0],
                    ['id', '=', $order_id]
                ])->find();
                break;
            case "recharge":
                $order = RechargeOrder::where([
                    ['id', '=', $order_id]
                ])->find();
                break;
            case "integral":
                $order = IntegralOrder::where(['del' => 0, 'id' => $order_id])->find();
                break;
        }
        if (empty($order)) {
            return JsonServer::error('订单不存在');
        }
        if (isset($order['pay_status']) && $order['pay_status'] == PayEnum::ISPAID) {
            return JsonServer::error('订单已支付');
        }
        // 这里进行微信支付
        $res = WeChatPayServer::unifiedOrder($form, $order, $client);
        if (false === $res) {
            return JsonServer::error(WeChatPayServer::getError());
        }
        if ((is_object($res) || is_string($res)) && $client != Client_::pc) {
            $res = (array)($res);
        }
        if (is_string($res)) {
            $data = [
                'code' => 1,
                'msg' => '微信支付发起成功',
                'show' => 0,
                'data' => $res
            ];
            return json($data);
        }
        return JsonServer::success('微信支付发起成功', $res, 1);
    }

    /**
     * @notes 支付宝支付
     * @param $order_id
     * @param $from
     * @param $client
     * @return bool|string
     * @author suny
     * @date 2021/7/27 4:22 下午
     */
    public static function aliPay($order_id , $from , $client)
    {
        $aliPay = new AliPayServer();
        $res = $aliPay->pay($from , $order_id , $client);
        return $res;
    }

    /**
     * order表状态改变
     */
    public static function changOrderStatus($order_id)
    {
        $where = ['id', '=', $order_id];
        if (is_array($order_id)) {
            $where = ['id', 'in', $order_id];
        }

        $orders = Order::where([ $where ])
            ->update([
                'pay_status' => PayEnum::ISPAID,
                'order_status' => OrderEnum::ORDER_STATUS_DELIVERY,
                'pay_way' => PayEnum::BALANCE_PAY,
                'pay_time' => time()
            ]);

        if ($orders) {
            return true;
        }
        return false;
    }

    /**
     * @notes 分销逻辑
     * @param $order_id
     * @param $user_id
     * @author Tab
     * @date 2021/9/2 10:48
     */
    public static function distributionOrderGoods($order_id)
    {
        if (is_array($order_id)) {
            $orderIds = $order_id;
        } else {
            $orderIds = [$order_id];
        }

        // 获取分销配置
        $distributionConfig = [
            'is_open' => ConfigServer::get('distribution', 'is_open', 0),
            'level' => ConfigServer::get('distribution', 'level', 2),
        ];
        if(!$distributionConfig['is_open']) {
            return false;
        }
        // 遍历订单
        foreach($orderIds as $orderId) {
            // 用户信息
            $order = Order::where('id', $orderId)->findOrEmpty()->toArray();
            $userInfo = self::userInfo($order['user_id']);
            // 判断店铺是否开通分销
            $isDistribution = Shop::where('id', $order['shop_id'])->value('is_distribution');
            if(!$isDistribution) {
                // 未开通分销跳过
                continue;
            }

            // 订单信息
            $orderInfo = self::orderInfo($orderId);
            // 遍历子订单
            foreach($orderInfo as $item) {
                // 判断商品是否参与分销
                $goodsDistribution = self::checkGoodsDistribution($item['goods_id']);
                if(empty($goodsDistribution) || !$goodsDistribution['is_distribution']) {
                    // 商品未参与分销
                    continue;
                }
                // 分销层级
                switch($distributionConfig['level'])
                {
                    case 1: // 一级分销
                        self::firstLevelCommission($userInfo, $item, $goodsDistribution);
                        break;
                    case 2: // 一级、二级分销
                        self::firstLevelCommission($userInfo, $item, $goodsDistribution);
                        self::secondLevelCommission($userInfo, $item, $goodsDistribution);
                        break;
                }
            }
        }

    }

    /**
     * @notes 用户信息
     * @param $userId
     * @return mixed
     * @author Tab
     * @date 2021/9/2 11:02
     */
    public static function userInfo($userId)
    {
        $userInfo = User::alias('u')
            ->leftJoin('distribution d', 'd.user_id = u.id')
            ->field('u.id,u.nickname,u.first_leader,u.second_leader,d.level_id,d.is_distribution,d.is_freeze')
            ->where('u.id', $userId)
            ->findOrEmpty()
            ->toArray();

        return $userInfo;
    }

    /**
     * @notes 订单信息
     * @param $orderId
     * @return mixed
     * @author Tab
     * @date 2021/9/2 11:05
     */
    public static function orderInfo($orderId)
    {
        $orderInfo = OrderGoods::alias('og')
            ->leftJoin('order o', 'o.id = og.order_id')
            ->field('og.id as order_goods_id,og.order_id,og.goods_id,og.item_id,og.goods_num,og.shop_id,og.total_pay_price,o.user_id')
            ->where('og.order_id', $orderId)
            ->select()
            ->toArray();
        return $orderInfo;
    }

    /**
     * @notes 校验商品是否参与分销
     * @param $goodsId
     * @return array
     * @author Tab
     * @date 2021/9/2 11:08
     */
    public static function checkGoodsDistribution($goodsId)
    {
        $distributionGoods = DistributionGoods::field('goods_id,item_id,level_id,first_ratio,second_ratio,is_distribution,rule')
            ->where('goods_id', $goodsId)
            ->select()
            ->toArray();

        if(empty($distributionGoods)) {
            return [];
        }

        return [
            'goods_id' => $distributionGoods[0]['goods_id'],
            'is_distribution' => $distributionGoods[0]['is_distribution'],
            'rule' => $distributionGoods[0]['rule']
        ];
    }

    /**
     * @notes 一级分佣
     * @param $userInfo
     * @param $item
     * @param $goodsDistribution
     * @return false
     * @author Tab
     * @date 2021/9/2 11:12
     */
    public static function firstLevelCommission($userInfo, $item, $goodsDistribution)
    {
        if(!$userInfo['first_leader']) {
            // 没有上级，无需分佣
            return false;
        }
        $firstLeaderInfo = self::userInfo($userInfo['first_leader']);
        if(!$firstLeaderInfo['is_distribution'] || $firstLeaderInfo['is_freeze']) {
            // 上级不是分销会员 或 分销资格已冻结
            return false;
        }

        $ratioArr = self::getRatio($goodsDistribution, $item, $firstLeaderInfo);
        $firstLeaderInfo['ratio'] = $ratioArr['first_ratio'];
        $firstLeaderInfo['level'] = 1;
        self::addDistributionOrderGoods($item, $firstLeaderInfo);
    }

    /**
     * @notes 获取分佣比例
     * @param $goodsDistribution
     * @param $item
     * @param $userInfo
     * @return array
     * @author Tab
     * @date 2021/9/2 11:14
     */
    public static function getRatio($goodsDistribution, $item, $userInfo)
    {
        // 按分销会员等级对应的比例
        if($goodsDistribution['rule'] == 1) {
            $ratioArr = DistributionLevel::field('first_ratio,second_ratio')
                ->where('id', $userInfo['level_id'])
                ->findOrEmpty()
                ->toArray();
            return $ratioArr;
        }

        // 单独设置的比例
        if($goodsDistribution['rule'] == 2) {
            $ratioArr = DistributionGoods::field('first_ratio,second_ratio')
                ->where([
                    'goods_id' => $item['goods_id'],
                    'item_id' => $item['item_id'],
                    'level_id' => $userInfo['level_id']
                ])
                ->findOrEmpty()
                ->toArray();
            return $ratioArr;
        }
    }

    /**
     * @notes 二级分佣
     * @param $userInfo
     * @param $item
     * @param $goodsDistribution
     * @return false
     * @author Tab
     * @date 2021/9/2 11:39
     */
    public static function secondLevelCommission($userInfo, $item, $goodsDistribution)
    {
        if(!$userInfo['second_leader']) {
            // 没有上上级，无需分佣
            return false;
        }
        $secondLeaderInfo = self::userInfo($userInfo['second_leader']);
        if(!$secondLeaderInfo['is_distribution'] || $secondLeaderInfo['is_freeze']) {
            // 上上级不是分销会员 或 分销资格已冻结
            return false;
        }

        $ratioArr = self::getRatio($goodsDistribution, $item, $secondLeaderInfo);
        $secondLeaderInfo['ratio'] = $ratioArr['second_ratio'];
        $secondLeaderInfo['level'] = 2;
        self::addDistributionOrderGoods($item, $secondLeaderInfo);
    }

    /**
     * @notes 生成分销订单
     * @param $item
     * @param $userInfo
     * @return bool|void
     * @author Tab
     * @date 2021/9/2 11:17
     */
    public static function addDistributionOrderGoods($item, $userInfo)
    {
        $earnings = self::calByPaymentAmount($item, $userInfo);
        if($earnings < 0.01) {
            return false;
        }
        $data = [
            'sn' => createSn('distribution_order_goods', 'sn'),
            'user_id' => $userInfo['id'],
            'real_name' => $userInfo['nickname'],
            'level_id' => $userInfo['level_id'],
            'level' => $userInfo['level'],
            'ratio' => $userInfo['ratio'],
            'order_id' => $item['order_id'],
            'order_goods_id' => $item['order_goods_id'],
            'goods_id' => $item['goods_id'],
            'goods_num' => $item['goods_num'],
            'item_id' => $item['item_id'],
            'shop_id' => $item['shop_id'],
            'money' => $earnings,
            'status' => 1
        ];

        DistributionOrderGoods::create($data);
        
        // 预估佣金通知
        event('Notice', [
            'scene'     => NoticeEnum::GET_FUTURE_EARNINGS_NOTICE,
            'mobile'    => User::where('id', $userInfo['id'])->value('mobile', ''),
            'params'    => [
                'user_id'           => $userInfo['id'],
                'future_earnings'   => $earnings,
            ]
        ]);
    }

    /**
     * @notes 根据商品实际支付金额计算佣金
     * @param $item
     * @param $userInfo
     * @return float
     * @author Tab
     * @date 2021/9/2 11:18
     */
    public static function calByPaymentAmount($item, $userInfo)
    {
        $earnings = round(($item['total_pay_price'] * $userInfo['ratio'] / 100), 2);
        return $earnings;
    }

    /**
     * @notes 分销商品记录
     * @param $order_id
     * @param $user_id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author suny
     * @date 2021/7/13 6:24 下午
     */
    public static function distributionOrderGoodsOld($order_id, $user_id)
    {
        $where = ['order_id', '=', $order_id];
        if (is_array($order_id)) {
            $where = ['order_id', 'in', $order_id];
        }

        $goods = OrderGoods::where([ $where ])
            ->field('id,goods_id,total_pay_price,goods_num,order_id,shop_id')
            ->select()->toArray();

        $User = new User();
        $Goods = new Goods();
        $user_leader = $User->where('id', $user_id)->field('first_leader,second_leader,third_leader')->find()->toArray();

        foreach ($goods as $key => $value) {
            // 商家是否开启分销
            $shop_is_distribution = Shop::where('id', $value['shop_id'])->value('is_distribution');

            if (!$shop_is_distribution) continue;
            // 商品是否开启分销
            if ($Goods->where(['id' => $value['goods_id'], 'is_distribution' => 1])->find()) {
                $goods_distribution = $Goods->where(['id' => $value['goods_id']])->field('first_ratio,second_ratio,third_ratio')->find()->toArray();

                if (!empty($user_leader['first_leader']) && !is_null($user_leader['first_leader'])) {
                    DistributionOrderGoods::create([
                        'sn' => createSn('distribution_order_goods', 'sn'),
                        'user_id' => $user_leader['first_leader'],
                        'real_name' => $User->where('id', $user_leader['first_leader'])->value('nickname') ?? '',
                        'order_id' => $value['order_id'],
                        'order_goods_id' => $value['id'],
                        'goods_num' => $value['goods_num'],
                        'money' => bcdiv(bcmul($value['total_pay_price'], $goods_distribution['first_ratio'], 2), 100, 2),
                        'status' => 1,
                        'shop_id' => $value['shop_id'],
                        'create_time' => time()
                    ]);
                }

                if (!empty($user_leader['second_leader']) && !is_null($user_leader['second_leader'])) {
                    DistributionOrderGoods::create([
                        'sn' => createSn('distribution_order_goods', 'sn'),
                        'user_id' => $user_leader['second_leader'],
                        'real_name' => $User->where('id', $user_leader['second_leader'])->value('nickname') ?? '',
                        'order_id' => $value['order_id'],
                        'order_goods_id' => $value['id'],
                        'goods_num' => $value['goods_num'],
                        'money' => bcdiv(bcmul($value['total_pay_price'], $goods_distribution['second_ratio'], 2), 100, 2),
                        'status' => 1,
                        'shop_id' => $value['shop_id'],
                        'create_time' => time()
                    ]);
                }

                if (!empty($user_leader['third_leader']) && !is_null($user_leader['third_leader'])) {
                    DistributionOrderGoods::create([
                        'sn' => createSn('distribution_order_goods', 'sn'),
                        'user_id' => $user_leader['third_leader'],
                        'real_name' => $User->where('id', $user_leader['third_leader'])->value('nickname') ?? '',
                        'order_id' => $value['order_id'],
                        'order_goods_id' => $value['id'],
                        'goods_num' => $value['goods_num'],
                        'money' => bcdiv(bcmul($value['total_pay_price'], $goods_distribution['third_ratio'], 2), 100, 2),
                        'status' => 1,
                        'shop_id' => $value['shop_id'],
                        'create_time' => time()
                    ]);
                }
            }
        }

        return true;
    }



    /**
     * @notes 余额支付积分订单
     * @param $order
     * @return bool|string
     * @author 段誉
     * @date 2022/3/1 15:02
     */
    public static function payIntegralOrder($order)
    {
        try {
            if ($order['order_amount'] != 0) {
                User::where(['id' => $order['user_id']])
                    ->dec('user_money', $order['order_amount'])
                    ->update();

                AccountLogLogic::AccountRecord(
                    $order['user_id'],
                    $order['order_amount'],
                    2,
                    AccountLog::integral_order_dec_balance,
                    '',
                    $order['id'],
                    $order['order_sn']
                );
            }

            $result = PayNotifyLogic::handle('integral', $order['order_sn']);
            if (true !== $result) {
                throw new \Exception($result);
            }
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }


}
