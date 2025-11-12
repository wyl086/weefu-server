<?php

namespace app\api\validate;

use app\common\basics\Validate;
use app\common\enum\IntegralGoodsEnum;
use app\common\enum\IntegralOrderEnum;
use app\common\enum\PayEnum;
use app\common\model\integral\IntegralOrder;

/**
 * 积分订单验证
 * Class IntegralOrderValidate
 * @package app\api\validate
 */
class IntegralOrderValidate extends Validate
{
    protected $rule = [
        'id' => 'require|number|checkOrder',
    ];

    protected $message = [
        'id.require' => '参数缺失',
        'id.number' => '参数类型错误',
    ];

    public function sceneDetail()
    {
        return $this->only(['id']);
    }


    public function sceneCancel()
    {
        return $this->only(['id'])->append('id', 'checkCancel');
    }


    public function sceneConfirm()
    {
        return $this->only(['id'])->append('id', 'checkConfirm');
    }

    public function sceneTraces()
    {
        return $this->only(['id']);
    }

    public function sceneDel()
    {
        return $this->only(['id'])->append('id','checkDel');
    }



    // 验证订单
    protected function checkOrder($value, $rule, $data)
    {
        $condition = ['id' => $value, 'user_id' => $data['user_id']];
        $order = IntegralOrder::where($condition)->findOrEmpty();

        if ($order->isEmpty()) {
            return '订单不存在';
        }

        if ($order['del'] == 1) {
            return '订单已删除';
        }
        return true;
    }


    protected function checkConfirm($value, $rule, $data)
    {
        $order = IntegralOrder::findOrEmpty($value);

        if ($order['order_status'] < IntegralOrderEnum::ORDER_STATUS_DELIVERY) {
            return '订单未发货';
        }

        if ($order['order_status'] == IntegralOrderEnum::ORDER_STATUS_COMPLETE) {
            return '订单已完成';
        }

        return true;
    }


    public function checkDel($value, $rule, $data)
    {
        $order = IntegralOrder::findOrEmpty($value);

        // 订单状态为 已关闭 且 [未支付 或者 已退款才可以删除]
        if ($order['order_status'] == IntegralOrderEnum::ORDER_STATUS_DOWN) {
            if ($order['pay_status'] == PayEnum::UNPAID  || $order['refund_status'] == 1) {
                return true;
            }
        }
        return '订单不可删除';
    }


    // 取消
    public function checkCancel($value, $rule, $data)
    {
        $order = IntegralOrder::findOrEmpty($value);
        $goods_snap = $order['goods_snap'];

        // 商品类型为红包的不可取消
        if ($goods_snap['type'] == IntegralGoodsEnum::TYPE_BALANCE) {
            return '此订单不可取消';
        }

        if ($order['order_status'] >= IntegralOrderEnum::ORDER_STATUS_GOODS) {
            return '此订单不可取消';
        }

        return true;
    }


}
