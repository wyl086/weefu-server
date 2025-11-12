<?php


namespace app\common\model\shop;


use think\Model;

/**
 * 结算记录模型
 * Class ShopSettlementRecord
 * @package app\common\model\shop
 */
class ShopSettlementRecord extends Model
{
    /**
     * @Notes: 获取器-格式化订单完成时间
     * @Author: 张无忌
     * @param $value
     * @return false|string
     */
    public function getOrderCompleteTimeAttr($value)
    {
        if ($value) {
            return date('Y-m-d H:i:s', $value);
        }

        return $value;
    }
}