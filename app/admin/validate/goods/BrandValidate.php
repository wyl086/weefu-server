<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\admin\validate\goods;


use app\common\basics\Validate;
use app\common\model\goods\Goods;


/**
 * 商品品牌
 * Class GoodsBrandValidate
 * @package app\admin\validate
 */
class BrandValidate extends Validate
{

    protected $rule = [
        'id'        => 'require',
        'name'      => 'require|unique:goodsBrand,name&del',
        'initial'   => 'require',
        'image'     => 'require'
    ];

    protected $message = [
        'id.require'        => '参数缺失',
        'name.require'      => '参数缺失',
        'name.unique'       => '该名称已被使用',
        'initial.unique'    => '请选择品牌首字母',
        'image.require'     => '请选择品牌图片',
    ];

    protected $scene = [
        'add'  =>  ['name', 'initial', 'image'],
        'edit' =>  ['id','name', 'initial', 'image'],
    ];

    public function sceneDel()
    {
        return $this->only(['id'])
        ->append('id','checkDel');
    }


    protected function checkDel($value,$rule,$data)
    {
        $check = Goods::where('brand_id', $value)->find();
        if ($check) {
            return '品牌已经关联商品，无法删除品牌';
        }
        return true;
    }
}