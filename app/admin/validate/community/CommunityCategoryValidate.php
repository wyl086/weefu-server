<?php


namespace app\admin\validate\community;


use app\common\basics\Validate;
use app\common\model\community\CommunityCategory;

/**
 * 种草社区分类验证
 * Class CommunityCategoryValidate
 * @package app\admin\validate\community
 */
class CommunityCategoryValidate extends Validate
{
    protected $rule = [
        'id' => 'require|number',
        'name' => 'require|max:4|unique:'.CommunityCategory::class.',name^del',
        'is_show' => 'require|in:0,1',
        'sort' => 'egt:0'
    ];

    protected $message = [
        'id.require' => 'id不可为空',
        'id.number' => 'id必须为数字',
        'name.require' => '请填写分类名称',
        'name.max' => '分类名称长度不能超过4位',
        'name.unique' => '分类名称已存在',
        'is_show.require' => '请选择是否显示',
        'is_show.in' => '选择是否显示异常',
        'sort.egt' => '请填写大于等于0的排序值',
    ];

    protected $scene = [
        'id' => ['id'],
        'status' => ['id', 'is_show'],
        'add' => ['name', 'is_show', 'sort'],
        'edit' => ['id', 'name', 'is_show', 'sort']
    ];
}