<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\common\logic;

use app\admin\logic\distribution\DistributionLevelLogic;
use app\api\logic\PayLogic;
use app\common\enum\IntegralGoodsEnum;
use app\common\enum\IntegralOrderEnum;
use app\common\enum\NoticeEnum;
use app\common\model\{AccountLog,
    goods\Goods,
    integral\IntegralGoods,
    integral\IntegralOrder,
    order\OrderGoods,
    RechargeOrder,
    shop\Shop};
use app\common\enum\OrderEnum;
use app\common\enum\OrderLogEnum;
use app\common\enum\PayEnum;
use app\common\model\order\Order;
use app\common\model\order\OrderTrade;
use app\common\server\DistributionServer;
use app\common\model\order\OrderLog;
use app\common\model\user\User;
use app\common\server\ConfigServer;
use think\facade\Db;
use think\Exception;
use think\facade\Log;

/**
 * 支付成功后处理订单状态
 * Class PayNotifyLogic
 * @package app\api\logic
 */
class PayNotifyLogic
{
    /**
     * @notes 回调处理
     * @param $action
     * @param $order_sn
     * @param array $extra
     * @return bool|string
     * @throws \think\exception\PDOException
     * @author suny
     * @date 2021/7/13 6:32 下午
     */
    public static function handle($action, $order_sn, $extra = [])
    {

        Db::startTrans();
        try {
            self::$action($order_sn, $extra);
            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            $record = [
                __CLASS__, __FUNCTION__, $e->getFile(), $e->getLine(), $e->getMessage()
            ];
            Log::write(implode('-', $record));
            return $e->getMessage();
        }
    }


    /**
     * @notes 添加订单日志表
     * @param $order_id
     * @param $user_id
     * @param $shop_id
     * @return array
     * @author suny
     * @date 2021/7/13 6:32 下午
     */
    public static function getOrderLogData($order_id, $user_id, $shop_id)
    {
        $order_log_data = [];
        $order_log_data['type'] = OrderLogEnum::TYPE_USER;
        $order_log_data['channel'] = OrderLogEnum::USER_PAID_ORDER;
        $order_log_data['order_id'] = $order_id;
        $order_log_data['handle_id'] = $user_id;
        $order_log_data['shop_id'] = $shop_id;
        $order_log_data['content'] = OrderLogEnum::getLogDesc(OrderLogEnum::USER_PAID_ORDER);
        $order_log_data['create_time'] = time();
        return $order_log_data;
    }


    /**
     * @notes 父订单回调
     * @param $order_sn
     * @param array $extra
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author suny
     * @date 2021/7/13 6:33 下午
     */
    private static function trade($order_sn, $extra = [])
    {
        //根据父订单号查找父订单
        $trade = OrderTrade::where(['t_sn' => $order_sn])->find();
        //根据父订单id查找子订单
        $orders = Order::with(['order_goods', 'shop'])
            ->where('trade_id', $trade['id'])
            ->select()->toArray();
        //修改用户消费累计额度
        $user = User::find($trade['user_id']);
        $user->total_order_amount = ['inc', $trade['order_amount']];
        $user->save();

        //赠送成长值
        $growth_ratio = ConfigServer::get('transaction', 'money_to_growth', 0);
        if ($growth_ratio > 0) {
            $able_get_growth = floor($trade['total_amount'] / $growth_ratio);
            $user->where('id', $trade['user_id'])
                ->inc('user_growth', $able_get_growth)
                ->update();
            AccountLogLogic::AccountRecord($trade['user_id'], $able_get_growth, 1, AccountLog::order_give_growth, '', $trade['id'], $order_sn);
        }

        // 生成分销订单
        PayLogic::distributionOrderGoods(array_column($orders, 'id'));
        // 更新分销会员等级
        DistributionLevelLogic::updateDistributionLevel($trade['user_id']);

        foreach ($orders as $item) {

            //赠送积分
            $open_award = ConfigServer::get('order_award', 'open_award', 0);
            if ($open_award == 1) {
                $award_event = ConfigServer::get('order_award', 'award_event', 0);
                $award_ratio = ConfigServer::get('order_award', 'award_ratio', 0);
                if ($award_ratio > 0) {
                    $award_integral = floor($item['order_amount'] * ($award_ratio / 100));
                }
            }

            //更新订单状态
            $data = [
                'pay_status' => PayEnum::ISPAID,
                'pay_time' => time(),
                'order_status' => Order::STATUS_WAIT_DELIVERY,
                'award_integral_status' => $award_event ?? 0,
                'award_integral' => $award_integral ?? 0
            ];

            //如果返回了第三方流水号
            if (isset($extra['transaction_id'])) {
                $data['transaction_id'] = $extra['transaction_id'];
            }
            $orderUpdate = Order::update($data, [
                [ 'id', '=', $item['id'] ],
                [ 'pay_status', '=', PayEnum::UNPAID ],
            ]);
            if ($orderUpdate->getUpdateResult() <= 0) {
                throw new \Exception('修改订单状态失败，订单可能已支付');
            }

            // 增加一条订单日志
            $order_log_add_data = self::getOrderLogData($item['id'], $item['user_id'], $item['shop_id']);
            $order_log_datas_insert[] = $order_log_add_data;
            OrderLog::insertAll($order_log_datas_insert);

//            if ($item['order_type'] == order::NORMAL_ORDER){
//                DistributionServer::commission($item['id']);
//            }
            //通知用户
            event('Notice', [
                'scene' => NoticeEnum::ORDER_PAY_NOTICE,
                'mobile' => $item['mobile'] ?? '',
                'params' => ['order_id' => $item['id'], 'user_id' => $item['user_id']]
            ]);

            //通知商家
            if (!empty($item['shop']['mobile'])) {
                event('Notice', [
                    'scene' => NoticeEnum::USER_PAID_NOTICE_SHOP,
                    'mobile' => $item['shop']['mobile'],
                    'params' => ['order_id' => $item['id'], 'user_id' => $item['user_id']]
                ]);
            }

            event('Printer', [
                'order_id' => $item['id'],
            ]);
        }
        //如果返回了第三方流水号
        if (isset($extra['transaction_id'])) {
            $trade->transaction_id = $extra['transaction_id'];
            $trade->save();
        }

        $order_ids = array_column($orders, 'id');

        // 虚拟商品更新订单信息
        GoodsVirtualLogic::afterPayVirtualDelivery($order_ids);

        // 更新商品销量
        self::updateGoodsSales($order_ids);
    }

    /**
     * @notes 子订单回调
     * @param $order_sn
     * @param array $extra
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author suny
     * @date 2021/7/13 6:33 下午
     */
    private static function order($order_sn, $extra = [])
    {

        $time = time();
        $order = Order::with(['order_goods', 'shop'])
            ->where('order_sn', $order_sn)
            ->find()->toArray();

        //赠送积分
        $open_award = ConfigServer::get('order_award', 'open_award', 0);
        if ($open_award == 1) {
            $award_event = ConfigServer::get('order_award', 'award_event', 0);
            $award_ratio = ConfigServer::get('order_award', 'award_ratio', 0);
            if ($award_ratio > 0) {
                $award_integral = floor($order['order_amount'] * ($award_ratio / 100));
            }
        }

        //更新订单状态
        $data = [
            'pay_status' => PayEnum::ISPAID,
            'pay_time' => time(),
            'order_status' => Order::STATUS_WAIT_DELIVERY,
            'award_integral_status' => $award_event ?? 0,
            'award_integral' => $award_integral ?? 0
        ];
        //如果返回了第三方流水号
        if (isset($extra['transaction_id'])) {
            $data['transaction_id'] = $extra['transaction_id'];
        }
        $orderUpdate = Order::update($data, [
            [ 'id', '=', $order['id'] ],
            [ 'pay_status', '=', PayEnum::UNPAID ],
        ]);
        if ($orderUpdate->getUpdateResult() <= 0) {
            throw new \Exception('修改订单状态失败，订单可能已支付');
        }

        // 增加商品销量

        // 增加一条订单日志
        $order_log_add_data = self::getOrderLogData($order['id'], $order['user_id'], $order['shop_id']);
        $order_log_datas_insert[] = $order_log_add_data;
        OrderLog::insertAll($order_log_datas_insert);

        //修改用户消费累计额度
        $user = User::find($order['user_id']);
        $user->total_order_amount = ['inc', $order['order_amount']];
        $user->save();

        //赠送成长值
        $growth_ratio = ConfigServer::get('transaction', 'money_to_growth', 0);
        if ($growth_ratio > 0) {
            $able_get_growth = floor($order['order_amount'] / $growth_ratio);
            $user->where('id', $order['user_id'])
                ->inc('user_growth', $able_get_growth)
                ->update();
            AccountLogLogic::AccountRecord($order['user_id'], $able_get_growth, 1, AccountLog::order_give_growth, '', $order['id'], $order_sn);
        }

        // 生成分销订单
        PayLogic::distributionOrderGoods([$order['id']]);
        // 更新分销会员等级
        DistributionLevelLogic::updateDistributionLevel($order['user_id']);

//        //拼购,砍价的订单不参与分销分佣
//        if ($order['order_type'] == order::NORMAL_ORDER){
//            DistributionServer::commission($order['id']);
//        }

        // 虚拟商品更新订单信息
        GoodsVirtualLogic::afterPayVirtualDelivery($order['id']);

        // 更新商品销量
        self::updateGoodsSales($order['id']);

        //通知用户
        event('Notice', [
            'scene' => NoticeEnum::ORDER_PAY_NOTICE,
            'mobile' => $order['mobile'] ?? '',
            'params' => ['order_id' => $order['id'], 'user_id' => $order['user_id']]
        ]);

        //通知商家
        if (!empty($order['shop']['mobile'])) {
            event('Notice', [
                'scene' => NoticeEnum::USER_PAID_NOTICE_SHOP,
                'mobile' => $order['shop']['mobile'],
                'params' => ['order_id' => $order['id'], 'user_id' => $order['user_id']]
            ]);
        }

        event('Printer', [
            'order_id' => $order['id'],
        ]);
    }


    /**
     * @notes 充值回调
     * @param $order_sn
     * @param array $extra
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author suny
     * @date 2021/7/13 6:33 下午
     */
    private static function recharge($order_sn, $extra = [])
    {

        $new = time();
        $recharge_order = new RechargeOrder();
        $order = $recharge_order->where(['order_sn' => $order_sn])->find();
        $update_data['pay_time'] = $new;
        $update_data['pay_status'] = PayEnum::ISPAID;
        if (isset($extra['transaction_id'])) {
            $update_data['transaction_id'] = $extra['transaction_id'];
        }
        $recharge_order->where(['id' => $order['id']])->update($update_data);
        $user = User::find($order['user_id']);
        $total_money = $order['order_amount'] + $order['give_money'];
        $total_integral = $order['give_integral'];
        $user->user_money = ['inc', $total_money];
        $user->user_integral = ['inc', $total_integral];
        $user->user_growth = ['inc', $order['give_growth']];
        $user->total_recharge_amount = ['inc', $total_money];
        $user->save();
        //记录余额
        $total_money > 0 && AccountLogLogic::AccountRecord($user->id, $total_money, 1, AccountLog::recharge_money, '', $order['id'], $order_sn);
        //记录积分
        $total_integral > 0 && AccountLogLogic::AccountRecord($user->id, $total_integral, 1, AccountLog::recharge_give_integral);
        //记录成长值
        $order['give_growth'] > 0 && AccountLogLogic::AccountRecord($user->id, $order['give_growth'], 1, AccountLog::recharge_give_growth);
    }


    /**
     * @notes 积分订单回调
     * @param $order_sn
     * @param array $extra
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/3/1 14:36
     */
    private static function integral($order_sn, $extra = [])
    {
        $order = IntegralOrder::where(['order_sn' => $order_sn])->findOrEmpty();
        $goods = $order['goods_snap'];

        // 更新订单状态
        $data = [
            'order_status' => IntegralOrderEnum::ORDER_STATUS_DELIVERY,
            'pay_status' => PayEnum::ISPAID,
            'pay_time' => time(),
        ];
        // 红包类型 或者 无需物流 支付完即订单完成
        if ($goods['type'] == IntegralGoodsEnum::TYPE_BALANCE || $goods['delivery_way'] == IntegralGoodsEnum::DELIVERY_NO_EXPRESS) {
            $data['order_status'] = IntegralOrderEnum::ORDER_STATUS_COMPLETE;
            $data['confirm_time'] = time();
        }
        // 第三方流水号
        if (isset($extra['transaction_id'])) {
            $data['transaction_id'] = $extra['transaction_id'];
        }
        IntegralOrder::update($data, ['id' => $order['id']]);

        // 更新商品销量
        IntegralGoods::where([['id', '=', $goods['id']], ['stock', '>=', $order['total_num']]])
            ->dec('stock', $order['total_num'])
            ->inc('sales', $order['total_num'])
            ->update();

        // 红包类型，直接增加余额
        if ($goods['type'] == IntegralGoodsEnum::TYPE_BALANCE) {
            $reward = round($goods['balance'] * $order['total_num'], 2);
            User::where(['id' => $order['user_id']])
                ->inc('user_money', $reward)
                ->update();

            AccountLogLogic::AccountRecord(
                $order['user_id'],
                $reward, 1,
                AccountLog::integral_order_inc_balance,
                '', $order['id'], $order['order_sn']
            );
        }

    }


    /**
     * @notes 更新商品销量
     * @param $order_id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/10/14 10:16
     */
    private static function updateGoodsSales($order_ids)
    {
        if (!is_array($order_ids)) {
            $order_ids = [$order_ids];
        }

        $order_goods = OrderGoods::whereIn('order_id', $order_ids)
            ->select()
            ->toArray();

        if (empty($order_goods)) {
            return false;
        }

        foreach ($order_goods as $item) {
            // 增加商品销量
            Goods::where('id', $item['goods_id'])
                ->inc('sales_actual', $item['goods_num'])
                ->update();
        }
        return true;
    }


}