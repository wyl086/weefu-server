<?php

namespace app\shop\validate\express_assistant;

use app\common\basics\Validate;
use app\common\enum\OrderEnum;
use app\common\enum\PayEnum;
use app\common\enum\TeamEnum;
use app\common\model\Express;
use app\common\model\face_sheet\FaceSheetSender;
use app\common\model\face_sheet\FaceSheetTemplate;
use app\common\model\order\Order;
use app\common\model\team\TeamJoin;

/**
 * 电子面单打印
 * Class FaceSheetOrderValidate
 * @package app\shop\validate\kefu
 */
class FaceSheetOrderValidate extends Validate
{
    protected $rule = [
        'order_id' => 'require|checkOrder',
        'template_id' => 'require|checkTemplate',
        'sender_id' => 'require|checkSender',
    ];

    protected $message = [
        'order_ids.require' => '请选择要打印的订单',
        'order_ids.array' => '订单参数须为数组格式',
        'template_id.require' => '请选择电子模板',
        'sender_id.require' => '请选择发件人',
    ];


    /**
     * @notes 校验订单是否可打印
     * @param $value
     * @param $rrule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2023/2/14 9:49
     */
    public function checkOrder($value, $rrule, $data)
    {
        $order = Order::where(['shop_id' => $data['shop_id'], 'id' => $value])->findOrEmpty()->toArray();
        if (empty($order)) {
            return '不存在id为' . $value . '的订单';
        }
        if ($order['order_status'] != OrderEnum::ORDER_STATUS_DELIVERY) {
            return '订单' . $order['order_sn'] . '不是待发货状态';
        }
        if ($order['pay_status'] == PayEnum::UNPAID) {
            return '订单' . $order['order_sn'] . '未支付';
        }
        if ($order['delivery_type'] != OrderEnum::DELIVERY_TYPE_EXPRESS) {
            return '订单' . $order['order_sn'] . '配送方式不是快递发货';
        }
        if ($order['refund_status'] > OrderEnum::REFUND_STATUS_NO_REFUND) {
            return '订单' . $order['order_sn'] . '已发生退款';
        }
        if ($order['order_type'] == OrderEnum::TEAM_ORDER && !$this->checkTeamJoinOrder($order['id'])) {
            return '拼团订单:' . $order['order_sn'] . '需要拼团成功后才能打印';
        }
        return true;
    }


    /**
     * @notes 校验面单模板
     * @param $templateId
     * @return bool|string
     * @author 段誉
     * @date 2023/2/13 18:15
     */
    public function checkTemplate($templateId, $rule, $data)
    {
        $template = FaceSheetTemplate::where(['shop_id' => $data['shop_id'], 'id' => $templateId])->findOrEmpty($templateId);
        if ($template->isEmpty()) {
            return '电子面单模板不存在';
        }
        if (Express::findOrEmpty($template['express_id'])->isEmpty()) {
            return '电子面单模板中的快递公司不存在';
        }
        return true;
    }


    /**
     * @notes 校验发件人模板
     * @param $senderId
     * @return bool|string
     * @author 段誉
     * @date 2023/2/13 18:16
     */
    public function checkSender($senderId, $rule, $data)
    {
        $sender = FaceSheetSender::where(['shop_id' => $data['shop_id'], 'id' => $senderId])->findOrEmpty();
        if ($sender->isEmpty()) {
            return '发件人模板不存在';
        }
        return true;
    }


    /**
     * @notes 校验拼团订单
     * @param $orderId
     * @return bool
     * @author 段誉
     * @date 2023/2/13 18:11
     */
    public function checkTeamJoinOrder($orderId)
    {
        $teamJoin = TeamJoin::where(['order_id' => $orderId])->findOrEmpty();
        if (!$teamJoin->isEmpty() && $teamJoin['status'] == TeamEnum::TEAM_STATUS_SUCCESS) {
            // 拼团成功
            return true;
        }
        return false;
    }


}