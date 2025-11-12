<?php


namespace app\admin\validate;


use app\common\basics\Validate;

class StoreValidate extends Validate
{
    protected $rule = [
        'id'             => 'require|number',
        'nickname'       => 'require',
        'mobile'         => 'require|mobile',
        'is_run'         => 'require|in:0,1',
        'service_mobile' => 'require',
    ];

    protected $message = [
        'id.require'     => 'id不可为空',
        'id.number'      => 'id必须为数字',
        'mobile.require' => '请填写联系人电话',
        'mobile.mobile'  => '联系人电话格式不正确',
        'is_run.require' => '请选择营业状态',
        'is_run.in'      => '营业状态选择异常',
        'service_mobile' => '请填写联系客户电话',
    ];

    protected $scene = [
        'id'   => ['id'],
        'add'  => ['nickname', 'mobile', 'is_run', 'service_mobile'],
        'edit' => ['id', 'nickname', 'mobile', 'is_run', 'service_mobile'],
    ];
}