<?php
namespace app\api\validate;

use think\Validate;
use app\common\model\user\User;
use app\common\logic\SmsLogic;

class RegisterValidate extends Validate
{
    protected $regex = ['password' => '^(?=.*[a-zA-Z0-9].*)(?=.*[a-zA-Z\\W].*)(?=.*[0-9\\W].*).{6,20}$'];

    protected $rule = [
        'mobile' => 'require|mobile|checkMobile',
        'password' => 'require|regex:password',
        'code' => 'requireIf:check_code,1|checkCode',
        'client' => 'require|in:1,2,3,4,5,6'
    ];

    protected $message = [
        'mobile.require'    => '请输入手机号',
        'mobile.mobile'     => '无效的手机号码',
        'password.require'  => '请输入密码',
        'password.regex'    => '密码格式错误',
        'code.requireIf'    => '请输入验证码',
        'client.require'    => '请输入客户端',
        'client.in'         => '无效的客户端',

    ];

    public function checkCode($value, $rule, $data)
    {
        $res = SmsLogic::check($data['message_key'], $data['mobile'], $value);
        if (false === $res) {
            return SmsLogic::getError();
        }
        return true;
    }

    public function checkMobile($value, $data, $rule)
    {
        $where = [
            'del' => 0,
            'mobile' => $value
        ];
        //检查手机号是否已存在
        $user = User::where($where)->findOrEmpty();

        if (!$user->isEmpty()) {
            return '此手机号已被使用';
        }
        return true;
    }
}