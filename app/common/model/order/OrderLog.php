<?php

namespace app\common\model\order;

use app\common\basics\Models;
use app\common\enum\OrderLogEnum;
use think\facade\Db;

/**
 * Class OrderLog
 * @package app\common\model\order
 */
class OrderLog extends Models
{
    /**
     * @notes 订单日志
     * @param $order_id
     * @return array|array[]|\array[][]|\array[][][]
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author suny
     * @date 2021/7/13 6:45 下午
     */
    public static function getOrderLog($order_id)
    {

        $logs = Db::name('order_log')
            ->where('order_id', $order_id)
            ->select()->toArray();
        foreach ($logs as &$log) {
            $log['create_time'] = date('Y-m-d H:i:s', $log['create_time']);
            $log['channel'] = OrderLogEnum::getLogDesc($log['channel']);
        }
        return $logs;
    }

}
