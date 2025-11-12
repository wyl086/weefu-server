<?php


namespace app\common\command;


use app\common\enum\OrderEnum;
use app\common\model\CouponList;
use app\common\model\goods\Goods;
use app\common\model\goods\GoodsItem;
use app\common\model\order\Order;
use app\common\server\ConfigServer;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Db;
use think\facade\Log;

class OrderClose extends Command
{
    protected function configure()
    {
        $this->setName('order_close')
            ->setDescription('关闭订单');
    }

    protected function execute(Input $input, Output $output)
    {
        try {
            $time = time();
            $order_cancel_time = ConfigServer::get('transaction', 'unpaid_order_cancel_time', 60);
            // 配置0或为空时不取消订单
            if (empty($order_cancel_time)) {
                return true;
            }

            $order_cancel_time = $order_cancel_time * 60;

            $model = new Order();
            $order_list = $model->field(true)
                ->whereRaw("create_time+$order_cancel_time < $time")
                ->where([
                    ['order_type', '<>', OrderEnum::TEAM_ORDER],
                    ['order_status', '=', OrderEnum::ORDER_STATUS_NO_PAID],
                    ['pay_status', '=', OrderEnum::PAY_STATUS_NO_PAID]
                ])->with(['orderGoods'])
                ->select()->toArray();

            $order_ids          = []; //更新的订单
            $update_total_stock = []; //更新总库存
            $update_stock       = []; //更新规格库存
            $total_stock_num    = []; //总库存
            $stock_num          = []; //规格库存
            $update_coupon_ids  = []; //更新优惠券状态
            foreach ($order_list as $order) {
                $order_ids[] = $order['id'];
                //返回优惠券
                if ($order['coupon_list_id']) {
                    $update_coupon_ids[] = $order['coupon_list_id'];
                }

                foreach ($order['orderGoods'] as $order_goods) {
                    //更新商品总库存数据
                    if (isset($update_total_stock[$order_goods['goods_id']])) {
                        $total_stock_num[$order_goods['goods_id']] = $total_stock_num[$order_goods['goods_id']] + $order_goods['goods_num'];
                        $update_total_stock[$order_goods['goods_id']]['stock'] = Db::raw('stock+' . $total_stock_num[$order_goods['goods_id']]);
                    } else {
                        $total_stock_num[$order_goods['goods_id']] = $order_goods['goods_num'];
                        $update_total_stock[$order_goods['goods_id']] = [
                            'id' => $order_goods['goods_id'],
                            'stock' => Db::raw('stock+' . $total_stock_num[$order_goods['goods_id']])
                        ];
                    }
                    //更新商品规格库存数据
                    if (isset($update_stock[$order_goods['item_id']])) {
                        $stock_num[$order_goods['item_id']] = $stock_num[$order_goods['item_id']] + $order_goods['goods_num'];
                        $update_stock[$order_goods['item_id']]['stock'] = Db::raw('stock+' . $stock_num[$order_goods['item_id']]);
                    } else {
                        $stock_num[$order_goods['item_id']] = $order_goods['goods_num'];
                        $update_stock[$order_goods['item_id']] = [
                            'id' => $order_goods['item_id'],
                            'stock' => Db::raw('stock+' . $stock_num[$order_goods['item_id']])
                        ];
                    }
                }
            }

            // 更新订单状态为关闭
            if ($order_ids) {
                $update_data = [
                    'order_status' => OrderEnum::ORDER_STATUS_DOWN,
                    'update_time'  => $time,
                ];

                $model->where(['id' => $order_ids])->update($update_data);
            }


            //批量更新库存
            if($update_total_stock){
                (new Goods())->saveAll(array_values($update_total_stock));
                (new GoodsItem())->saveAll(array_values($update_stock));
            }

            if($update_coupon_ids){
                $update_coupon = [
                    'status'        => 0,
                    'use_time'      => '',
                    'order_id'      => '',
                    'update_time'   => $time,
                ];
                (new CouponList())->where(['id'=>$update_coupon_ids])->update($update_coupon);
            }
            return true;
        } catch (\Exception $e) {
            Log::write('自动关闭订单异常:'.$e->getMessage());
            return false;
        }
    }
}