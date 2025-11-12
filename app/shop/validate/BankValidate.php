<?php


namespace app\shop\validate;


use app\common\basics\Validate;

class BankValidate extends Validate
{
    protected $rule = [
        'id'       => 'require|number',
        'name'     => 'require',
        'branch'   => 'require',
        'nickname' => 'require',
        'account'  => 'require',
    ];

    protected $message = [
        'id.require'       => '缺少ID字段',
        'id.number'        => 'ID必须为数字',
        'name.require'     => '请填写提现银行',
        'branch.require'   => '请填写银行支行',
        'nickname.require' => '请填写开户名称',
        'account.require'  => '请填写银行账号',
    ];

    protected $scene = [
        'id'   => ['id'],
        'add'  => ['name', 'branch', 'nickname', 'account'],
        'edit' => ['id', 'name', 'branch', 'nickname', 'account']
    ];
}