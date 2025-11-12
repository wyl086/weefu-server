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

use app\common\enum\PayEnum;
use app\common\model\distribution\DistributionOrderGoods;
use app\common\model\Pay;
use app\common\model\user\User;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use app\common\model\order\Order;
use think\facade\Log;

class UserDistribution extends Command
{
    protected function configure()
    {
        $this->setName('user_distribution')
            ->setDescription('更新会员分销信息');
    }

    protected function execute(Input $input, Output $output)
    {
        try {
            $userModel = new User();
            $users = $userModel->alias('u')
                ->field('d.*')
                ->join('user_distribution d', 'd.user_id = u.id')
                ->where(['u.del' => 0])
                ->select()->toArray();

            if (!$users) {
                return true;
            }

            foreach ($users as $user) {
                //粉丝数量
                $where1 = [
                    ['first_leader', '=', $user['user_id']],
                ];
                $where2 = [
                    ['second_leader', '=', $user['user_id']],
                ];
                $fans = User::whereOr([$where1, $where2])->count();

                //分销订单信息
                $distribution = DistributionOrderGoods::where(['user_id' => $user['user_id']])
                    ->field('sum(money) as money, count(id) as order_num')
                    ->find();

                //订单信息
                $order = Order::where([
                    'user_id' => $user['user_id'],
                    'pay_status' => PayEnum::ISPAID,
                    'refund_status' => 0
                ])
                ->field('sum(order_amount) as order_amount, count(id) as order_num')
                ->find();

                $data = [
                    'distribution_order_num' => $distribution['order_num'] ?? 0,
                    'distribution_money' => $distribution['money'] ?? 0,
                    'order_num' => $order['order_num'] ?? 0,
                    'order_amount' => $order['order_amount'] ?? 0,
                    'fans' => $fans,
                    'update_time' => time(),
                ];

                //更新会员分销信息表
                \app\common\model\user\UserDistribution::where('user_id', $user['user_id'])->update($data);
            }
            return true;
        } catch (\Exception $e) {
            Log::write('自动更新会员分销信息异常:'.$e->getMessage());
            return false;
        }
    }

}