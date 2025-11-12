<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\common\model;


use app\common\basics\Models;

class AfterSale extends Models
{
    //售后状态
    const STATUS_APPLY_REFUND           = 0;//申请退款
    const STATUS_REFUSE_REFUND          = 1;//商家拒绝
    const STATUS_WAIT_RETURN_GOODS      = 2;//商品待退货
    const STATUS_WAIT_RECEIVE_GOODS     = 3;//商家待收货
    const STATUS_REFUSE_RECEIVE_GOODS   = 4;//商家拒收货
    const STATUS_WAIT_REFUND            = 5;//等待退款
    const STATUS_SUCCESS_REFUND         = 6;//退款成功
}