<?php

namespace app\api\validate;

use app\common\basics\Validate;
use app\common\enum\IntegralGoodsEnum;
use app\common\model\integral\IntegralGoods;

/**
 * 积分商品验证
 * Class IntegralOrderValidate
 * @package app\api\validate
 */
class IntegralGoodsValidate extends Validate
{
    protected $rule = [
        'id' => 'require|number|checkGoods',
    ];

    protected $message = [
        'id.require' => '参数缺失',
        'id.number' => '参数类型错误',
    ];


    // 验证商品
    protected function checkGoods($value, $rule, $data)
    {
        $goods = IntegralGoods::where([
            'id' => $value,
            'del' => IntegralGoodsEnum::DEL_NORMAL,
        ])->findOrEmpty();

        if ($goods->isEmpty()) {
            return '积分商品不存在';
        }

        if ($goods['status'] != IntegralGoodsEnum::STATUS_SHELVES) {
            return '商品已下架';
        }

        return true;
    }

}
