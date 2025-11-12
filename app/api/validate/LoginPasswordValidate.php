<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\api\validate;


use app\common\basics\Validate;
use app\common\logic\SmsLogic;

/**
 * Class LoginPasswordValidate
 * @package app\api\validate
 */
class LoginPasswordValidate extends Validate
{
    protected $regex = ['password' => '^(?=.*[a-zA-Z0-9].*)(?=.*[a-zA-Z\\W].*)(?=.*[0-9\\W].*).{6,20}$'];
    protected $rule = [
        'mobile' => 'require|mobile',
        'password' => 'require|confirm:password|regex:password',
        // 'repassword' => 'require|confirm:password',
        'code' => 'require|checkCode',
    ];

    protected $message = [
        'mobile.require' => '请输入手机号',
        'password.require' => '请输入密码',
        'password.regex' => '密码格式错误',
        // 'repassword.require' => '请再次输入密码',
        // 'repassword.confirm' => '两次密码输入不一致',
        'code.require' => '请输入验证码',
        'mobile.mobile' => '非有效手机号码'
    ];

    /**
     * @notes 验证码
     * @param $value
     * @param $rule
     * @param $data
     * @return array|bool|string
     * @author suny
     * @date 2021/7/13 6:29 下午
     */
    public static function checkCode($value, $rule, $data)
    {

        $res = SmsLogic::check($data['message_key'], $data['mobile'], $value);
        if (false === $res) {
            return SmsLogic::getError();
        }
        return true;
    }

}