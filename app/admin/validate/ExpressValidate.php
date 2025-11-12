<?php

namespace app\admin\validate;

use app\common\basics\Validate;

class ExpressValidate extends Validate
{
    protected $rule = [
        'name' => 'require|unique:Express,name^del',
        'poster' => 'require',
    ];

    protected $message = [
        'name.unique' => '该名称已存在',
        'poster.require' => '图标不能为空',
    ];

}
