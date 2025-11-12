<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\admin\validate\setting;

use app\common\basics\Validate;
use think\helper\Str;

class StorageValidate extends Validate
{
    protected $rule = [
        'domain' => 'require|checkDomain',
    ];
    
    protected $message = [
        'domain.require' => '空间域名必须填写',
    ];
    
    function sceneEdit()
    {
        $this->only([ 'domain' ]);
    }
    
    function checkDomain($domain, $rule, $data)
    {
        if (Str::contains($domain, 'http://') || Str::contains($domain, 'https://')) {
            return true;
        }
        
        return '空间域名请补全http://或https://';
    }
}