<?php

namespace app\admin\validate\order;

use app\common\enum\OrderEnum;
use app\common\model\team\TeamJoin;
use think\Validate;
use app\common\model\order\Order;
use app\common\model\team\Team;

class OrderValidate extends Validate
{
    /**
     * @notes 发货操作验证
     * @param $post
     * @return bool|string
     * @author suny
     * @date 2021/7/14 10:10 上午
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\db\exception\DataNotFoundException
     */
    protected function checkDelivery($post)
    {

        $id = $post['id'];
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

        if ($order['order_type'] == OrderEnum::TEAM_ORDER) {
            $join = TeamJoin::where(['order_id' => $order['id']])->findOrEmpty();
            if ($join['status'] != Team::STATUS_SUCCESS) {
                return '已支付的拼团订单需要等待拼团成功后才能发货';
            }
        }

        return true;
    }
}
