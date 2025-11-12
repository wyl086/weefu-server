<?php


namespace app\common\command;


use app\common\enum\OrderEnum;
use app\common\enum\OrderLogEnum;
use app\common\enum\TeamEnum;
use app\common\logic\OrderRefundLogic;
use app\common\model\order\Order;
use app\common\model\team\TeamActivity;
use app\common\model\team\TeamFound;
use app\common\model\team\TeamJoin;
use app\common\server\ConfigServer;
use Exception;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Db;
use think\facade\Log;

class TeamEnd extends Command
{
    protected function configure()
    {
        $this->setName('team_end')
            ->setDescription('结束已超时的拼团');
    }

    protected function execute(Input $input, Output $output)
    {
        try {
            $time = time();
            $automatic = ConfigServer::get('team', 'automatic', 0);

            // 获取并关闭已结束的活动
            $team_ids = (new TeamActivity())->where([['activity_end_time', '<=', $time]])->column('id');
            (new TeamActivity())->whereIn('id', $team_ids)->update(['status'=>0, 'update_time'=>$time]);

            // 找出拼团中&&拼团有效期结束的拼团记录
            $map1 = array(
                ['invalid_time', '<=', $time],
                ['status', '=', 0]
            );
            $map2 = array(
                ['team_activity_id', 'in', $team_ids],
                ['status', '=', 0]
            );
            $teamFound1 = (new TeamFound())->whereOr([$map1, $map2])->select()->toArray();
            $teamFound2 = (new TeamFound())->alias('TF')
                                ->where('TF.people','exp',' <= TF.join ')
                                ->where('status', '=', 0)
                                ->select()->toArray();

            $teamFound = $teamFound1;
            if (empty($teamFound1)) {
                $teamFound = array_merge($teamFound1, $teamFound2);
            } else {
                $found_ids = array_column($teamFound1, "id");
                if (!empty($teamFound2)) {
                    foreach ($teamFound2 as $item) {
                        if (!in_array($item['id'], $found_ids)) {
                            $teamFound[] = $item;
                        }
                    }
                }
            }

            // 结束拼团时间到的团
            $teamJoinModel = new TeamJoin();
            foreach ($teamFound as $found) {
                $teamJoin = $teamJoinModel->alias('TJ')
                    ->field(['TJ.*,O.order_sn,O.order_status,O.pay_status,O.refund_status,O.order_amount'])
                    ->where(['team_id' => $found['id']])
                    ->join('order O', 'O.id=TJ.order_id')
                    ->select()->toArray();

                // 已支付的数量
                $payNums = array_column($teamJoin, 'pay_status');
                $payCount = 0;
                foreach ($payNums as $i) {
                    if ($i == 1) {
                        $payCount += 1;
                    }
                }

                // 此团有未支付订单: 关闭拼团,关闭订单,给已支付的退款
                if (in_array(1, array_column($teamJoin, 'pay_status'))) {
                    if ($automatic == 1 || ($found['people'] == $found['join'] && $found['join'] == $payCount)) {
                        $this->teamSuccess($teamJoin, $found, $time);
                    } else {
                        $this->teamFail($teamJoin, $found, $time);
                    }
                } else {
                    $this->teamFail($teamJoin, $found, $time);
//                    $this->teamSuccess($teamJoin, $found, $time);
                }
            }
        } catch (Exception $e) {
            Log::write('拼团关闭异常'.$e->getMessage());
            throw new \think\Exception($e->getMessage());
        }
    }

    /**
     * @Notes: 拼团失败
     * @Author: 张无忌
     * @param $teamJoin (参团列表数据)
     * @param $found (开团的数据)
     * @param $time (时间)
     * @throws \think\Exception
     */
    private function teamFail($teamJoin, $found, $time)
    {
        Db::startTrans();
        try {
            TeamFound::update(['status'=>TeamEnum::TEAM_STATUS_FAIL, 'team_end_time'=>$time], ['id'=>$found['id']]);
            foreach ($teamJoin as $item) {
                TeamJoin::update(['status' => TeamEnum::TEAM_STATUS_FAIL, 'update_time' => $time], ['id' => $item['id']]);
                if ($item['order_status'] == OrderEnum::ORDER_STATUS_DOWN) continue;
                if ($item['refund_status'] != OrderEnum::REFUND_STATUS_NO_REFUND) continue;
                $order = (new Order())->findOrEmpty($item['order_id'])->toArray();
                // 取消订单
                OrderRefundLogic::cancelOrder($order['id'], OrderLogEnum::TYPE_SYSTEM);
                if ($order['pay_status'] == OrderEnum::PAY_STATUS_PAID) {
                    // 更新订单状态
                    OrderRefundLogic::cancelOrderRefundUpdate($order);
                    // 订单退款
                    OrderRefundLogic::refund($order, $order['order_amount'], $order['order_amount']);
                }
            }
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            throw new \think\Exception($e->getMessage());
        }
    }

    /**
     * @Notes: 拼团成功
     * @Author: 张无忌
     * @param $teamJoin (该团的,参团数据)
     * @param $found (该团的, 开团时间)
     * @param $time (时间)
     * @throws \think\Exception
     */
    private function teamSuccess($teamJoin, $found, $time)
    {
        Db::startTrans();
        try {
            TeamFound::update(['status'=>TeamEnum::TEAM_STATUS_SUCCESS, 'team_end_time'=>$time], ['id'=>$found['id']]);
            foreach ($teamJoin as $item) {
                TeamJoin::update(['status'=>TeamEnum::TEAM_STATUS_SUCCESS, 'update_time'=>$time], ['id'=>$item['id']]);
            }
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            throw new \think\Exception($e->getMessage());
        }
    }
}