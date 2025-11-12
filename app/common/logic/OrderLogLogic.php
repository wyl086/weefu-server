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

use app\common\enum\OrderLogEnum;
use app\common\model\order\OrderLog;

/**
 * 订单记录日志
 * Class OrderLogLogic
 * @package app\common\logic
 */
class OrderLogLogic
{
    public static function record($type, $channel, $order_id, $handle_id, $content, $desc = '')
    {
        if (empty($content)) {
            return true;
        }
        $log = new OrderLog();
        $log->type = $type;
        $log->order_id = $order_id;
        $log->channel = $channel;
        $log->handle_id = $handle_id;
        $log->content = OrderLogEnum::getLogDesc($content);
        $log->create_time = time();

        if ($desc != '') {
            $log->content = OrderLogEnum::getLogDesc($content) . '(' . $desc . ')';
        }

        $log->save();
    }
}