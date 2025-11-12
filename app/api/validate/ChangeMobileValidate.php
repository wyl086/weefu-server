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
use app\common\model\Client_;
use app\common\model\user\User;

class ChangeMobileValidate extends Validate
{
    protected $rule = [
        'mobile' => 'require|mobile',
        'new_mobile' => 'require|mobile|checkMobile',
    ];

    protected $message = [
        'mobile.require' => '参数缺失',
        'mobile.mobile' => '请填写正确的手机号',
        'new_mobile.mobile' => '请填写正确的手机号',
        'new_mobile.require' => '请填写手机号'
    ];


    public function sceneBinding()
    {
        $this->only(['new_mobile']);
    }


    protected function checkMobile($value, $rule, $data)
    {
        //检查新手机号是否已存在
        $user = User::where([
            ['mobile', '=', $value],
            ['id', '<>', $data['user_id']]
        ])->find();

        if ($user) {
            return '此手机号已被使用';
        }

        if (!isset($data['code'])) {
            return '请填写验证码';
        }

        $mobile = $data['new_mobile'];
        if (isset($data['action']) && 'change' == $data['action'] && $data['client'] != Client_::pc) {
            $mobile = $data['mobile'];
        }

        $res = SmsLogic::check($data['message_key'], $mobile, $data['code']);
        if (false === $res) {
            return SmsLogic::getError();
        }
        return true;
    }
}