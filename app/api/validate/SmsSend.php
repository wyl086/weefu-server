<?php

namespace app\api\validate;

use app\api\logic\LoginLogic;
use app\common\basics\Validate;
use app\common\enum\NoticeEnum;
use app\common\model\Client_;
use app\common\model\shop\Shop;
use app\common\model\SmsLog;
use app\common\model\user\User;
use app\common\server\sms\Driver;
use think\facade\Db;

class SmsSend extends Validate
{
    protected $rule = [
        'mobile'    => 'require|mobile|checkSms',
        'key'       => 'checkMobile',
    ];
    protected $message = [
        'mobile.require' => '请输入手机号码',
        'mobile.mobile' => '请输入正确的手机号码',
    ];


    //限制验证码发送频率
    protected function checkSms($value, $rule, $data)
    {
        $message_key = NoticeEnum::SMS_SCENE[$data['key']];
        $send_time = SmsLog::where(['message_key' => $message_key, 'mobile' => $value, 'is_verify' => 0])
            ->order('id desc')
            ->value('send_time');
        //一分钟内不能频繁发送
        if ($send_time && $send_time + 60 > time()) {
            return '验证码发送频繁，请稍后在发送';
        }
        return true;
    }

    //验证手机号
    protected function checkMobile($value, $rule, $data)
    {
        if($data['client'] == Client_::pc && $data['key'] == 'BGSJHM'){
            $data['new_mobile'] =  $data['mobile'];
            $mobile = Db::name('user')->where(['id' => $data['user_id']])->value('mobile');
            $data['mobile'] =  $mobile;
        }
        $user = User::where(['mobile' => $data['mobile'], 'del' => 0])->findOrEmpty();
        switch ($value) {
            case 'ZCYZ':    //注册验证
            case 'BDSJHM':  //绑定手机号码
                if (!$user->isEmpty()) {
                    return '该手机号码已存在';
                }
                break;
            case 'YZMDL':   //验证码登录
                if ($user->isEmpty()) { //账号不存在, 给他注册
                    $post = request()->post();
                    $post['password'] = '';
                    LoginLogic::register($post);
                }
                break;
            case 'ZHMM':    //找回密码
            case 'BGSJHM':  //变更手机号码
            case 'ZHZFMM':  // 找回支付密码
                if ($user->isEmpty()) {
                    return '手机号码不存在';
                }
                break;
            case 'SJSQYZ':  //商家入驻
                $shop = Shop::where(['mobile' => $data['mobile'], 'del' => 0])->findOrEmpty();
                if(!$shop->isEmpty()) {
                    return '该手机号码已存在!';
                }
                break;
            default:
                return '场景错误';
        }
        return true;
    }
}