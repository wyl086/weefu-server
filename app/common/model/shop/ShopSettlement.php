<?php


namespace app\common\model\shop;


use think\Model;

/**
 * 商家结算模型
 * Class ShopSettlement
 * @package app\common\model\shop
 */
class ShopSettlement extends Model
{
    /**
     * @Notes: 关联商家模型
     * @Author: 张无忌
     */
    public function shop()
    {
        return $this->hasOne('Shop', 'id', 'shop_id');
    }
}