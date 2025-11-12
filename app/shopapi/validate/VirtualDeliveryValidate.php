<?php


namespace app\shopapi\validate;


use app\common\basics\Validate;
use app\common\model\order\Order;

/**
 * 虚拟发货验证
 * Class virtualDeliveryValidate
 * @package app\shop\validate\order
 */
class VirtualDeliveryValidate extends Validate
{
    protected $rule = [
        'order_id' => 'require|checkOrder',
        'delivery_content' => 'require',
    ];

    protected $message = [
        'order_id.require' => '缺少ID字段',
        'delivery_content.require' => '请填写发货内容',
    ];

    /**
     * @notes 校验订单信息
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2022/4/7 18:26
     */
    protected function checkOrder($value, $rule, $data)
    {
        $order = Order::findOrEmpty($value);
        if ($order->isEmpty()) {
            return '订单不存在';
        }

        if ($order['del'] == 1) {
            return '订单已删除';
        }

        if ($order['shipping_status'] == 1) {
            return '此订单已发货';
        }
        return true;
    }
}