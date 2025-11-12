<?php

namespace app\api\controller;

use app\api\logic\LoginPasswordLogic;
use app\api\validate\LoginPasswordValidate;
use app\common\basics\Api;
use app\common\enum\NoticeEnum;
use app\common\server\JsonServer;

class LoginPassword extends Api
{
    public $like_not_need_login = ['forget'];

    public function forget()
    {
        $post = $this->request->post();
        $post['message_key'] = NoticeEnum::GET_BACK_MOBILE_NOTICE;
        (new LoginPasswordValidate())->goCheck('', $post);
        $result =  LoginPasswordLogic::forget($post);
        if (false === $result) {
            return JsonServer::error(LoginPasswordLogic::getError());
        }
        return JsonServer::success('修改成功',$result);
    }
}