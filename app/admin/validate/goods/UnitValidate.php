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
 * 商品单位验证
 * Class GoodsUnitValidate
 * @package app\admin\validate
 */
class UnitValidate extends Validate
{

    protected $rule = [
        'id'    => 'require',
        'name'  => 'require|unique:goodsUnit,name&del'
    ];

    protected $message = [
        'id.require'    => '参数缺失',
        'name.unique'   => '该名称已被使用',
    ];


    protected $scene = [
        'add'   => ['name'],
        'edit'  => ['id','name'],
    ];

    public function sceneDel()
    {
        return $this->only(['id'])
            ->append('id','CheckUnit');
    }

    protected function CheckUnit($value, $rule, $data)
    {
        $check = Goods::where('unit_id', $value)->find();
        if ($check) {
            return '当前商品单位已使用，无法删除';
        }
        return true;
    }

}