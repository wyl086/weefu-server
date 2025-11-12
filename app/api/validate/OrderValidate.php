<?php

namespace app\api\validate;

use app\common\basics\Validate;

class OrderValidate extends Validate
{
    protected $rule = [
        'id' => 'require',
        'cart_id' => 'require',
        'goods' => 'require',
        'address_id' => 'require|checkParam',
    ];

    protected $message = [
        'id' => '参数错误',
        'cart_id' => '参数类型错误',
        'goods' => '请选择商品',
        'address_id' => '请选择收货地址',
    ];


    protected $scene = [
        'add' => ['address_id'],
        'detail' => ['id'],
    ];


    /**
     * @notes 参数验证
     * @param $value
     * @param $arr
     * @param $data
     * @return bool|string
     * @author suny
     * @date 2021/7/13 6:29 下午
     */
    public function checkParam($value, $arr, $data)
    {
        if (!isset($data['goods']) && !isset($data['cart_id'])) {
            return '参数有误';
        }
        return true;
    }

}
