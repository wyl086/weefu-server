<?php

namespace app\api\logic;

use app\common\basics\Logic;
use app\common\enum\IntegralGoodsEnum;
use app\common\enum\IntegralOrderEnum;
use app\common\enum\PayEnum;
use app\common\logic\AccountLogLogic;
use app\common\logic\IntegralOrderRefundLogic;
use app\common\logic\PayNotifyLogic;
use app\common\model\AccountLog;
use app\common\model\integral\IntegralGoods;
use app\common\model\integral\IntegralOrder;
use app\common\model\user\User;
use app\common\server\ConfigServer;
use app\common\server\UrlServer;
use expressage\Kd100;
use expressage\Kdniao;
use think\facade\Env;
use think\facade\Db;

/**
 * 积分商城订单
 * Class IntegralOrderLogic
 * @package app\api\logic
 */
class IntegralOrderLogic extends Logic
{

    /**
     * @notes 结算订单
     * @param $params
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/3/2 9:50
     */
    public static function settlement($params)
    {
        // 地址
        if (empty($params['address_id']) || !$params['address_id']) {
            $address = UserAddressLogic::getDefaultAddress($params['user_id']);
        } else {
            $address = UserAddressLogic::getOneAddress($params['user_id'], ['id' => $params['address_id']]);
        }

        // 订单需支付总金额
        $order_amount = 0;
        $goods_price = 0;

        $goods = IntegralGoods::withoutField(['content', 'update_time'])->findOrEmpty($params['id'])->toArray();
        // 兑换方式为纯积分
        if ($goods['exchange_way'] == IntegralGoodsEnum::EXCHANGE_WAY_INTEGRAL) {
            // 订单需支付积分
            $order_integral = $goods['need_integral'] * $params['num'];
        } else {
            // 订单需支付总金额
            $goods_price = $goods['need_money'] * $params['num'];
            $order_amount = $goods_price;
            // 订单需支付总积分
            $order_integral = $goods['need_integral'] * $params['num'];
        }

        // 运费
        $shipping_price = 0;
        // 快递配送 && 快递统一运费 && 运费>0
        if ($goods['delivery_way'] == IntegralGoodsEnum::DELIVERY_EXPRESS
            && $goods['express_type'] == IntegralGoodsEnum::EXPRESS_TYPE_UNIFIED
            && $goods['express_money'] > 0) {
            $order_amount = $order_amount + $goods['express_money'];
            $shipping_price = $goods['express_money'];
        }

        return [
            'address' => $address,
            'goods' => $goods,
            'need_pay' => $order_amount > 0 ? 1 : 0,
            'exchange_way' => $goods['exchange_way'],
            'delivery_way' => $goods['delivery_way'],
            'total_num' => intval($params['num']),
            'shipping_price' => $shipping_price, // 运费
            'goods_price' => round($goods_price, 2), // 商品金额(不包含运费)
            'order_amount' => round($order_amount, 2), // 订单需要的金额(包含运费)
            'order_integral' => $order_integral, // 订单需要返回的积分
        ];
    }


    /**
     * @notes 提交订单
     * @param $params
     * @return array|false
     * @author 段誉
     * @date 2022/3/2 9:50
     */
    public static function submitOrder($params)
    {
        Db::startTrans();
        try {
            // 结算详情(支付积分，支付金额)
            $settle = self::settlement($params);

            // 提交前验证
            $user = User::findOrEmpty($params['user_id']);
            if ($user['user_integral'] < $settle['order_integral']) {
                throw new \Exception('积分不足');
            }

            if ($settle['total_num'] <= 0) {
                throw new \Exception('请选择商品数量');
            }

            $settle['goods']['image'] = UrlServer::setFileUrl($settle['goods']['image']);

            // 提交订单
            $order = IntegralOrder::create([
                'order_sn' => createSn('integral_order', 'order_sn'),
                'user_id' => $params['user_id'],
                'order_source' => $params['client'],
                'delivery_way' => $settle['goods']['delivery_way'],
                'exchange_type' => $settle['goods']['type'],
                'exchange_way' => $settle['goods']['exchange_way'],

                'consignee' => $settle['address']['contact'],
                'province' => $settle['address']['province_id'],
                'city' => $settle['address']['city_id'],
                'district' => $settle['address']['district_id'],
                'address' => $settle['address']['address'],
                'mobile' => $settle['address']['telephone'],

                'order_amount' => $settle['order_amount'],
                'order_integral' => $settle['order_integral'],
                'total_num' => $settle['total_num'],
                'goods_price' => $settle['goods_price'],
                'shipping_price' => $settle['shipping_price'],

                'user_remark' => $params['user_remark'] ?? '',
                'goods_snap' => $settle['goods']
            ]);

            // 扣减应付积分
            if ($settle['order_integral'] > 0) {
                User::where(['id' => $params['user_id']])
                    ->dec('user_integral', $settle['order_integral'])
                    ->update();

                AccountLogLogic::AccountRecord(
                    $params['user_id'],
                    $settle['order_integral'], 2,
                    AccountLog::pay_integral_order,
                    '', $order['id'], $order['order_sn']
                );
            }

            // 兑换方式-积分 且没有运费 扣减积分后 直接支付完成
            if ($settle['goods']['exchange_way'] == IntegralGoodsEnum::EXCHANGE_WAY_INTEGRAL && $settle['order_amount'] <= 0) {
                PayNotifyLogic::handle('integral', $order['order_sn']);
            }
            Db::commit();

            return ['order_id' => $order['id'], 'type' => 'integral'];

        } catch (\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 订单列表
     * @param $user_id
     * @param $type
     * @param $page
     * @param $size
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/3/2 9:39
     */
    public static function lists($user_id, $type, $page, $size)
    {
        $order = new IntegralOrder();

        $where[] = ['del', '=', 0];
        $where[] = ['user_id', '=', $user_id];
        if (isset($type) && $type != '') {
            $where[] = ['order_status', '=', $type];
        }

        $field = [
            'id', 'order_sn', 'order_status', 'pay_status', 'shipping_status', 'shipping_price',
            'delivery_way', 'order_amount', 'total_num', 'order_integral', 'goods_snap',
            'create_time', 'refund_status'
        ];

        $count = $order->where($where)->count();
        $lists = $order->where($where)->field($field)->page($page, $size)
            ->order('id desc')
            ->append(['btns'])
            ->select()->toArray();

        foreach ($lists as &$item) {
            $item['order_status'] = IntegralOrderEnum::getOrderStatus($item['order_status']);
            $goods = $item['goods_snap'];
            $item['goods'] = [
                'image' => UrlServer::getFileUrl($goods['image']),
                'name' => $goods['name'],
                'need_integral' => $goods['need_integral'],
                'need_money' => $goods['need_money'],
                'exchange_way' => $goods['exchange_way'],
            ];
            unset($item['goods_snap']);
        }

        $data = [
            'list' => $lists,
            'page' => $page,
            'size' => $size,
            'count' => $count,
            'more' => is_more($count, $page, $size)
        ];
        return $data;
    }


    /**
     * @notes 订单详情
     * @param $id
     * @return array
     * @author 段誉
     * @date 2022/3/2 10:22
     */
    public static function detail($id)
    {
        $order = IntegralOrder::where(['id' => $id])
            ->withoutField(['content', 'order_source', 'transaction_id', 'refund_amount'])
            ->append(['delivery_address', 'pay_time', 'btns'])
            ->findOrEmpty()->toArray();

        $order['pay_time'] = $order['pay_time'] ? date('Y-m-d H:i:s', $order['pay_time']) :  '-' ;
        $order['shipping_time'] = $order['shipping_time'] ? date('Y-m-d H:i:s', $order['shipping_time']) :  '-' ;
        $order['pay_way'] = PayEnum::getPayWay($order['pay_way']);

        $goods_snap = $order['goods_snap'];
        unset($order['goods_snap']);

        $order['goods'] = [
            'image' => UrlServer::getFileUrl($goods_snap['image']),
            'name' => $goods_snap['name'],
            'exchange_way' => $goods_snap['exchange_way'],
            'need_integral' => $goods_snap['need_integral'],
            'need_money' => $goods_snap['need_money'],
            'total_num' => $order['total_num'],
        ];
        return $order;
    }


    /**
     * @notes 确认收货
     * @param $id
     * @param $user_id
     * @author 段誉
     * @date 2022/3/2 10:59
     */
    public static function confirm($id, $user_id)
    {
        //更新订单状态
        IntegralOrder::update([
            'order_status' => IntegralOrderEnum::ORDER_STATUS_COMPLETE,
            'confirm_time' => time(),
        ], ['id' => $id, 'user_id' => $user_id]);
    }


    /**
     * @notes 删除订单
     * @param $id
     * @param $user_id
     * @author 段誉
     * @date 2022/3/2 10:59
     */
    public static function del($id, $user_id)
    {
        //更新订单状态
        IntegralOrder::update(['del' => 1], ['id' => $id, 'user_id' => $user_id]);
    }


    /**
     * @notes 取消订单
     * @param $id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/3/3 14:56
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


    /**
     * @notes 订单物流轨迹
     * @param $id
     * @return array
     * @author 段誉
     * @date 2022/3/3 17:29
     */
    public static function orderTraces($id)
    {
        $order = IntegralOrder::alias('o')
            ->field('invoice_no, shipping_name, shipping_id, send_type, o.shipping_status,
             o.mobile, o.province, o.city, o.district, o.address,o.total_num,o.goods_snap')
            ->join('integral_delivery d', 'd.order_id = o.id')
            ->where(['o.id' => $id])
            ->findOrEmpty();

        if ($order->isEmpty() || $order['send_type'] != 1) {
            return [];
        }

        //数据合并
        $order_traces = [
            'order' => [
                'tips' => self::getTracesOrderTips($order),
                'image' => UrlServer::getFileUrl($order['goods_snap']['image']),
                'count' => $order['total_num'],
                'invoice_no' => $order['invoice_no'],
                'shipping_name' => empty($order['shipping_name']) ? '-' : $order['shipping_name'],
            ],
            'take' => [
                'contacts' => $order['consignee'],
                'mobile' => $order['mobile'],
                'address' => $order['delivery_address'],
            ],
            'finish' => self::getTracesFinish($order),
            'delivery' => [
                'title' => '运输中',
                'traces' => self::getTraces($order)
            ],
            'shipment' => self::getTracesShipment($order),
            'buy' => [
                'title' => '已下单',
                'tips' => '订单提交成功',
                'time' => date('Y-m-d H:i:s', $order['pay_time'])
            ],
        ];
        return $order_traces;
    }



    /**
     * @notes 订单物流-备注信息
     * @param $order
     * @return string
     * @author 段誉
     * @date 2022/3/3 17:30
     */
    public static function getTracesOrderTips($order)
    {
        $order_tips = '已下单';
        //确认收货
        if ($order['order_status'] == IntegralOrderEnum::ORDER_STATUS_COMPLETE) {
            $order_tips = '交易完成';
        }
        return $order_tips;
    }


    /**
     * @notes 订单物流-待收货信息
     * @param $order
     * @return string[]
     * @author 段誉
     * @date 2022/3/3 17:30
     */
    public static function getTracesShipment($order)
    {
        $shipment = [
            'title' => '已发货',
            'tips' => '',
            'time' => '',
        ];
        //待收货
        if ($order['order_status'] == IntegralOrderEnum::ORDER_STATUS_GOODS) {
            $shipment['tips'] = '商品已出库';
            $shipment['time'] = date('Y-m-d H:i:s', $order['shipping_time']);
        }
        return $shipment;
    }



    /**
     * @notes 订单物流-订单完成信息
     * @param $order
     * @return string[]
     * @author 段誉
     * @date 2022/3/3 17:30
     */
    public static function getTracesFinish($order)
    {
        $finish = [
            'title' => '交易完成',
            'tips' => '',
            'time' => '',
        ];
        //确认收货
        if ($order['order_status'] == IntegralOrderEnum::ORDER_STATUS_COMPLETE) {
            $finish['tips'] = '订单交易完成';
            $finish['time'] = $order['confirm_time'] ? date('Y-m-d H:i:s', $order['confirm_time']) : $order['confirm_time'];
        }
        return $finish;
    }




    /**
     * @notes 订单物流-物流轨迹
     * @param $order
     * @return array|bool
     * @author 段誉
     * @date 2022/3/3 17:31
     */
    public static function getTraces($order)
    {
        $express = ConfigServer::get('express', 'way', '', '');
        $key = ConfigServer::get($express, 'appkey');
        $app = ConfigServer::get($express, 'appsecret');

        // 没有配置,没有发货
        if (empty($express) || empty($key) || empty($app) || $order['shipping_status'] != IntegralOrderEnum::SHIPPING_FINISH) {
            return [];
        }

        //快递配置设置为快递鸟时
        if ($express === 'kdniao') {
            $expressage = (new Kdniao($app, $key, Env::get('app.app_debug', 'true')));
            $shipping_field = 'codebird';
        } else {
            $expressage = (new Kd100($app, $key, Env::get('app.app_debug', 'true')));
            $shipping_field = 'code100';
        }
        //快递编码
        $shipping_code = Db::name('express')->where(['id' => $order['shipping_id']])->value($shipping_field);
        //获取物流轨迹
        if ($shipping_code === 'SF' && $express === 'kdniao') {
            $expressage->logistics($shipping_code, $order['invoice_no'], substr($order['mobile'], -4));
        } else {
            $expressage->logistics($shipping_code, $order['invoice_no']);
        }
        $traces = $expressage->logisticsFormat();
        //获取不到物流轨迹时
        if ($traces == false) {
            $error = $expressage->getError();
            $error = json_decode($error, true);
            if ($express === 'kdniao') {
                if ($error['Success'] == false) {
                    $traces[] = [$error['Reason']];
                }
            } else {
                if ($error['result'] == false) {
                    $traces[] = [$error['message']];
                }
            }
        } else {
            foreach ($traces as &$item) {
                $item = array_values(array_unique($item));
            }
        }
        return $traces;
    }


}
