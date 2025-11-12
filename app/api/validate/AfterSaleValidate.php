<?php

namespace app\api\validate;

use app\common\basics\Validate;
use app\common\server\ConfigServer;
use think\facade\Db;
use app\common\model\after_sale\AfterSale;
use app\common\enum\OrderEnum;

class AfterSaleValidate extends Validate
{
    protected $rule = [
        'id' => 'require',
        'order_id' => 'require|checkSettle|checkRefundAddress|checkAfterSale|checkAbleApply',
        'reason' => 'require',
        'item_id' => 'require',
        'refund_type' => 'require',
        'express_name' => 'require',
        'invoice_no' => 'require|alphaNum',
    ];
    protected $message = [
        'id.require' => '参数错误',
        'reason.require' => '请选择退款原因',
        'order_id.require' => '参数错误',
        'order_id.checkSettle' => '该订单无法申请售后',
        'order_id.checkRefundAddress' => '该商家未设置售后地址',
        'item_id.require' => '参数错误',
        'refund_type.require' => '参数错误',
        'express_name.require' => '请填写物流公司名称',
        'invoice_no.require' => '请填写快递单号',
        'invoice_no.alphaNum' => '请填写正确的快递单号',
    ];
    protected $scene = [
        'add' => ['item_id', 'order_id', 'reason', 'refund_type'],
        'sceneInfo' => ['order_id', 'item_id'],
        'goodsInfo' => ['item_id'],
        'express' => ['id', 'express_name', 'invoice_no'],
        'cancel' => ['id'],
        'detail' => ['id'],
        'again' => ['id', 'reason', 'refund_type'],
    ];

    /**
     * @notes 验证该订单是否已经申请售后
     * @param $value
     * @param $rule
     * @param $data
     * @author suny
     * @date 2021/7/29 4:43 下午
     */
    public function checkAfterSale($value, $rule, $data)
    {
        $condition = [
            'order_id' => $value,
            'item_id' => $data['item_id'],
            'del' => 0
        ];
        $after_sale = AfterSale::where($condition)->find();
        if (!$after_sale) {
            return true;
        } else {
            return '该订单已申请过售后，请勿重复申请';
        }
    }

    /**
     * @notes 判断该订单是否已经结算
     * @param $value
     * @param $rule
     * @param $data
     * @return bool
     * @author suny
     * @date 2021/7/13 6:26 下午
     */
    public function checkSettle($value, $rule, $data)
    {

        $settle_id = Db::name('order')->where('id', $value)->value('settle_id');
        if ($settle_id == 1) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @notes 验证退货地址
     * @param $value
     * @param $rule
     * @param $data
     * @return bool
     * @author suny
     * @date 2021/7/13 6:27 下午
     */
    public function checkRefundAddress($value, $rule, $data)
    {

        if ($data['refund_type'] == 1) {
            $shop_id = Db::name('order')->where('id', $value)->value('shop_id');
            $refund_address = Db::name('shop')->where('id', $shop_id)->value('refund_address');
            if (empty($refund_address) || $refund_address == '') {
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }

    //验证订单是否在售后时间内
    protected function checkAbleApply($value, $rule, $data)
    {
        $now = time();
        $where = [];
        $where[] = ['o.id', '=', $value];
        $where[] = ['g.item_id', '=', $data['item_id']];
        $where[] = ['o.order_status', 'in', [OrderEnum::ORDER_STATUS_GOODS, OrderEnum::ORDER_STATUS_COMPLETE]];

        $order = Db::name('order')->alias('o')
            ->field('o.order_status,o.confirm_take_time,g.refund_status')
            ->join('order_goods g', 'o.id = g.order_id')
            ->where($where)
            ->find();


        $refund_days = ConfigServer::get('transaction', 'order_after_sale_days', 7);
        if ($refund_days == 0) {
            return true;
        }

        if ($order['order_status'] == OrderEnum::ORDER_STATUS_COMPLETE) {
            $check_time = intval($order['confirm_take_time'] + ($refund_days * 24 * 60 * 60));
            if ($now > $check_time) {
                return '不在售后时间内';
            }
        }

        return true;
    }
}