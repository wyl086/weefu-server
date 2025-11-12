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
 * 售后退款相关 枚举类型
 * Class AfterSaleEnum
 * @Author ISH
 * @package app\common\enum
 */
class AfterSaleEnum
{
    //售后状态status
    const STATUS_ING = 0;//-申请退款
    const STATUS_MECHANT_REFUSED = 1;//商家拒绝
    const STATUS_GOODS_RETURNED = 2;//商品待退货
    const STATUS_RECEIVE_GOODS = 3;//商家待收货
    const STATUS_MECHANT_REFUSED_GOODS = 4;//商家拒收货
    const STATUS_WAITING = 5;//等待退款
    const STATUS_COMPLETE = 6;//退款成功
   
}