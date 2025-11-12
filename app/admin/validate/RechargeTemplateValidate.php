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
use think\Validate;

class RechargeTemplateValidate extends Validate{
    protected $rule = [
        'money'          => 'require|gt:0',
        'give_money'     => 'egt:0',
        'sort'         => 'integer|egt:0',
    ];
    protected $message = [
        'money.require'     => '请输入充值金额',
        'money.gt'          => '充值金额须为大于0',
        'give_money.egt'     => '赠送金额须为大于0',
        'sort.integer'    => '排序值须为整数',
        'sort.egt'        => '排序值须大于或等于0',
    ];
}
