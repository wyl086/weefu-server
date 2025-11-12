<?php
namespace app\admin\validate\wechat;

use think\Validate;

class ReplyValidate extends Validate
{
    protected $rule = [
        'id'            => 'require',
        'name'          => 'require|unique:wechatReply,name^del',
        'keyword'       => 'require|max:5',
        'content'       => 'require',
    ];
    protected $message = [
        'id.require'            => '请选择回复',
        'name.require'          => '请输入规则名称',
        'name.unique'           => '规则名称重复',
        'keyword.require'       => '请输入关键词',
        'keyword.max'           => '关键词不能超过5个字',
        'content.require'       => '请输入回复内容',
    ];
    protected $scene = [
        'subscribe' =>  ['name','content'],
        'text'      =>  ['name','keyword','content'],
        'default'   =>  ['name','content'],
        'del'       =>  ['id'],
    ];
}