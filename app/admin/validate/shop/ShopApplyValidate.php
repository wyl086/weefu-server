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

class ShopApplyValidate extends Validate
{
    protected $rule = [
        'id'           => 'require|number',
        'audit_status' => 'require|number'
    ];

    protected $message = [
        'id.require' => 'ID不可为空',
        'id.number'  => 'ID必须为数字',
        'audit_status.require'  => '请选择审核状态',
        'audit_status.number'   => '审核状态选择异常',
    ];

    protected $scene = [
        'id'    => ['id'],
        'audit' => ['id', 'audit']
    ];
}