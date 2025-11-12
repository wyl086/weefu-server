<?php


namespace app\shop\validate;


use app\common\basics\Validate;

class AlipayValidate extends Validate
{
    protected $rule = [
        'id'       => 'require|number',
        'account'     => 'require',
        'username'   => 'require',
    ];

    protected $message = [
        'id.require'        => '缺少ID字段',
        'id.number'         => 'ID必须为数字',
        'account.require'   => '请填写支付宝账号',
        'username.require'  => '请填写支付宝姓名',
    ];

    protected $scene = [
        'id'   => [ 'id' ],
        'add'  => [ 'account', 'username' ],
        'edit' => [ 'id', 'account', 'username' ],
    ];
}