<?php


namespace app\api\validate;


use app\common\basics\Validate;
use app\common\enum\NoticeEnum;
use app\common\logic\SmsLogic;

class ShopApplyValidate extends Validate
{
    protected $rule = [
        'cid'           => 'require|number',
        'name'          => 'require|chsAlphaNum',
        'nickname'      => 'require|chsAlphaNum',
        'mobile'        => 'require|mobile',
        'account'       => 'require|unique:shop_apply,name&del|alphaNum|min:2|max:20',
        'password'      => 'require|alphaDash|min:6|max:32',
        'license'       => 'require|array',
        'code'          => 'require|checkCode'
    ];

    protected $message = [
        'cid.require'      => '请选择主营类目',
        'cid.number'       => 'cid参数必须为数字',
        'name.require'     => '请填写商家名称',
        'nickname.require' => '请填写联系人姓名',
        'mobile.require'   => '请填写手机号码',
        'mobile.mobile'    => '请填写正确的手机号',
        'account.require'  => '请现在创建的账号',
        'account.unique'   => '该账号已存在',
        'account.alphaNum'   => '账号必须由数字或字母组成',
        'account.min'   => '账号长度不能少于2个字符',
        'account.max'   => '账号长度不能超过20个字符',
        'password.require' => '请填写登录密码',
        'license.require'  => '请上传营业执照',
        'license.array'    => 'license参数需数组格式',
        'code.require'     => '请输入验证码'
    ];

    protected $scene = [
        'apply' => ['cid', 'name', 'nickname', 'mobile', 'account', 'password', 'license', 'code']
    ];


    //验证手机验证码
    protected  function checkCode($value, $rule, $data)
    {
        $message_key = NoticeEnum::SHOP_APPLY_CHECK_CODE;
        $res = SmsLogic::check($message_key, $data['mobile'], $value);
        if (false === $res) {
            return SmsLogic::getError();
        }
        return true;
    }
}
