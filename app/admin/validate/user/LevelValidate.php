<?php
namespace app\admin\validate\user;

use think\Validate;

class LevelValidate extends Validate
{
    protected $rule = [
        'id' => 'require',
        'name' => 'require',
        'growth_value' => 'require|integer|egt:0',
        'image' => 'require',
        'background_image' => 'require',
        'discount' => 'between:0,10'
    ];

    protected $message = [
        'id.require' => '参数缺失',
        'name.require' => '请输入等级名称',
        'growth_value.require' => '请输入成长值',
        'growth_value.integer' => '成长值必须为整数',
        'growth_value.egt' => '成长值必须大于或等于0',
        'image.require' => '请选择等级图标',
        'background_image.require' => '请选择等级背景图',
        'discount.between' => '会员折扣必须在0-10之前',
    ];

    public function sceneAdd() {
        return $this->only(['name', 'growth_value', 'image', 'background_image', 'discount']);
    }

    public function sceneEdit() {
        return $this->only(['id', 'name', 'growth_value', 'image', 'background_image', 'discount']);
    }
}