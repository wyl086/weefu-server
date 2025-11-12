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

class changeUserInfo extends Validate{
    protected $rule = [
        'nickname'  => 'require',
        'sex'       => 'require|in:0,1,2',
    ];
    protected $message = [
        'nickname.require'  => '请输入昵称',
        'sex.require'       => '请选择性别',
        'sex.in'            => '性别设置错误',
    ];
    public function scenePc(){
        $this->only(['nickname','sex']);
    }
}