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
 * 积分订单
 * Class IntegralOrderEnum
 * @package app\common\enum
 */
class IntegralOrderEnum
{
    // 订单状态order_status
    const ORDER_STATUS_NO_PAID = 0;//待支付
    const ORDER_STATUS_DELIVERY = 1;//待发货
    const ORDER_STATUS_GOODS = 2;//待收货
    const ORDER_STATUS_COMPLETE = 3;//已完成
    const ORDER_STATUS_DOWN = 4;//已关闭

    // 退款状态 refund_status
    const NO_REFUND = 0;//未退款
    const IS_REFUND = 1;//已退款

    // 发货状态 shipping_status
    const SHIPPING_NO = 0;   //未发货
    const SHIPPING_FINISH = 1;  //已发货


    /**
     * @notes 订单状态
     * @param bool $type
     * @return string|string[]
     * @author 段誉
     * @date 2022/3/3 14:18
     */
    public static function getOrderStatus($type=true)
    {
        $desc = [
            self::ORDER_STATUS_NO_PAID  => '待支付',
            self::ORDER_STATUS_DELIVERY => '待发货',
            self::ORDER_STATUS_GOODS    => '待收货',
            self::ORDER_STATUS_COMPLETE => '已完成',
            self::ORDER_STATUS_DOWN     => '已关闭'
        ];

        if ($type === true) {
            return $desc;
        }
        return $desc[$type] ?? '未知来源';
    }


    /**
     * @notes 退款状态
     * @param bool $type
     * @return string|string[]
     * @author 段誉
     * @date 2022/3/3 14:18
     */
    public static function getRefundStatus($type=true)
    {
        $desc = [
            self::NO_REFUND   => '未退款',
            self::IS_REFUND  => '已退款',
        ];

        if ($type === true) {
            return $desc;
        }
        return $desc[$type] ?? '--';
    }
}