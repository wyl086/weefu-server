<?php


namespace app\admin\validate\content;


use app\common\basics\Validate;

class HelpCategoryValidate extends Validate
{
    protected $rule = [
        'id'      => 'require|number',
        'name'    => 'require',
        'is_show' => 'require|in:0,1'
    ];

    protected $message = [
        'id.require'      => 'id不可为空',
        'id.number'       => 'id必须为数字',
        'name.require'    => '请填写分类名称',
        'is_show.require' => '请选择是否显示',
        'is_show.in'      => '选择是否显示异常'
    ];

    protected $scene = [
        'id'   => ['id'],
        'add'  => ['name', 'is_show'],
        'edit' => ['id', 'name', 'is_show']
    ];
}