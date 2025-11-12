<?php

namespace app\api\validate;

use app\common\basics\Validate;
use app\common\enum\IntegralGoodsEnum;
use app\common\model\integral\IntegralGoods;
use app\common\model\user\UserAddress;

/**
 * 积分订单下单验证
 * Class IntegralOrderValidate
 * @package app\api\validate
 */
class IntegralPlaceOrderValidate extends Validate
{
    protected $rule = [
        'num' => 'require|number|gt:0',
        'id' => 'require|number|checkGoods',
        'address_id' => 'require|checkAddress',
    ];

    protected $message = [
        'id.require' => '参数缺失',
        'id.number' => '参数类型错误',
        'num.require' => '请选择商品数量',
        'num.number' => '商品数量参数类型错误',
        'num.gt' => '请选择商品数量',
        'address_id.require' => '请选择地址',
    ];


    public function sceneSettlement()
    {
        return $this->only(['code','num']);
    }

    public function sceneSubmit()
    {
        return $this->only(['id', 'num', 'address_id'])
            ->append('id', 'checkGoods');
    }


    // 验证商品
    protected function checkGoods($value, $rule, $data)
    {
        $goods = IntegralGoods::where([
            'id' => $value,
            'del' => IntegralGoodsEnum::DEL_NORMAL,
            'status' => IntegralGoodsEnum::STATUS_SHELVES
        ])->findOrEmpty();

        if ($goods->isEmpty()) {
            return '积分商品不存在';
        }

        if ($goods['stock'] < intval($data['num'])) {
            return '积分商品库存不足';
        }

        return true;
    }

    // 验证地址信息
    protected function checkAddress($value, $rule, $data)
    {
        $condition = [
            'id' => (int)$value,
            'user_id' => $data['user_id'],
            'del' => 0
        ];
        $address = UserAddress::where($condition)->findOrEmpty();

        if ($address->isEmpty()) {
            return '收货地址信息不存在';
        }

        return true;
    }

}
