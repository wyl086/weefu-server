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
 * 退款订单相关 枚举类型
 * Class OrderRefundEnum
 * @Author ISH
 * @package app\common\enum
 */
class OrderRefundEnum
{
    //退款状态refund_status
    const REFUND_STATUS_ING = 0;//退款中
    const REFUND_STATUS_COMPLETE = 1;//退款完成
    const REFUND_STATUS_FAIL = 2;//退款失败
    const REFUND_STATUS_ABNORMAL = 3;//退款异常
   
}