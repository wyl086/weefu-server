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


/**
 * 商品栏目
 * Class GoodsColumnValidate
 * @package app\admin\validate
 */
class ColumnValidate extends Validate
{

    protected $rule = [
        'id'    => 'require',
        'name'  => 'require|unique:goodsColumn,name&del',
    ];

    protected $message = [
        'id.require'    => '参数缺失',
        'name.require'  => '参数缺失',
        'name.unique'   => '该名称已被使用',
    ];

    protected $scene = [
        'add'  =>  ['name'],
        'edit' =>  ['name'],
        'del'  =>  ['id'],
    ];
}