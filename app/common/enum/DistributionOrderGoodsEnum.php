<?php
namespace app\common\enum;

class DistributionOrderGoodsEnum
{
    //分销订单状态
    const STATUS_WAIT_HANDLE = 1;//待返佣
    const STATUS_SUCCESS     = 2;//已结算
    const STATUS_ERROR       = 3;//已失效


    //分销订单状态
    public static function getOrderStatus($status = true)
    {
        $desc = [
            self::STATUS_WAIT_HANDLE => '待返佣',
            self::STATUS_SUCCESS     => '已结算',
            self::STATUS_ERROR       => '已失效',
        ];
        if ($status === true) {
            return $desc;
        }
        return $desc[$status] ?? '未知';
    }
}
