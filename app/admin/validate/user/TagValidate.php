<?php
namespace app\admin\validate\user;

use think\Validate;

class TagValidate extends Validate
{
    protected  $rule = [
        'name' => 'require|max:16',
        'remark' => 'max:6',
        'id'     => 'require'
    ];

    protected  $message = [
        'name.require' => '请输入标签名称',
        'name.max' => '标签长度不能超过16个字符',
        'remark.max' => '备注长度不能超过64个字符',
        'id.require' => '标签id不能为空',
    ];

    public function sceneAdd()
    {
        return $this->only(['name', 'remark']);
    }

    public function sceneEdit()
    {
        return $this->only(['name', 'remark', 'id']);
    }
}