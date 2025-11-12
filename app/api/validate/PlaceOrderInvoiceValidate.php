<?php

namespace app\api\validate;

use app\common\basics\Validate;
use app\common\enum\OrderInvoiceEnum;

/**
 * 下单时订单发票验证
 * Class OrderInvoiceValidate
 * @package app\api\validate
 */
class PlaceOrderInvoiceValidate extends Validate
{

    protected $rule = [
        'type' => 'require', // 发票类型
        'header_type' => 'require|checkHeaderType',// 抬头类型
        'name' => 'require',// 抬头名称
        'email' => 'require',// 邮箱

        'duty_number' => 'requireIf:header_type,'. OrderInvoiceEnum::HEADER_TYPE_COMPANY,// 税号 (企业类型必填)
        'address' => 'requireIf:type,'. OrderInvoiceEnum::TYPE_SPEC,// 企业地址(专票类型必填)
        'mobile' => 'requireIf:type,'. OrderInvoiceEnum::TYPE_SPEC,// 企业电话(专票类型必填)
        'bank' => 'requireIf:type,'. OrderInvoiceEnum::TYPE_SPEC,// 快狐银行(专票类型必填)
        'bank_account' => 'requireIf:type,'. OrderInvoiceEnum::TYPE_SPEC,// 银行账号(专票类型必填)
    ];


    protected $message = [
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


    /**
     * @notes 校验
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



}
