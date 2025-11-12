<?php
namespace app\api\validate;

use think\Validate;

class UpdateUserValidate extends Validate
{
    protected $rule = [
        'field' => 'require|checkField',
        'value' => 'require',
    ];

    protected $message = [
        'field.require' => '请填写要修改的字段',
        'value.require' => '请填写要修改的值',
    ];


    public function sceneSet()
    {
        $this->only(['field', 'value']);
    }

    protected function checkField($value, $rule, $data)
    {
        $allow_field = ['nickname', 'sex', 'avatar'];
        if (in_array($value, $allow_field)) {
            return true;
        }
        return '非法字段';
    }
}