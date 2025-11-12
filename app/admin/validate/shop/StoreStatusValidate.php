<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\admin\validate\shop;


use app\common\basics\Validate;
use app\common\model\shop\Shop;

/**
 * 商家状态验证(营业状态，冻结状态)
 * Class StoreStatusValidate
 * @package app\admin\validate\shop
 */
class StoreStatusValidate extends Validate
{
    protected $rule = [
        'ids'               => 'require',
        'field'             => 'require|checkField',
        'value'             => 'require|in:0,1',
    ];

    protected $message = [
        'ids.require'       => 'id不可为空',
        'ids.number'        => 'id必须为数字',
        'field.require'     => '参数缺失',
        'value.require'     => '参数缺失',
        'value.in'          => '参数错误',
    ];

    /**
     * @notes 验证字段
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2022/3/17 10:40
     */
    public function checkField($value, $rule, $data)
    {
        $checArr = ['is_run', 'is_freeze'];

        if (!in_array($value, $checArr)) {
            return '参数错误';
        }

        return true;
    }

}

