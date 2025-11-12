<?php
namespace app\admin\validate;

use app\common\basics\Validate;
use think\facade\Db;

class FreightValidate extends Validate
{

    protected $rule = [
        'id' => 'require',
        'charge_way' => 'require',
        'name' => 'require|unique:freight',
        'region' => 'require|checkTypeData',
    ];

    protected $message = [
        'id.require' => '参数缺失',
        'charge_way.require' => '请选择计费方式',
        'name.require' => '请输入模板名称',
        'name.unique' => '该模板名称已存在',
    ];

    protected function sceneAdd()
    {
        $this->only(['name', 'charge_way', 'region']);
    }

    protected function sceneEdit()
    {
        $this->only(['id', 'name', 'charge_way', 'region']);
    }

    public function sceneDel()
    {
        $this->only(['id'])->append('id', 'checkIsAbleDel');
    }

    //添加时验证全国模板或指定地区模板的数据
    protected function checkTypeData($value, $reule, $data)
    {
        foreach ($data as &$item) {
            if (is_array($item)) {
                $item = array_values($item);
            }
        }

        $configs = form_to_linear($data);

        foreach ($configs as $config) {
            if (
                !isset($config['first_unit']) ||
                !isset($config['first_money']) ||
                !isset($config['continue_unit']) ||
                !isset($config['continue_money'])
            ) {
                return '请填写完整设置参数';
            }

            if (
                ($config['first_unit'] < 0) ||
                ($config['first_money'] < 0) ||
                ($config['continue_unit'] < 0) ||
                ($config['continue_money'] < 0)
            ){
                return '所填设置参数不能小于0';
            }
        }
        return true;
    }


    //验证模板是否可以删除
    protected function checkIsAbleDel($value, $reule, $data)
    {
        $freight = Db::name('goods')
            ->where('express_template_id', $value)
            ->find();

        if ($freight) {
            return '此模板已有商品使用!';
        }
        return true;
    }
}