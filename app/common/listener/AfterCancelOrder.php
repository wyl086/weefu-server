<?php

namespace app\common\listener;

use app\common\enum\DistributionOrderGoodsEnum;
use app\common\enum\OrderLogEnum;
use app\common\logic\OrderLogLogic;
use app\common\model\coupon\CouponList;
use app\common\model\distribution\DistributionOrderGoods;
use app\common\model\goods\Goods;
use app\common\model\goods\GoodsItem;
use app\common\model\order\OrderGoods;
use app\common\model\order\Order;

/**
 * 取消订单后的操作
 */
class AfterCancelOrder
{
    public function handle($params)
    {
        try {
            $type = $params['type'];
            $channel = $params['channel'];
            $order_id = $params['order_id'];
            $handle_id = $params['handle_id'] ?? 0;

            $order = Order::findOrEmpty($order_id);
            if ($order->isEmpty()) {
                return false;
            }

            // 分销订单置为失效状态
            DistributionOrderGoods::where('order_id', $order->id)->update(['status' => DistributionOrderGoodsEnum::STATUS_ERROR, 'update_time' => time()]);

            // 回退库存
            self::backStock($order);

            // 返回优惠券
            self::backCoupon($order);

            //订单日志
            OrderLogLogic::record(
                $type,
                $channel,
                $order->id,
                $handle_id,
                $channel
            );

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @notes 回退库存
     */
    public static function backStock($order)
    {
        $orderGoods = OrderGoods::where('order_id', $order->id)->select()->toArray();
        foreach ($orderGoods as $item) {
            // SKU库存更新
            $goodsItem = GoodsItem::findOrEmpty($item['item_id']);
            if ($goodsItem->isEmpty()) {
                continue;
            }
            $goodsItem->stock += $item['goods_num'];
            $goodsItem->save();
            // 商品总库存更新
            $goods = Goods::findOrEmpty($item['goods_id']);
            if ($goods->isEmpty()) {
                continue;
            }
            $goods->stock += $item['goods_num'];
            $goods->save();
        }
    }

    /**
     * @notes 回退优惠券
     */
    public static function backCoupon($order)
    {
        $countList = CouponList::where('order_id', $order->id)->findOrEmpty();
        if ($countList->isEmpty()) {
            return false;
        }
        $countList->status = 0;
        $countList->use_time = '';
        $countList->order_id = '';
        $countList->update_time = time();
        $countList->save();
    }
}