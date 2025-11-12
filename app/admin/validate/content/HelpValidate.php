<?php


namespace app\admin\validate\content;


use app\common\basics\Validate;

class HelpValidate extends Validate
{
    protected $rule = [
        'id'    => 'require|number',
        'cid'   => 'require|number',
        'title' => 'require',
    ];

    protected $message = [
        'id.require'    => 'id不可为空',
        'id.number'     => 'id必须为数字',
        'cid.require'   => '请选择分类',
        'cid.number'    => '分类选择异常',
        'title.require' => '请填写帮助标题'
    ];

    protected $scene = [
        'id'   => ['id'],
        'add'  => ['cid', 'title'],
        'edit' => ['id', 'cid', 'title'],
    ];
}