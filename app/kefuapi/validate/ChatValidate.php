<?php

namespace app\kefuapi\validate;

use app\common\basics\Validate;
use app\common\model\user\User;


class ChatValidate extends Validate
{
    protected $rule = [
        'user_id' => 'require|checkUser',
    ];

    protected $message = [
        'user_id.require' => '参数缺失',
    ];

    protected function checkUser($value, $rule, $data=[])
    {
        $user = User::where(['id' => $value])->findOrEmpty();

        if ($user->isEmpty() || $user['del']) {
            return '用户不存在';
        }
        return true;
    }

}