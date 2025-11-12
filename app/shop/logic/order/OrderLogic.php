<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | gitee下载：https://gitee.com/multshop_gitee
// | github下载：https://github.com/multshop-github
// | 访问官网：https://www.multshop.cn
// | 访问社区：https://home.multshop.cn
// | 访问手册：http://doc.multshop.cn
// | 微信公众号：multshop技术社区
// | multshop系列产品在gitee、github等公开渠道开源版本可免费商用，未经许可不能去除前后端官方版权标识
// |  multshop系列产品收费版本务必购买商业授权，购买去版权授权后，方可去除前后端官方版权标识
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\shop\logic\order;


use app\common\basics\Logic;
use app\common\enum\NoticeEnum;
use app\common\enum\OrderEnum;
use app\common\enum\OrderLogEnum;
use app\common\enum\PayEnum;
use app\common\enum\TeamEnum;
use app\common\enum\VerificationEnum;
use app\common\logic\GoodsVirtualLogic;
use app\common\logic\OrderLogLogic;
use app\common\logic\OrderRefundLogic;
use app\common\model\Delivery;
use app\common\model\Express;
use app\common\model\order\Order;
use app\common\model\DevRegion;
use app\common\model\printer\Printer;
use app\common\model\printer\PrinterConfig;
use app\common\model\team\Team;
use app\common\model\team\TeamFound;
use app\common\model\team\TeamJoin;
use app\common\model\user\UserLevel;
use app\common\model\order\Verification;
use app\common\server\ConfigServer;
use app\common\server\ExportExcelServer;
use app\common\server\UrlServer;
use app\common\server\YlyPrinter;
use app\shop\logic\printer\PrinterLogic;
use think\facade\Cache;
use expressage\Kd100;
use expressage\Kdniao;
use think\facade\Db;
use think\Exception;
use think\facade\Validate;
use think\Model;


/**
 * 订单管理-逻辑
 * Class GoodsLogic
 * @package app\shop\logic\goods
 */
class OrderLogic extends Logic
{
    /**
     * @notes 订单统计
     * @param array $get
     * @param int $shop_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/14 10:23 上午
     */
    public static function statistics(array $get = [], int $shop_id = 0, $is_export = false)
    {
        $order = new Order();
        $where = static::getWhere($get);
        
        $page = $get['page'] ?? 1;
        $limit = $get['limit'] ?? 10;
        
        $where[] = ['o.delete', '=', 0];
        $where[] = ['o.del', '=', 0];
        $where[] = ['o.shop_id', '=', $shop_id];
    
        if (Validate::must($get['page'] ?? '')) {
            $page = $get['page'];
        }
    
        if (Validate::must($get['limit'] ?? '')) {
            $limit = $get['limit'];
        }

        // 导出excel
        if (true === $is_export) {
            return self::export($where);
        }

        $field = 'o.*,s.name as shop_name,u.level';

        $count = $order
            ->alias('o')
            ->Join('shop s', 's.id = o.shop_id')
            ->Join('user u', 'u.id = o.user_id')
            ->Join('order_goods g', 'g.order_id = o.id')
            ->where($where)
            ->group('o.id')
            ->count();

        $lists = $order
            ->alias('o')
            ->field($field)
            ->Join('shop s', 's.id = o.shop_id')
            ->Join('user u', 'u.id = o.user_id')
            ->Join('order_goods g', 'g.order_id = o.id')
            ->with(['order_goods', 'user'])
            ->where($where)
            ->append([
                'delivery_address', 'pay_status_text', 'order_source_text',
                'order_status_text', 'delivery_type_text','order_type_text',
                'pay_way_text'
            ])
            ->page($page, $limit)
            ->order('o.id desc')
            ->group('o.id')
            ->select()->toArray();

        $user_level = UserLevel::where(['del'=>0])->column('name','id');

        foreach ($lists as &$list) {
            $list['pay_time'] = $list['pay_time'] == '0' ? '未支付' : date('Y-m-d H:i:s', $list['pay_time']);
            $list['user']['avatar'] = UrlServer::getFileUrl($list['user']['avatar']);
            foreach ($list['order_goods'] as $order_good) {
                $order_good['image'] = UrlServer::getFileUrl($order_good['image']);
            }

            // 订单核销按钮
            $list['verification_btn'] = false;
            if ($list['order_status'] == OrderEnum::ORDER_STATUS_DELIVERY
                && $list['delivery_type'] == OrderEnum::DELIVERY_TYPE_SELF) {
                $list['verification_btn'] = true;
            }

            if ($list['order_type'] == OrderEnum::TEAM_ORDER) {
                $team = TeamJoin::field('TJ.status,TF.status as found_status')->alias('TJ')
                    ->where(['TJ.order_id'=>$list['id']])
                    ->join('team_found TF', 'TF.id = TJ.team_id')
                    ->findOrEmpty()->toArray();

                $list['is_team_success'] = false;
                if ($team && $team['found_status'] == 1) {
                    $list['is_team_success'] = true;
                }
            }

            // 会员等级
            $list['user_level'] = '暂无等级';
            if(isset($user_level[$list['level']])) {
                $list['user_level'] = $user_level[$list['level']];
            }

            //会员优惠
            $list['member_amount'] = $list['member_amount'] ?? 0.00;
        }
        return ['count' => $count, 'lists' => $lists];
    }
    
    static function getWhere($data)
    {
        $where = [];
        $where[] = [ 'o.del', '=', 0 ];
        $where[] = [ 'o.delete', '=', 0 ];
        
        //订单状态选项卡
        if (Validate::must($data['type'] ?? '')) {
            $where[] = [ 'order_status', '=', $data['type'] ];
        }
        
        //订单搜素
        if (! empty($data['search_key']) && ! empty($data['keyword'])) {
            $keyword = $data['keyword'];
            switch ($data['search_key']) {
                case 'order_sn':
                    $where[] = ['o.order_sn', '=', $keyword];
                    break;
                case 'user_sn':
                    $where[] = ['u.sn', '=', $keyword];
                    break;
                case 'shop_name':
                    $where[] = ['s.name', 'like', '%' . $keyword . '%'];
                    break;
                case 'goods_name':
                    $where[] = ['g.goods_name', 'like', '%' . $keyword . '%'];
                    break;
                case 'user_id':
                    $where[] = ['o.user_id', '=', $keyword];
                    break;
                case 'nickname':
                    $where[] = ['u.nickname', 'like', '%' . $keyword . '%'];
                    break;
                case 'user_mobile':
                    $where[] = ['u.mobile', '=', $keyword];
                    break;
                case 'consignee':
                    $where[] = ['consignee', '=', $keyword];
                    break;
                case 'consignee_mobile':
                    $where[] = ['o.mobile', '=', $keyword];
                    break;
            }
        }
        
        //商品名称
        if (Validate::must($data['goods_name'] ?? '')) {
            $where[] = ['g.goods_name', 'like', '%' . $data['goods_name'] . '%'];
        }
        
        //配送方式
        if (Validate::must($data['delivery_type'] ?? '')) {
            $where[] = ['o.delivery_type', '=', $data['delivery_type']];
        }
        
        //订单状态
        if (Validate::must($data['order_status'] ?? '')) {
            $where[] = [ 'o.order_status', '=', $data['order_status'] ];
        }
        
        //订单类型
        if (Validate::must($data['order_type'] ?? '')) {
            $where[] = [ 'o.order_type', '=', $data['order_type'] ];
        }
        
        //付款方式
        if (Validate::must($data['pay_way'] ?? '')) {
            $where[] = [ 'o.pay_way', '=', $data['pay_way'] ];
        }
        
        //订单来源
        if (Validate::must($data['order_source'] ?? '')) {
            $where[] = ['o.order_source', '=', $data['order_source']];
        }
        
        //下单时间
        if (Validate::must($data['start_time'] ?? '')) {
            $where[] = ['o.create_time', '>=', strtotime($data['start_time'])];
        }
        
        if (Validate::must($data['end_time'] ?? '')) {
            $where[] = ['o.create_time', '<=', strtotime($data['end_time'])];
        }
        
        return $where;
    }

    public static function totalCount($shopId)
    {
        $all = Order::where(['shop_id' => $shopId, 'del' => 0, 'delete' => 0])->count();
        $waitPay = Order::where(['shop_id' => $shopId, 'order_status' => 0 , 'del' => 0, 'delete' => 0])->count();
        $waitDelivery = Order::where(['shop_id' => $shopId, 'order_status' => 1 , 'del' => 0, 'delete' => 0])->count();
        $waitReceipt = Order::where(['shop_id' => $shopId, 'order_status' => 2 , 'del' => 0, 'delete' => 0])->count();
        $waitComplete = Order::where(['shop_id' => $shopId, 'order_status' => 3 , 'del' => 0, 'delete' => 0])->count();
        $waitClose = Order::where(['shop_id' => $shopId, 'order_status' => 4 , 'del' => 0, 'delete' => 0])->count();
        return [
            'all' => $all,
            'wait_pay' => $waitPay,
            'wait_delivery' => $waitDelivery,
            'wait_receipt' => $waitReceipt,
            'wait_complete' => $waitComplete,
            'wait_close' => $waitClose,
        ];
    }

    /**
     * @notes 订单详情
     * @param $id
     * @return array|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/14 10:23 上午
     */
    public static function getDetail($id)
    {

        $order = new Order();
        $result = $order
            ->with(['user', 'order_goods', 'invoice'])
            ->where('id', $id)
            ->append(['delivery_address', 'pay_status_text', 'order_status_text', 'pay_way_text', 'order_type_text', 'verification_status_text', 'can_change_invoice_no' ])
            ->find();
        $result['pay_time'] = $result['pay_time'] == 0 ? '未支付' : date('Y-m-d H:i:s', $result['pay_time']);
        $user = $result['user'];
        $user['avatar'] = UrlServer::getFileUrl($user['avatar']);
        $result['user'] = $user;
        foreach ($result['order_goods'] as &$order_goods) {
            $order_goods['goods_image'] = empty($order_goods['spec_image']) ?
                UrlServer::getFileUrl($order_goods['image']) : UrlServer::getFileUrl($order_goods['spec_image']);
        }

        if ($result['order_type'] == OrderEnum::TEAM_ORDER) {
            $team = TeamJoin::field('TJ.status,TF.status as found_status')->alias('TJ')
                ->where(['TJ.order_id'=>$result['id']])
                ->join('team_found TF', 'TF.id = TJ.team_id')
                ->findOrEmpty()->toArray();
            $result['is_team_success'] = false;
            if ($team) {
                $result['is_team_success'] = $team['found_status'] == 1 ? true : false;
            }
        }

        // 自提提货时间
        if ($result['delivery_type'] == OrderEnum::DELIVERY_TYPE_SELF && $result['verification_status']) {
            $result['confirm_take_time'] = date('Y-m-d H:i:s', $result['confirm_take_time']);
        } else {
            $result['confirm_take_time'] = '';
        }

        //会员优惠
        $result['member_amount'] = $result['member_amount'] ?? 0.00;

        return $result;
    }

    /**
     * @notes 物流信息
     * @param $order_id
     * @return array|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author suny
     * @date 2021/7/14 10:24 上午
     */
    public static function shippingInfo($order_id)
    {

        $shipping = Db::name('delivery')->where('order_id', $order_id)->find();
        if ($shipping) {
            $shipping['create_time_text'] = date('Y-m-d H:i:s', $shipping['create_time']);
        }
        $shipping['traces'] = self::getShipping($order_id);
        return $shipping;
    }

    /**
     * @notes 物流轨迹
     * @param $order_id
     * @return bool|string[]
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/14 10:24 上午
     */
    public static function getShipping($order_id)
    {

        $orderModel = new Order();
        $order = $orderModel->alias('o')
            ->field('invoice_no,shipping_name,shipping_id,o.shipping_status,o.mobile')
            ->join('delivery d', 'd.order_id = o.id')
            ->where(['o.id' => $order_id])
            ->find();
        $express = ConfigServer::get('express', 'way', '', '');
        $key = ConfigServer::get($express, 'appkey');
        $app = ConfigServer::get($express, 'appsecret');

        if (empty($express) || $order['shipping_status'] != 1 || empty($app) || empty($key)) {
            return $traces[] = ['暂无物流信息'];
        }
        //快递配置设置为快递鸟时
        if ($express === 'kdniao') {
            $expressage = (new Kdniao($app, $key, true));
            $shipping_field = 'codebird';
        } else {
            $expressage = (new Kd100($app, $key, true));
            $shipping_field = 'code100';
        }

        //快递编码
        $shipping_code = Db::name('express')
            ->where(['id' => $order['shipping_id']])
            ->value($shipping_field);

        //获取物流轨迹
        if (in_array(strtolower($shipping_code ), [ 'sf', 'shunfeng' ])) {
            if ($express === 'kdniao') {
                $expressage->logistics($shipping_code, $order['invoice_no'], substr($order['mobile'], -4));
            } else {
                $expressage->logistics($shipping_code, $order['invoice_no'], $order['mobile']);
            }
        }else {
            $expressage->logistics($shipping_code, $order['invoice_no']);
        }
        $traces = $expressage->logisticsFormat();
        if ($traces == false) {
            $traces[] = [$expressage->getError()];
        } else {
            foreach ($traces as &$item) {
                $item = array_values(array_unique($item));
            }
        }
        return $traces;
    }

    /**
     * @notes 获取物流
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/14 10:24 上午
     */
    public static function express()
    {
        return Express::where('del', 0)->select();
    }
    
    static function change_invoice_no($post)
    {
        $id = $post['order_id'];
        $order = Order::where(['id' => $id])->find();
        if (!$order) {
            return '订单失效';
        }
    
        if ($order['del'] == 1) {
            return '订单已删除';
        }
    
        if (! $order->getAttr('can_change_invoice_no')) {
            return '已不能修改物流单号';
        }
    
        //
        if ($post['send_type'] == 1 && empty($post['invoice_no'])) {
            return '请输入快递单号';
        }
        
        $delivery_data = [
            'invoice_no' => $post['invoice_no'] ?? '',
        ];
    
        $shipping = Db::name('express')->where('id', $post['shipping_id'])->find();
    
        if ($post['send_type'] == 1) {
            $delivery_data['shipping_id'] = $post['shipping_id'];
            $delivery_data['shipping_name'] = $shipping['name'] ?? '';
        }
        
        Delivery::update($delivery_data, [ [ 'order_id', '=', $post['order_id'] ] ]);
        
        return true;
    }

    /**
     * @notes 发货操作
     * @param $data
     * @param $admin_id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author suny
     * @date 2021/7/14 10:24 上午
     */
    public static function deliveryHandle($data, $admin_id)
    {

        $order_id = $data['order_id'];
        $order = Order::where(['del' => 0, 'id' => $order_id])->find();

        if ($order['shipping_status'] == 1) {
            return true;
        }

        $shipping = Db::name('express')->where('id', $data['shipping_id'])->find();

        //添加发货单
        $delivery_data = [
            'order_id' => $order_id,
            'order_sn' => $order['order_sn'],
            'user_id' => $order['user_id'],
            'admin_id' => $admin_id,
            'consignee' => $order['consignee'],
            'mobile' => $order['mobile'],
            'province' => $order['province'],
            'city' => $order['city'],
            'district' => $order['district'],
            'address' => $order['address'],
            'invoice_no' => $data['invoice_no'],
            'send_type' => $data['send_type'],
            'create_time' => time(),
        ];
        //配送方式->快递配送
        if ($data['send_type'] == 1) {
            $delivery_data['shipping_id'] = $data['shipping_id'];
            $delivery_data['shipping_name'] = $shipping['name'];
            $delivery_data['shipping_status'] = 1;
        }
        $delivery_id = Db::name('delivery')->insertGetId($delivery_data);


        //更新订单下商品的发货状态
        $order->update_time = time();
        $order->shipping_time = time();
        $order->shipping_status = 1;
        $order->order_status = Order::STATUS_WAIT_RECEIVE;
        $order->delivery_id = $delivery_id;
        $order->save();

        //订单日志
        OrderLogLogic::record(
            OrderLogEnum::TYPE_SHOP,
            OrderLogEnum::SHOP_DELIVERY_ORDER,
            $order_id,
            $admin_id,
            OrderLogEnum::SHOP_DELIVERY_ORDER
        );

        //通知用户发货
        if (!empty($order['mobile'])) {
            event('Notice', [
                'scene' => NoticeEnum::ORDER_DELIVERY_NOTICE,
                'mobile' => $order['mobile'],
                'params' => [
                    'order_id' => $order['id'],
                    'user_id' => $order['user_id'],
                    'shipping_name' => $delivery_data['shipping_name'] ?? '无需快递',
                    'invoice_no' => $data['invoice_no'] ?? '',
                ]
            ]);
        }
    }

    /**
     * @notes 判断是否可以发货
     * @param $post
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/14 10:24 上午
     */
    public static function checkDelivery($post)
    {

        $id = $post['order_id'];
        $order = Order::where(['id' => $id])->find();
        if (!$order) {
            return '订单失效';
        }

        if ($order['del'] == 1) {
            return '订单已删除';
        }

        if ($order['shipping_status'] == 1) {
            return '此订单已发货';
        }
        
        //
        if ($post['send_type'] == 1 && empty($post['invoice_no'])) {
            return '请输入快递单号';
        }

        if ($order['order_type'] == OrderEnum::TEAM_ORDER) {
            $join = TeamJoin::where(['order_id' => $order['id']])->findOrEmpty();
            if ($join['status'] != Team::STATUS_SUCCESS) {
                return '已支付的拼团订单需要等待拼团成功后才能发货';
            }
        }
        
        return true;
    }

    /**
     * @notes 判断是否可以取消订单
     * @param $post
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author suny
     * @date 2021/7/14 10:24 上午
     */
    public static function checkCancel($post)
    {

        $id = $post['order_id'];
        $order = Order::where(['id' => $id, 'del' => 0])->find();

        if (!$order) {
            return '订单失效';
        }

        if ($order['order_status'] > OrderEnum::ORDER_STATUS_DELIVERY) {
            return '此订单不可取消';
        }

        if ($order['order_type'] == OrderEnum::TEAM_ORDER) {
            $found = Db::name('team_found')->where(['id' => $order['team_found_id']])->find();
            if ($found['status'] == Team::STATUS_WAIT_SUCCESS) {
                return '已支付的拼团订单需要有拼团结果才可以取消';
            }
        }
        return true;
    }

    /**
     * @notes 取消订单(返回商品规格表库存)
     * @param $order_id
     * @param $admin_id
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     * @author suny
     * @date 2021/7/14 10:24 上午
     */
    public static function cancel($order_id, $admin_id)
    {

        Db::startTrans();
        try {
            $order = Order::where(['id' => $order_id], ['orderGoods'])->find();

            // 如果是拼团订单
            $team_join = (new TeamJoin())->where(['order_id' => $order['id'],'status'=>TeamEnum::TEAM_STATUS_CONDUCT])->findOrEmpty()->toArray();//拼团中
            if ($order['order_type'] == OrderEnum::TEAM_ORDER && !empty($team_join)) {
                $time = time();
                $team_id = $team_join['team_id'];
                $teamJoin = (new TeamJoin())->alias('TJ')
                    ->field(['TJ.*,O.order_sn,O.order_status,O.pay_status,O.refund_status,O.order_amount'])
                    ->where(['team_id' => $team_id])
                    ->join('order O', 'O.id=TJ.order_id')
                    ->select()->toArray();

                TeamFound::update(['status' => TeamEnum::TEAM_STATUS_FAIL, 'team_end_time' => $time], ['id' => $team_id]);
                foreach ($teamJoin as $item) {
                    TeamJoin::update(['status' => TeamEnum::TEAM_STATUS_FAIL, 'update_time' => $time], ['id' => $item['id']]);
                    OrderRefundLogic::cancelOrder($item['order_id'], OrderLogEnum::TYPE_SHOP, $admin_id);  //取消订单

                    if ($item['pay_status'] == PayEnum::ISPAID) {
                        $order = (new Order())->findOrEmpty($item['order_id'])->toArray();
                        OrderRefundLogic::cancelOrderRefundUpdate($order); //更新订单状态
                        OrderRefundLogic::refund($order, $order['order_amount'], $order['order_amount']); //订单退款
                    }
                }


            } else {

                //取消订单
                OrderRefundLogic::cancelOrder($order_id, OrderLogEnum::TYPE_SHOP, $admin_id);
                //已支付的订单,取消,退款
                if ($order['pay_status'] == PayEnum::ISPAID) {
                    //更新订单状态
                    OrderRefundLogic::cancelOrderRefundUpdate($order);
                    //订单退款
                    OrderRefundLogic::refund($order, $order['order_amount'], $order['order_amount']);
                }
            }

            Db::commit();
            return true;

        } catch (\Exception $e) {
            Db::rollback();
            //增加退款失败记录
            OrderRefundLogic::addErrorRefund($order, $e->getMessage());
            return $e->getMessage();
        }
    }

    /**
     * @notes 判断是否可以删除订单
     * @param $post
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/14 10:24 上午
     */
    public static function checkDel($post)
    {

        $id = $post['order_id'];
        $order = Order::where(['id' => $id])->find();

        if (!$order) {
            return '订单失效';
        }

        if ($order['del'] == 1) {
            return '订单已删除';
        }

        if ($order['order_status'] != OrderEnum::ORDER_STATUS_DOWN) {
            return '此订单不可删除';
        }
        return true;
    }

    /**
     * @notes 删除已取消的订单
     * @param $order_id
     * @param $admin_id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/14 10:24 上午
     */
    public static function del($order_id, $admin_id)
    {

        $order = Order::where(['delete' => 0, 'id' => $order_id])->find();
        $order->save(['delete' => 1, 'update_time' => time()]);

        //订单日志
        OrderLogLogic::record(
            OrderLogEnum::TYPE_SHOP,
            OrderLogEnum::SHOP_DEL_ORDER,
            $order_id,
            $admin_id,
            OrderLogEnum::SHOP_DEL_ORDER
        );
    }

    /**
     * @notes 确认收货
     * @param $order_id
     * @param $admin_id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/14 10:25 上午
     */
    public static function confirm($order_id, $admin_id)
    {

        $order = Order::where(['del' => 0, 'id' => $order_id])->find();
        $order->order_status = Order::STATUS_FINISH;
        $order->update_time = time();
        $order->confirm_take_time = time();
        $order->save();

        //订单日志
        OrderLogLogic::record(
            OrderLogEnum::TYPE_SHOP,
            OrderLogEnum::SHOP_CONFIRM_ORDER,
            $order_id,
            $admin_id,
            OrderLogEnum::SHOP_CONFIRM_ORDER
        );
    }

    /**
     * @notes 订单备注
     * @param $post
     * @param string $type
     * @return Order|array|Model
     * @author suny
     * @date 2021/7/14 10:25 上午
     */
    public static function remarks($post, $type = "get")
    {

        if ($type === 'get') {
            return Order::field('id,order_remarks')
                ->where(['id' => $post['id']])
                ->findOrEmpty();
        } else {
            return Order::where(['id' => $post['id']])
                ->update(['order_remarks' => $post['order_remarks']]);
        }
    }


    /**
     * @notes 打印订单
     * @param $id
     * @param $shop_id
     * @return string|void
     * @author 段誉
     * @date 2022/1/20 11:24
     */
    public static function orderPrint($id, $shop_id)
    {
        try {
            //打印机配置
            $config = PrinterConfig::where(['status' => 1, 'shop_id' => $shop_id])->findOrEmpty();
            //打印机列表
            $printers = Printer::where([
                'shop_id' => $shop_id,
                'config_id' => $config['id'],
                'del' => 0,
                'status' => 1
            ])->select()->toArray();

            if ($config->isEmpty() || empty($printers)) {
                throw new Exception('请先配置打印机');
            }

            // 订单信息
            $order = self::getPrintOrder($id);

            $yly_print = new YlyPrinter($config['client_id'], $config['client_secret'], $shop_id);
            // 模板
            $template = PrinterLogic::getPrinterTpl($shop_id);
            $yly_print->ylyPrint($printers, $order, $template);

            return true;

        } catch (\Exception $e) {
            $msg = json_decode($e->getMessage(), true);
            if ($msg && isset($msg['error'])) {
                return '易联云：' . $msg['error_description'];
            }
            if (18 === $e->getCode()) {
                Cache::rm('yly_access_token' . $shop_id);
                Cache::rm('yly_refresh_token' . $shop_id);
            }
            return '易联云：' . $e->getMessage();
        }
    }


    /**
     * @notes 获取打印订单
     * @param $id
     * @return array
     * @author 段誉
     * @date 2022/1/21 9:58
     */
    public static function getPrintOrder($id)
    {
        return (new Order())
            ->with(['user', 'order_goods'])
            ->where('id', $id)
            ->append(['delivery_address'])
            ->findOrEmpty()->toArray();
    }

    /**
     * @notes 获取地址
     * @param $post
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/14 10:25 上午
     */
    public static function change_address($post)
    {

        $order = new Order();
        $info = $order->where(['id' => $post['id']])
            ->field(['id', 'province', 'city', 'district', 'address','consignee','mobile'])
            ->find()->toArray();
        $info = json_encode($info, JSON_UNESCAPED_UNICODE);
        $address_tree = json_encode(self::getAddressTree(), JSON_UNESCAPED_UNICODE);
        $data = [
            'info' => $info,
            'address_tree' => $address_tree
        ];
        return $data;
    }

    /**
     * @notes 获取城市信息
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/14 10:25 上午
     */
    public static function getAddressTree()
    {

        $DevRegion = new DevRegion();
        $lists = $DevRegion
            ->field(['name', 'id', 'parent_id', 'level'])
            ->where('level', '>', 0)
            ->select();
        return $lists;
    }

    /**
     * @notes 修改地址
     * @param $post
     * @author suny
     * @date 2021/7/14 10:27 上午
     */
    public static function change_address_post($post)
    {

        $order = new Order();
        $data = [
            'province' => $post['province'],
            'city' => $post['city'],
            'district' => $post['district'],
            'address' => $post['address'],
            'consignee' => $post['consignee'],
            'mobile' => $post['mobile'],
        ];
        $order->where(['id' => $post['order_id']])->update($data);
    }

    /**
     * @notes 获取各列表数量
     * @param $shop_id
     * @return int
     * @author suny
     * @date 2021/7/14 10:27 上午
     */
    public static function getAll($shop_id)
    {
    
        $data = input();
    
        unset($data['type']);
    
        return Order::alias('o')
            ->join('shop s', 's.id = o.shop_id')
            ->join('user u', 'u.id = o.user_id')
            ->join('order_goods g', 'g.order_id = o.id')
            ->with(['order_goods', 'user', 'shop'])
            ->where(static::getWhere($data))
            ->where('o.shop_id', $shop_id)
            ->where('o.del', 0)
            ->where('o.delete', 0)
            ->group('o.id')
            ->count();
    }

    /**
     * @notes 拼装统计信息
     * @param $order_status
     * @return array
     * @author suny
     * @date 2021/7/13 3:36 下午
     */
    public static function getStat($order_status, $shop_id)
    {
    
        $data   = input();
    
        unset($data['type']);
    
        $result = [];
    
        foreach ($order_status as $status => $title) {
            $num = Order::alias('o')
                ->join('shop s', 's.id = o.shop_id')
                ->join('user u', 'u.id = o.user_id')
                ->join('order_goods g', 'g.order_id = o.id')
                ->with(['order_goods', 'user', 'shop'])
                ->where(static::getWhere($data))
                ->where('o.order_status', $status)
                ->where('o.shop_id', $shop_id)
                ->where('o.del', 0)
                ->where('o.delete', 0)
                ->group('o.id')
                ->count();
    
            $result[] = [
                'title' => $title,
                'status' => $status,
                'count' => $num,
            ];
        }
        
        return $result;
    }


    /**
     * @notes 虚拟商品发货
     * @param $post
     * @param $admin_id
     * @return bool|void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/4/7 17:55
     */
    public static function virtualDelivery($post, $admin_id)
    {
        try {
            $order_id = $post['order_id'];
            $order = Order::with(['order_goods'])->where(['del' => 0, 'id' => $order_id])->find();

            // 更新发货订单信息
            $result = GoodsVirtualLogic::shopSelfDelivery($order_id,  $post['delivery_content']);
            if (true !== $result) {
                throw new \Exception($result);
            }

            //订单日志
            OrderLogLogic::record(
                OrderLogEnum::TYPE_SHOP,
                OrderLogEnum::SHOP_DELIVERY_ORDER,
                $order_id,
                $admin_id,
                OrderLogEnum::SHOP_DELIVERY_ORDER
            );

            //通知用户发货
            if (!empty($order['mobile'])) {
                event('Notice', [
                    'scene' => NoticeEnum::ORDER_DELIVERY_NOTICE,
                    'mobile' => $order['mobile'],
                    'params' => [
                        'order_id' => $order['id'],
                        'user_id' => $order['user_id'],
                        'shipping_name' => '无需快递',
                        'invoice_no' => '',
                    ]
                ]);
            }

            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 导出Excel
     * @param array $condition
     * @return array|false
     * @author 段誉
     * @date 2022/4/24 10:10
     */
    public static function export($condition = [])
    {
        try {
            $field = 'o.*,order_status as order_status_text,pay_way as pay_way_text,
            o.delivery_type as delivery_type_text,order_type as order_type_text,
            u.nickname,s.name as shop_name';

            $lists = Order::alias('o')
                ->join('shop s', 's.id = o.shop_id')
                ->join('user u', 'u.id = o.user_id')
                ->join('order_goods g', 'g.order_id = o.id')
                ->with(['order_goods'])
                ->field($field)
                ->where($condition)
                ->append(['delivery_address', 'pay_status_text', 'order_source_text'])
                ->order('o.id desc')
                ->group('o.id')
                ->select()
                ->toArray();

            foreach ($lists as &$item) {
                $orderGoodsList = [];
                $goodsItemList = [];
                $goodsPriceList = [];
                $goodsNumList = [];
                foreach ($item['order_goods'] as $good) {
                    $orderGoodsList[] = $good['goods_name'];
                    $goodsItemList[] = $good['spec_value'];
                    $goodsPriceList[] = $good['goods_price'];
                    $goodsNumList[] = $good['goods_num'];
                }
                $item['order_goods_list'] = implode(';', $orderGoodsList);
                $item['goods_item_list'] = implode(';', $goodsItemList);
                $item['goods_price_list'] = implode(';', $goodsPriceList);
                $item['goods_num_list'] = implode(';', $goodsNumList);
            }

            $excelFields = [
                'order_sn' => '订单编号',
                'order_type_text' => '订单类型',
                'nickname' => '用户名称',
                'order_goods_list' => '商品信息',
                'goods_item_list' => '规格',
                'goods_price_list' => '商品价格',
                'goods_num_list' => '商品数量',
                'order_amount' => '实付金额',
                'consignee' => '收货人',
                'mobile' => '收货人手机',
                'delivery_address' => '收货地址',
                'pay_status_text' => '支付状态',
                'order_status_text' => '订单状态',
                'create_time' => '下单时间',
            ];

            $export = new ExportExcelServer();
            $export->setFileName('订单');
            $result = $export->createExcel($excelFields, $lists);

            return ['url' => $result];

        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 提货核销
     * @param $params
     * @param $adminInfo
     * @return bool
     * @author 段誉
     * @date 2022/11/2 14:58
     */
    public static function verification($params, $adminInfo)
    {
        Db::startTrans();
        try {
            $order = Order::find($params['order_id']);

            //添加核销记录
            Verification::create([
                'order_id' => $order['id'],
                'shop_id' => $order['shop_id'],
                'handle_id' => $adminInfo['id'],
                'verification_scene' => VerificationEnum::TYPE_SHOP,
                'snapshot' => json_encode([
                    'sn' => $adminInfo['account'],
                    'name' => $adminInfo['name']
                ]),
            ]);

            //更新订单状态
            $order->order_status = OrderEnum::ORDER_STATUS_COMPLETE;
            $order->verification_status = OrderEnum::WRITTEN_OFF;
            $order->confirm_take_time = time();
            $order->save();

            //订单日志
            OrderLogLogic::record(
                OrderLogEnum::TYPE_SHOP,
                OrderLogEnum::SHOP_VERIFICATION,
                $order['id'],
                $adminInfo['id'],
                OrderLogEnum::SHOP_VERIFICATION
            );

            Db::commit();
            return true;

        } catch (\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }

}