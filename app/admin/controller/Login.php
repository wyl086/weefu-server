<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\admin\controller;


use app\admin\logic\LoginLogic;
use app\admin\validate\LoginValidate;
use app\common\basics\AdminBase;
use app\common\server\JsonServer;

class Login extends AdminBase
{
    public $like_not_need_login = ['login'];

    /**
     * Notes: 登录
     * @author FZR(2021/1/28 15:08)
     */
    public function login()
    {
        if ($this->request->isAjax()) {
            $post = request()->post();
            (new LoginValidate())->goCheck();
            if (LoginLogic::login($post)){
                return JsonServer::success('登录成功');
            }
            $error = LoginLogic::getError() ?: '登录失败';
            return JsonServer::error($error);
        }

        return view('', [
            'account'  => cookie('account'),
            'config'  => LoginLogic::config(),
        ]);
    }

    /**
     * Notes: 退出登录
     * @author FZR(2021/1/28 18:44)
     */
    public function logout()
    {
        LoginLogic::logout();
        $this->redirect(url('login/login'));
    }
}