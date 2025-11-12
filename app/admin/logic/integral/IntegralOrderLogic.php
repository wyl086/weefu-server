<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\admin\logic\integral;


use app\common\basics\Logic;
use app\common\enum\IntegralOrderEnum;
use app\common\enum\NoticeEnum;
use app\common\enum\OrderLogEnum;
use app\common\enum\PayEnum;
use app\common\logic\IntegralOrderRefundLogic;
use app\common\logic\OrderLogLogic;
use app\common\model\Express;
use app\common\model\integral\IntegralDelivery;
use app\common\model\integral\IntegralOrder;
use app\common\model\order\Order;
use app\common\server\ConfigServer;
use app\common\server\UrlServer;
use expressage\Kd100;
use expressage\Kdniao;
use think\facade\Db;

class IntegralOrderLogic extends Logic
{
    /**
     * @notes 兑换订单列表
     * @param array $get
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2022/3/3 10:38 上午
     */
    public static function lists($get = [])
    {
        $order = new IntegralOrder();

        $where = [];
        //列表状态
        if (isset($get['status']) && $get['status'] != '') {
            $where[] = ['order_status', '=', $get['status']];
        }
        //兑换单号
        if (isset($get['order_sn']) && $get['order_sn'] != '') {
            $where[] = ['order_sn', 'like', '%' . $get['order_sn'] . '%'];
        }
        //商品名称
        if (isset($get['goods_name']) && $get['goods_name'] != '') {
            $where[] = ['goods_snap->name', 'like', '%' . $get['goods_name'] . '%'];
        }
        //兑换类型
        if (isset($get['type']) && $get['type'] != '') {
            $where[] = ['exchange_type', '=', intval($get['type'])];
        }
        //订单状态
        if (isset($get['order_status']) && $get['order_status'] != '') {
            $where[] = ['order_status', '=', $get['order_status']];
        }
        //下单时间
        if (isset($get['start_time']) && $get['start_time'] != '') {
            $where[] = ['create_time', '>=', strtotime($get['start_time'])];
        }
        if (isset($get['end_time']) && $get['end_time'] != '') {
            $where[] = ['create_time', '<=', strtotime($get['end_time'])];
        }

        $count = $order->where($where)->count();

        $lists = $order
            ->field('*')
            ->with(['user'])
            ->where($where)
            ->append(['pay_status_text', 'delivery_address', 'order_status_desc', 'type_desc'])
            ->page($get['page'], $get['limit'])
            ->order('id', 'desc')
            ->select()
            ->toArray();

        foreach ($lists as $key=>&$list) {
            $list['pay_time'] = $list['pay_time'] == '0' ? '未支付' : date('Y-m-d H:i:s', $list['pay_time']);
            $list['user']['avatar'] = UrlServer::getFileUrl($list['user']['avatar']);
            $list['goods_snap']['image'] = UrlServer::getFileUrl($list['goods_snap']['image']);
        }
        return ['count' => $count, 'lists' => $lists];
    }

    /**
     * @notes 兑换订单详情
     * @param $id
     * @return array
     * @author ljj
     * @date 2022/3/3 11:10 上午
     */
    public static function detail($id)
    {
        $result = (new IntegralOrder())
            ->with(['user'])
            ->where('id', $id)
            ->append(['delivery_address', 'pay_status_desc', 'order_status_desc','type_desc','pay_way_desc'])
            ->findOrEmpty()
            ->toArray();

        $result['pay_time'] = $result['pay_time'] == '0' ? '未支付' : date('Y-m-d H:i:s', $result['pay_time']);
        $result['confirm_time'] = empty($result['confirm_time']) ? '-' : date('Y-m-d H:i:s', $result['confirm_time']);
        $result['user']['avatar'] = UrlServer::getFileUrl($result['user']['avatar']);
        $result['goods_snap']['image'] = UrlServer::getFileUrl($result['goods_snap']['image']);

        return $result;
    }

    /**
     * @notes 发货详情
     * @param $id
     * @return array
     * @author ljj
     * @date 2022/3/3 11:48 上午
     */
    public static function deliveryDetail($id)
    {
        $result = (new IntegralOrder())
            ->where('id', $id)
            ->append(['delivery_address'])
            ->findOrEmpty()
            ->toArray();

        $result['goods_snap']['image'] = UrlServer::getFileUrl($result['goods_snap']['image']);

        return $result;
    }

    /**
     * @notes 快递公司列表
     * @return array|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2022/3/3 11:35 上午
     */
    public static function express()
    {
        return Express::where('del', 0)->field('id,name')->select()->toArray();
    }

    /**
     * @notes 发货操作
     * @param $post
     * @param $admin_id
     * @author ljj
     * @date 2022/3/3 2:53 下午
     */
    public static function deliveryHandle($post, $admin_id)
    {
        Db::startTrans();
        try {
            $order = IntegralOrder::where(['del'=>0,'id'=>$post['id']])->findOrEmpty()->toArray();

            $shipping = Express::where('id',$post['shipping_id'])->findOrEmpty()->toArray();

            //添加发货单
            $delivery_data = [
                'order_id' => $order['id'],
                'order_sn' => $order['order_sn'],
                'user_id' => $order['user_id'],
                'admin_id' => $admin_id,
                'consignee' => $order['consignee'],
                'mobile' => $order['mobile'],
                'province' => $order['province'],
                'city' => $order['city'],
                'district' => $order['district'],
                'address' => $order['address'],
                'invoice_no' => $post['invoice_no'],
                'send_type' => 1,
                'shipping_id' => $post['shipping_id'],
                'shipping_name' => $shipping['name'],
                'shipping_status' => 1,
                'create_time' => time(),
            ];
            $delivery = IntegralDelivery::create($delivery_data);

            //更新订单信息
            IntegralOrder::update([
                'update_time' => time(),
                'shipping_time' => time(),
                'shipping_status' => 1,
                'order_status' => IntegralOrderEnum::ORDER_STATUS_GOODS,
                'delivery_id' => $delivery->id,
            ],['id'=>$order['id']]);

            //通知用户发货
            if (!empty($order['mobile'])) {
                event('Notice', [
                    'scene' => NoticeEnum::ORDER_DELIVERY_NOTICE,
                    'mobile' => $order['mobile'],
                    'params' => [
                        'order_id' => $order['id'],
                        'user_id' => $order['user_id'],
                        'shipping_name' => $delivery_data['shipping_name'],
                        'invoice_no' => $post['invoice_no'],
                    ]
                ]);
            }

            // 提交事务
            Db::commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return $e->getMessage();
        }

    }

    /**
     * @notes 物流信息
     * @param $order_id
     * @return array
     * @author ljj
     * @date 2022/3/3 3:31 下午
     */
    public static function shippingInfo($order_id)
    {
        $shipping = IntegralDelivery::where('order_id', $order_id)->findOrEmpty()->toArray();
        $shipping['traces'] = self::getShipping($order_id);
        return $shipping;
    }

    /**
     * @notes 物流轨迹
     * @param $order_id
     * @return bool|string[]
     * @author ljj
     * @date 2022/3/3 3:31 下午
     */
    public static function getShipping($order_id)
    {
        $order = IntegralOrder::alias('o')
            ->field('invoice_no,shipping_name,shipping_id,o.shipping_status,o.mobile')
            ->join('integral_delivery d', 'd.order_id = o.id')
            ->where(['o.id' => $order_id])
            ->findOrEmpty()
            ->toArray();

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
        if ($shipping_code === 'SF' && $express === 'kdniao') {
            $expressage->logistics($shipping_code, $order['invoice_no'], substr($order['mobile'],-4));
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
     * @notes 确认收货
     * @param $order_id
     * @param $admin_id
     * @return bool
     * @author ljj
     * @date 2022/3/3 3:39 下午
     */
    public static function confirm($order_id, $admin_id)
    {
        IntegralOrder::update([
            'order_status' => IntegralOrderEnum::ORDER_STATUS_COMPLETE,
            'update_time' => time(),
            'confirm_time' => time(),
        ],['id'=>$order_id]);

        return true;
    }


    /**
     * @notes 取消订单
     * @param $id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/3/3 18:49
     */
    public static function cancel($id)
    {
        Db::startTrans();
        try {
            $order = IntegralOrder::findOrEmpty($id);

            // 更新订单状态, 退回库存, 扣减销量
            IntegralOrderRefundLogic::cancelOrder($id);

            // 退回已支付积分
            IntegralOrderRefundLogic::refundOrderIntegral($id);

            // 退回订单已支付积分或已支付金额
            if ($order['pay_status'] == PayEnum::ISPAID) {
                IntegralOrderRefundLogic::refundOrderAmount($id);
            }

            Db::commit();
            return true;

        } catch (\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();

            IntegralOrderRefundLogic::addRefundLog(
                $order, $order['order_amount'],
                2, $e->getMessage()
            );
            return false;
        }
    }
}