<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\admin\validate;


use app\common\basics\Validate;
use app\common\model\Admin;
use think\facade\Cache;

/**
 * 登录数据验证器
 * Class LoginValidate
 * @Author FZR
 * @package app\admin\validate
 */
class LoginValidate extends Validate
{
    protected $rule = [
        'account'   => 'require',
        'password'  => 'require|password',
    ];

    protected $message = [
        'account.require'   => '请填写登录账号',
        'password.require'  => '请填写登录密码',
        'password.password' => '账号密码错误',
    ];


    /**
     * 账号密码验证码
     * @param $password
     * @param $other
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function password($password, $other, $data)
    {
        if ($this->safe() === false) {
            $this->message['password.password'] .= ':多次输入错误';
            return false;
        }

        $admin_info = (new Admin())
            ->where(['account' => $data['account'], 'del' => 0])
            ->find();

        if (empty($admin_info)) {
            $this->safe(true);
            return false;
        }

        if ($admin_info['disable']) {
            return '账号被禁用';
        }

        $password = generatePassword($password, $admin_info['salt']);
        if ($password != $admin_info['password']) {
            $this->safe(true);
            return false;
        }

        return true;
    }

    /**
     * 连续30分钟内15次输错密码，无法登录
     * @param bool $add
     * @return bool
     */
    protected function safe($add = false)
    {
        $cache_name = 'admin_login_error_count' . request()->ip();
        if ($add) {
            $admin_login_error_count = Cache::get($cache_name);
            $admin_login_error_count++;
            Cache::tag('admin_login_error_count')->set($cache_name, $admin_login_error_count, 1800);
        }
        $count = Cache::get($cache_name);
        if (!empty($count) && $count >= 15) {
            return false;
        }
        return true;
    }

}