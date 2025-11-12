<?php
namespace app\api\validate;

use app\common\basics\Validate;
use app\common\enum\NoticeEnum;
use app\common\model\user\User;
use app\common\logic\SmsLogic;


class LoginValidate extends Validate
{
    protected $rule = [
        'client' => 'require|in:1,2,3,4,5,6',
        'mobile' => 'require|mobile',
        'password' => 'require|checkPassword',
        'code' => 'require|checkCode'
    ];

    protected  $message = [
        'mobile.require' => '请输入手机号',
        'mobile.mobile' => '请输入正确手机号',
        'password.require' => '请输入密码',
        'client.require'    => '请输入客户端',
        'client.in'         => '无效的客户端',
        'code.require'  => '请输入验证码'
    ];
    protected $scene = [
        'smsLogin' => ['mobile', 'code','client'], // 短信验证码登录
        'mpLogin' => ['mobile', 'password', 'client'], //手机号密码登录
    ];


    public function checkPassword($value, $rule, $data) {
        if($this->safe() === false) {
            return '密码输入错误次数过多';
        }

        $user = User::where([
            'mobile' => $data['mobile'],
            'del' => 0
        ])->findOrEmpty();

        if($user->isEmpty()) { // 账号错误
            $this->safe(true); // 记录错误次数
            return '账号不存在';
        }

        if($user['disable']) {
            return '账号已禁用';
        }

        $password = create_password($value, $user['salt']);
        if($password != $user['password']) {
            $this->safe(true);
            return '密码错误';
        }
        return true;
    }

    /**
     * 连续30分钟内15次输错密码，无法登录
     */
    public function safe($flag = false)
    {
        $cache_name = 'login_error_count_'. request()->ip();
        if($flag) {
            $login_error_count = cache($cache_name);
            $login_error_count++;
            cache($cache_name, $login_error_count, 1800); // 1800秒 = 30分钟
        }
        $login_error_count = cache($cache_name);
        if(!empty($login_error_count) && $login_error_count >= 15) {
            return false;
        }
        return true;
    }

    /***
     * 验证验证码
     * @param $value
     * @param $rule
     * @param $data
     * @return bool
     */
    public static function checkCode($value, $rule, $data)
    {
        $message_key = NoticeEnum::GET_GODE_LOGIN_NOTICE;
        $res = SmsLogic::check($message_key, $data['mobile'], $value);
        if (false === $res) {
            return SmsLogic::getError();
        }
        return true;
    }
}