<?php


namespace app\common\command;


use app\admin\logic\distribution\DistributionLevelLogic;
use app\common\enum\DistributionOrderGoodsEnum;
use app\common\model\AccountLog;
use app\common\model\after_sale\AfterSale;
use app\common\model\distribution\DistributionOrderGoods;
use app\common\model\order\Order;
use app\common\model\user\User;
use app\common\server\ConfigServer;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Db;
use think\facade\Log;

class DistributionOrder extends Command
{
    protected function configure()
    {
        $this->setName('distribution_order')
            ->setDescription('结算分销订单');
    }

    protected function execute(Input $input, Output $output)
    {
        Db::startTrans();
        try {
            // 1、获取结算时间
            $time = time();
            $afterSaleTime = ConfigServer::get('distribution', 'settlement_days', 7);
            $afterSaleTime = intval($afterSaleTime * 24 * 60 * 60);

            // 2、查询可以结算的订单
            $model = new DistributionOrderGoods();
            $orders = $model->alias('DOG')->field([
                    'O.id as order_id, O.order_status, O.confirm_take_time',
                    'DOG.id as distribution_id, DOG.sn, DOG.money',
                    'DOG.user_id, DOG.order_goods_id'
                ])
                ->join('order_goods OG', 'OG.id = DOG.order_goods_id')
                ->join('order O', 'O.id = OG.order_id')
                ->whereRaw("O.confirm_take_time+$afterSaleTime < $time")
                ->where([
                    ['DOG.status', '=', DistributionOrderGoodsEnum::STATUS_WAIT_HANDLE],
                ])
                ->limit(100)
                ->select()->toArray();

            foreach ($orders as &$order) {
                //当前分佣订单是否可结算
                if (false === self::isSettle($order)) {
                    continue;
                }

                // 增加用户佣金
                $earnings = User::where('id', $order['user_id'])->value('earnings');
                User::update([
                    'earnings'    => $earnings + $order['money'],
                    'update_time' => $time,
                    'id' => $order['user_id']
                ]);

                // 记录流水
                AccountLog::create([
                    'log_sn'        => createSn('account_log', 'log_sn', '', 4),
                    'user_id'       => $order['user_id'],
                    'source_type'   => AccountLog::distribution_inc_earnings,
                    'source_id'     => $order['distribution_id'],
                    'source_sn'     => $order['sn'],
                    'change_amount' => $order['money'],
                    'left_amount'   => $earnings+$order['money'],
                    'change_type'   => 1,
                    'remark'        => '分销佣金增加'
                ]);

                // 更新分销订单状态
                DistributionOrderGoods::update([
                    'status'      => DistributionOrderGoodsEnum::STATUS_SUCCESS,
                    'update_time' => $time,
                    'settlement_time' => $time,
                    'id'=>$order['distribution_id']
                ]);

                // 更新订单分销佣金
                $orderModel = Order::findOrEmpty($order['order_id']);
                $orderModel->distribution_money = $orderModel->distribution_money + $order['money'];
                $orderModel->update_time = $time;
                $orderModel->save();

                // 更新分销会员等级
                DistributionLevelLogic::updateDistributionLevel($order['user_id']);

            }

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            Log::write('分销结算异常:'.$e->getMessage());
        }
    }

    /**
     * @Notes: 是否可以结算分佣订单 (检查是否有售后记录 没有则可结算, 有则需要检查售后记录状态)
     * @Author: 张无忌
     * @param $order
     * @return bool
     */
    protected function isSettle($order)
    {
        // 订单是否在售后(正在退款或已退款)
        $check = (new AfterSale())->where([
            'order_id'       => $order['order_id'],
            'order_goods_id' => $order['order_goods_id'],
            'del'=>0
        ])->findOrEmpty()->toArray();

        if (!$check) {
            return true;
        }

        // 有售后订单记录且状态 $no_settlement中的 不结算分佣订单
        $no_settlement = [
            AfterSale::STATUS_APPLY_REFUND,       //申请退款
            AfterSale::STATUS_WAIT_RETURN_GOODS,  //商品待退货
            AfterSale::STATUS_WAIT_RECEIVE_GOODS, //商家待收货
        ];

        // 不结算且分佣订单改为已失效
        $set_fail = [
            AfterSale::STATUS_WAIT_REFUND,    //等待退款
            AfterSale::STATUS_SUCCESS_REFUND, //退款成功
        ];

        // 售后情况不明 不结算
        if (in_array($check['status'], $no_settlement)) {
            return false;
        }

        // 分佣订单更新为已失效  不结算
        if (in_array($check['status'], $set_fail)) {
            DistributionOrderGoods::update([
                'status'      => DistributionOrderGoodsEnum::STATUS_ERROR,
                'update_time' => time()
            ], ['id'=>$order['distribution_id']]);

            return false;
        }

        return true;
    }
}