<?php
namespace app\api\validate;

use think\Validate;

class SetWechatUserValidate extends Validate
{
    protected $rule = [
        'nickname'  => 'require',
        'avatar'    => 'require',
        'sex'       => 'require',
    ];

    protected $message = [
        'nickname.require'  => '参数缺失',
        'avatar.require'    => '参数缺失',
        'sex.require'       => '参数缺失',
    ];
}