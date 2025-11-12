<?php

namespace app\api\validate;

use app\common\basics\Validate;
use app\common\enum\OrderEnum;
use app\common\enum\OrderInvoiceEnum;
use app\common\model\order\Order;
use app\common\model\order\OrderInvoice;

/**
 * 订单发票验证
 * Class OrderInvoiceValidate
 * @package app\api\validate
 */
class OrderInvoiceValidate extends Validate
{

    protected $rule = [
        'id' => 'require|checkInvoice', // 发票id
        'shop_id' => 'require', // 门店id
        'order_id' => 'require|checkOrder', // 订单id
        'type' => 'require', // 发票类型
        'header_type' => 'require|checkHeaderType',// 抬头类型
        'name' => 'require',// 抬头名称
        'email' => 'require',// 邮箱

        'duty_number' => 'requireIf:header_type,' . OrderInvoiceEnum::HEADER_TYPE_COMPANY,// 税号 (企业类型必填)
        'address' => 'requireIf:type,' . OrderInvoiceEnum::TYPE_SPEC,// 企业地址(专票类型必填)
        'mobile' => 'requireIf:type,' . OrderInvoiceEnum::TYPE_SPEC,// 企业电话(专票类型必填)
        'bank' => 'requireIf:type,' . OrderInvoiceEnum::TYPE_SPEC,// 快狐银行(专票类型必填)
        'bank_account' => 'requireIf:type,' . OrderInvoiceEnum::TYPE_SPEC,// 银行账号(专票类型必填)
    ];


    protected $message = [
        'id.require' => '参数缺失',
        'shop_id.require' => '参数缺失',
        'order_id.require' => '订单参数缺失',
        'type.require' => '请选择发票类型',
        'header_type.require' => '请选择抬头类型',
        'name.require' => '请填写抬头名称',
        'email.require' => '请填写邮箱',

        'duty_number.requireIf' => '请填写税号',
        'address.requireIf' => '请填写企业地址',
        'mobile.requireIf' => '请填写企业电话',
        'bank.requireIf' => '请填写开户银行',
        'bank_account.requireIf' => '请填写银行账号',
    ];

    public function sceneAdd()
    {
        return $this->remove('id', true)
            ->remove('shop_id', true);
    }

    public function sceneEdit()
    {
        return $this->remove('order_id', true)
            ->remove('shop_id', true)
            ->append('id', 'checkAbleEdit');
    }

    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    public function sceneSetting()
    {
        return $this->only(['shop_id']);
    }


    /**
     * @notes 校验发票
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2022/4/12 12:09
     */
    protected function checkInvoice($value, $rule, $data)
    {
        $invoice = OrderInvoice::findOrEmpty($value);
        if ($invoice->isEmpty()) {
            return '发票信息不存在';
        }
        return true;
    }


    /**
     * @notes 校验订单是否可添加发票
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2022/4/12 10:01
     */
    protected function checkOrder($value, $rule, $data)
    {
        $order = Order::with(['invoice'])->findOrEmpty($value);

        if ($order->isEmpty()) {
            return '订单不存在';
        }

        if ($order['del'] == 1 || $order['order_status'] == OrderEnum::ORDER_STATUS_DOWN) {
            return '此订单已不可申请发票';
        }

        if (!empty($order['invoice'])) {
            return '此订单已有发票信息';
        }

        return true;
    }


    /**
     * @notes 校验抬头类型
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2022/4/11 17:17
     */
    protected function checkHeaderType($value, $rule, $data)
    {
        if ($value == OrderInvoiceEnum::HEADER_TYPE_PERSONAL && $data['type'] == OrderInvoiceEnum::TYPE_SPEC) {
            return '专用发票抬头类型仅支持企业';
        }
        return true;
    }


    /**
     * @notes 校验能否编辑
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2022/4/12 14:56
     */
    protected function checkAbleEdit($value, $rule, $data)
    {
        $invoice = OrderInvoice::findOrEmpty($value);
        if ($invoice['status'] == OrderInvoiceEnum::STATUS_YES) {
            return '此发票已开票，无法编辑';
        }
        return true;
    }


}
