<?php


namespace app\api\validate;


use think\Validate;

class OaLoginValidate extends Validate
{
    protected $rule = [
        'code' => 'require',
    ];
}