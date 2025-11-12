<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\common\command;


use app\common\enum\OrderEnum;
use app\common\logic\AccountLogLogic;
use app\common\model\AccountLog;
use app\common\model\order\Order;
use app\common\model\user\User;
use app\common\server\ConfigServer;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Log;

class AwardIntegral extends Command
{
    protected function configure()
    {
        $this->setName('award_integral')
            ->setDescription('结算消费赠送积分');
    }

    protected function execute(Input $input, Output $output)
    {
        try {
            $time = time();
            $config = ConfigServer::get('transaction', 'order_after_sale_days', 7);

            $finish_limit = $config * 24 * 60 * 60;
            $orders = Order::field(true)->where([
                ['del', '=', 0],
                ['award_integral_status', '>', 0],
                ['is_award_integral', '=', 0],
                ['pay_status', '=', 1]
            ])->select()->toArray();

            foreach ($orders as $order) {
                if ($order['award_integral'] <= 0) {
                    continue;
                }
                if ($order['award_integral_status'] == OrderEnum::ORDER_DELIVERY && $order['order_status'] < OrderEnum::ORDER_STATUS_GOODS) {
                    continue;
                }
                if ($order['award_integral_status'] == OrderEnum::ORDER_FINISH && $order['order_status'] < OrderEnum::ORDER_STATUS_COMPLETE) {
                    continue;
                }
                if ($order['award_integral_status'] == OrderEnum::ORDER_AFTER_SALE_OVER && ($order['order_status'] < OrderEnum::ORDER_STATUS_COMPLETE || ($finish_limit + $order['confirm_take_time']) > $time)) {
                    continue;
                }
                User::where('id',$order['user_id'])->inc('user_integral', $order['award_integral'])->update();
                Order::update(['is_award_integral'=>1,'update_time'=>$time],['id'=>$order['id']]);
                AccountLogLogic::AccountRecord($order['user_id'], $order['award_integral'], 1, AccountLog::consume_award_integral, '', $order['id'], $order['order_sn']);
            }

            return true;
        } catch (\Exception $e) {
            Log::write('结算消费赠送积分异常:'.$e->getMessage());
            return false;
        }
    }
}