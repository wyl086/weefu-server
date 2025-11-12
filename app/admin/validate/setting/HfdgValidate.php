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

class HfdgValidate extends Validate
{
    protected $rule = [
        'sys_id'                => [ 'require' ],
        'product_id'            => [ 'require' ],
        'huifu_id'              => [ 'require' ],
        'rsa_merch_private_key' => [ 'require' ],
        'rsa_merch_public_key'  => [ 'require' ],
        'rsa_huifu_public_key'  => [ 'require' ],
    ];
    
    protected $field = [
        'sys_id'                => '系统号',
        'product_id'            => '产品号',
        'huifu_id'              => '商户号',
        'rsa_merch_private_key' => '商户私钥',
        'rsa_merch_public_key'  => '商户公钥',
        'rsa_huifu_public_key'  => '汇付公钥',
    ];
    
    public function sceneDevSet()
    {
        return $this->only([
            'sys_id',
            'product_id',
            'huifu_id',
            'rsa_merch_private_key',
            'rsa_merch_public_key',
            'rsa_huifu_public_key',
        ]);
    }
}