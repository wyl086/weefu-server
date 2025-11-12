<?php


namespace app\shop\validate\order;


use app\common\basics\Validate;
use app\common\model\order\OrderInvoice;

/**
 * 订单发票验证
 * Class OrderInvoiceValidate
 * @package app\shop\validate\order
 */
class OrderInvoiceValidate extends Validate
{
    protected $rule = [
        'id' => 'require|checkInvoice',
        'status' => 'require',
        'invoice_number' => 'require',
    ];

    protected $message = [
        'id.require' => '缺少ID字段',
        'status.require' => '请选择开票状态',
        'invoice_number.require' => '请填写发票编号',
    ];

    protected function checkInvoice($value, $rule, $data)
    {
        $invoice = OrderInvoice::findOrEmpty($value);
        if ($invoice->isEmpty()) {
            return '该发票记录不存在';
        }
        return true;
    }
}