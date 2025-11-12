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


use app\common\enum\GoodsEnum;
use app\common\enum\OrderEnum;
use app\common\model\goods\Goods;
use app\common\model\order\Order;

/**
 * 虚拟商品逻辑
 * Class GoodsVirtualLogic
 * @package app\common\logic
 */
class GoodsVirtualLogic
{

    /**
     * @notes 订单之后虚拟配送
     * @param $orderIds
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/4/12 11:37
     */
    public static function afterPayVirtualDelivery($orderIds)
    {
        $orderIds = is_array($orderIds) ? $orderIds : [$orderIds];
        $orders = Order::with(['order_goods'])->whereIn('id', $orderIds)->select()->toArray();

        foreach ($orders as $order) {

            $goodsId = $order['order_goods'][0]['goods_id'] ?? 0;
            $goods = Goods::findOrEmpty($goodsId);

            // 商品不存在,不是虚拟商品,买家付款后不是自动发货
            if ($goods->isEmpty() || $goods['type'] != GoodsEnum::TYPE_VIRTUAL) {
                continue;
            }

            $data = [
                'delivery_content' => $goods['delivery_content'],
                'update_time' => time(),
            ];

            // 商品为支付后自动发货
            if ($goods['after_pay'] == GoodsEnum::AFTER_PAY_AUTO_DELIVERY) {
                $data['order_status'] = OrderEnum::ORDER_STATUS_GOODS; // 待收货状态
                $data['delivery_type'] = OrderEnum::DELIVERY_TYPE_VIRTUAL; // 发货方式为虚拟发货
                $data['shipping_status'] = OrderEnum::SHIPPING_FINISH; // 已发货
                $data['shipping_time'] = time();

                // 自动完成订单
                if ($goods['after_delivery'] == GoodsEnum::AFTER_DELIVERY_AUTO_COMFIRM) {
                    $data['order_status'] = OrderEnum::ORDER_STATUS_COMPLETE;// 已完成
                    $data['confirm_take_time'] = time();
                }
            }
            Order::where(['id' => $order['id']])->update($data);
        }

        return true;
    }



    /**
     * @notes 商家手动发货
     * @param $orderId
     * @param null $content
     * @return bool|string
     * @author 段誉
     * @date 2022/4/12 11:44
     */
    public static function shopSelfDelivery($orderId, $content = null)
    {
        $order = Order::with(['order_goods'])->where('id', $orderId)->findOrEmpty()->toArray();

        $goodsId = $order['order_goods'][0]['goods_id'] ?? 0;
        $goods = Goods::findOrEmpty($goodsId);

        // 商品不存在,不是虚拟商品,买家付款后不是自动发货
        if ($goods->isEmpty() || $goods['type'] != GoodsEnum::TYPE_VIRTUAL) {
            return '虚拟商品信息不存在';
        }

        $data = [
            'order_status' => OrderEnum::ORDER_STATUS_GOODS, // 待收货状态
            'delivery_type' => OrderEnum::DELIVERY_TYPE_VIRTUAL, // 发货方式为虚拟发货
            'delivery_content' => empty($content) ? $goods['delivery_content'] : $content,
            'shipping_status' => OrderEnum::SHIPPING_FINISH, // 已发货
            'shipping_time' => time(),
            'update_time' => time(),
        ];

        // 自动完成订单
        if ($goods['after_delivery'] == GoodsEnum::AFTER_DELIVERY_AUTO_COMFIRM) {
            $data['order_status'] = OrderEnum::ORDER_STATUS_COMPLETE;// 已完成
            $data['confirm_take_time'] = time();
        }
        Order::where(['id' => $order['id']])->update($data);
        return true;
    }


}