<?php

namespace app\admin\validate\kefu;

use app\common\basics\Validate;
use app\common\logic\ChatLogic;


class LoginValidate extends Validate
{
    protected $rule = [
        'id' => 'require|number|checkConfig',
    ];

    protected $message = [
        'id.require' => 'id不可为空',
        'id.number' => 'id必须为数字',
    ];


    protected function checkConfig($value, $rule, $data = [])
    {
        if (false === ChatLogic::checkConfig()) {
            return ChatLogic::getError() ?: '请联系管理员设置后台配置';
        }
        return true;
    }


}