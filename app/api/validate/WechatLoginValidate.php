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

class WechatLoginValidate extends Validate
{
    protected $rule = [
        'code'          => 'require',
    ];
    
    protected $message = [
        'code.require'          => 'code缺少',
    ];
    
    public function sceneWechatAuth()
    {
        return $this->only(['code']);
    }
}