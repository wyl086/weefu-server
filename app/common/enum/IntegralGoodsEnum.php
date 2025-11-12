<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\common\enum;

/**
 * 积分商品
 * Class IntegralGoodsEnum
 * @package app\common\enum
 */
class IntegralGoodsEnum
{
    // 兑换类型
    const TYPE_GOODS = 1;  //商品
    const TYPE_BALANCE = 2;  //红包（余额）

    // 商品状态
    const STATUS_SOLD_OUT   = 0;  //下架
    const STATUS_SHELVES    = 1;  //上架中

    // 删除状态
    const DEL_NORMAL = 0; // 正常
    const DEL_TRUE = 1; // 已删除


    // 兑换方式
    const EXCHANGE_WAY_INTEGRAL = 1; // 积分
    const EXCHANGE_WAY_HYBRID = 2; // 积分 + 金额


    // 物流类型
    const DELIVERY_NO_EXPRESS = 0; // 无需物流
    const DELIVERY_EXPRESS = 1; // 快递配送

    // 运费
    const EXPRESS_TYPE_FREE = 1; // 包邮
    const EXPRESS_TYPE_UNIFIED = 2; // 统一运费

    /**
     * @notes 兑换类型
     * @param bool $type
     * @return string|string[]
     * @author 段誉
     * @date 2022/2/25 17:48
     */
    public static function getTypeDesc($type = true)
    {
        $desc = [
            self::TYPE_GOODS => '商品',
            self::TYPE_BALANCE => '红包',
        ];
        if ($type === true) {
            return $desc;
        }
        return $desc[$type] ?? '';
    }
}