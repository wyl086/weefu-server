<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | gitee下载：https://gitee.com/multshop_gitee
// | github下载：https://github.com/multshop-github
// | 访问官网：https://www.multshop.cn
// | 访问社区：https://home.multshop.cn
// | 访问手册：http://doc.multshop.cn
// | 微信公众号：multshop技术社区
// | multshop系列产品在gitee、github等公开渠道开源版本可免费商用，未经许可不能去除前后端官方版权标识
// |  multshop系列产品收费版本务必购买商业授权，购买去版权授权后，方可去除前后端官方版权标识
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\shop\controller;


use app\shop\logic\LoginLogic;
use app\shop\validate\LoginValidate;
use app\common\basics\ShopBase;
use app\common\server\JsonServer;

class Login extends ShopBase
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