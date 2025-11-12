<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\admin\validate\shop;


use app\common\basics\Validate;

class StoreLValidate extends Validate
{
    protected $rule = [
        'id'                => 'require|number',
        'cid'               => 'require|number',
        'type'              => 'require|in:1,2',
        'name'              => 'require',
        'nickname'          => 'require',
        'mobile'            => 'require|mobile',
        'account'           => 'require|unique:ShopAdmin',
        'password'          => 'require|min:6',
        'okPassword'        => 'require|min:6|confirm:password',
        'logo'              => 'require',
        'trade_service_fee' => 'require|float',
        'weight'            => 'require|number',
        'is_run'            => 'require|in:0,1',
        'is_freeze'         => 'require|in:0,1',
        'is_product_audit'  => 'require|in:0,1',
        'is_recommend'      => 'require',
        'is_distribution'      => 'require',
        'is_pay'            => 'require',
        'expire_time'       => 'require',
        'delivery_type'     => 'require'
    ];

    protected $message = [
        'id.require'        => 'id不可为空',
        'id.number'         => 'id必须为数字',
        'type.require'      => '请选择商家类型',
        'type.in'           => '选择的商家类型不符合',
        'name.require'      => '请填写商家名称',
        'nickname.require'  => '请填写联系人',
        'mobile.require'            => '请填写联系手机号',
        'logo.require'              => '请选择商家logo',
        'expire_time.require'       => '请选择到期时间',
        'trade_service_fee.require' => '请填写交易服务费率',
        'is_product_audit.require'  => '请选择产品是否需要审核',
        'is_run.require'            => '请选择店铺营业状态',
        'is_freeze.require'         => '请选择商家状态',
        'account.require'           => '请填写商家账号',
        'account.unique'            => '商家账号不可重复',
        'password.require'          => '请填写登录密码',
        'password.min'              => '登录密码最少6位数',
        'okPassword.require'        => '请填写确认登录密码',
        'okPassword.confirm'        => '两次密码不一致',
        'weight.require'            => '权重不可为空',
        'weight.number'             => '权重必须为数字',
        'is_recommend.require'      => '请选择是否推荐商家',
        'is_distribution.require'      => '请选择是否允许分销',
        'is_pay.require'            => '请选择是否开启支付功能',
        'delivery_type.require'     => '最少选择一种配送方式',
    ];



    protected $scene = [
        'id'      => ['id'],
        'add'     => ['cid', 'type', 'delivery_type', 'name', 'nickname', 'mobile', 'account', 'password', 'okPassword', 'logo', 'trade_service_fee', 'is_run', 'is_freeze', 'is_product_audit', 'expire_time'],
        'edit'    => ['id', 'cid', 'type', 'name', 'delivery_type', 'nickname', 'mobile', 'logo', 'trade_service_fee', 'is_run', 'is_freeze', 'is_product_audit', 'expire_time'],
        'set'     => ['id', 'is_recommend', 'weight', 'is_distribution', 'is_pay'],
        'account' => ['id'],
        'pwd'     => ['password', 'okPassword']
    ];

}
