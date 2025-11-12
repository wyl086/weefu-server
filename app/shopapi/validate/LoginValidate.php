<?php

namespace app\shopapi\validate;

use app\common\basics\Validate;
use app\common\model\shop\ShopAdmin;
use think\facade\Cache;

/**
 * 商家移动端登录验证
 * Class LoginValidate
 * @package app\shopapi\validate
 */
class LoginValidate extends Validate
{
    protected $rule = [
        'client'    => 'require|in:1,2,3,4,5,6',
        'account'   => 'require',
        'password'  => 'require|checkPassword',
    ];

    protected $message = [
        'password.require'          => '请输入密码',
        'password.checkPassword'    => '账号或密码错误',
        'client.require'            => '请输入客户端',
        'client.in'                 => '无效的客户端',
    ];


    /**
     * @notes 校验密码
     * @param $password
     * @param $other
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2021/11/9 16:02
     */
    protected function checkPassword($password, $other, $data)
    {
        if (false === $this->safe()) {
            $this->message['password.password'] .= ':多次输入错误';
            return false;
        }

        $admin_info = (new ShopAdmin())->alias('a')
            ->field('a.*')
            ->join('shop s', 's.id = a.shop_id')
            ->where(['a.account' => $data['account'], 'a.del' => 0])
            ->find();

        if (empty($admin_info)) {
            $this->safe(true);
            return '账号不存在';
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
        $cache_name = 'shop_api_login_error_count' . request()->ip();
        if ($add) {
            $admin_login_error_count = Cache::get($cache_name);
            $admin_login_error_count++;
            Cache::tag('shop_api_login_error_count')->set($cache_name, $admin_login_error_count, 1800);
        }
        $count = Cache::get($cache_name);

        if (!empty($count) && $count >= 15) {
            return false;
        }
        return true;
    }
}