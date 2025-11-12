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

use app\common\model\order\Order;

/**
 * 订单相关 枚举类型
 * Class OrderEnum
 * @Author FZR
 * @package app\common\enum
 */
class OrderEnum
{
    const ORDER_CANCEL = 1; //直接退款
    const ORDER_AFTERSALE_CANCEL = 0; //售后退款退款（未退款也是0）
    //来源
    const ORDER_SOURCE_APPLET = 1;//小程序来源
    const ORDER_SOURCE_H5 = 2;//h5来源
    const ORDER_SOURCE_IOS = 3;//ios来源
    const ORDER_SOURCE_ANDROID = 4;//安卓来源

    //订单状态order_status
    const ORDER_STATUS_NO_PAID = 0;//待支付
    const ORDER_STATUS_DELIVERY = 1;//待发货
    const ORDER_STATUS_GOODS = 2;//待收货
    const ORDER_STATUS_COMPLETE = 3;//已完成
    const ORDER_STATUS_DOWN = 4;//已关闭
    
    // 是否可修改物流单号的状态
    const CAN_CHANGE_INVOICE_NO_ARR = [
        self::ORDER_STATUS_GOODS,
    ];

    //支付状态pay_status
    const PAY_STATUS_NO_PAID = 0;//待支付
    const PAY_STATUS_PAID = 1;//已支付
    const PAY_STATUS_REFUND = 2;//已退款
    const PAY_STATUS_REFUSE_REFUND = 3;//拒绝退款

    //配送方式delivery_type
    const DELIVERY_TYPE_EXPRESS = 0;//快递配送
    const DELIVERY_TYPE_VIRTUAL = 1;//虚拟发货
    const DELIVERY_TYPE_SELF = 2;//线下自提
    
    const DELIVERY_TYPES = [
        self::DELIVERY_TYPE_EXPRESS,
        self::DELIVERY_TYPE_VIRTUAL,
        self::DELIVERY_TYPE_SELF,
    ];

    //售后状态aftersale_status
    const AFTERSALE_STATUS_NO_SALE = 0;//不在售后中
    const AFTERSALE_STATUS_SALEING = 1;//售后中

    //退款状态refund_status
    const REFUND_STATUS_NO_REFUND = 0;//未退款
    const REFUND_STATUS_PART_REFUND = 1;//部分退款
    const REFUND_STATUS_ALL_REFUND = 2;//全部退款

    //订单类型
    const NORMAL_ORDER = 0;//普通订单
    const SECKILL_ORDER = 1;//秒杀订单
    const TEAM_ORDER = 2;//拼团订单
    const BARGAIN_ORDER = 3;//砍价订单

    //结算状态
    const SETTLE_WAIT = 0;    //待结算
    const SETTLE_FINISH = 1;  //已结算

    //发货状态
    const SHIPPING_NO = 0;   //未发货
    const SHIPPING_FINISH = 1;  //已发货

    //标识子订单的退款
    const IS_CANCEL_NO = 0;   //售后退款或未退款
    const IS_CANCEL_FINISH = 1;  //直接退款

    //核销状态
    const NOT_WRITTEN_OFF = 0;//待核销
    const WRITTEN_OFF = 1;//已核销

    //订单赠送积分场景
    const ORDER_PAY = 1;//订单付款
    const ORDER_DELIVERY = 2;//订单发货
    const ORDER_FINISH = 3;//订单完成
    const ORDER_AFTER_SALE_OVER = 4;//订单超过售后期


    public static function getPayWay($log)
    {
        $desc = [
            OrderLogEnum::USER_ADD_ORDER => '会员提交订单',
            OrderLogEnum::USER_CANCEL_ORDER => '会员取消订单',
            OrderLogEnum::USER_DEL_ORDER => '会员删除订单',
            OrderLogEnum::USER_CONFIRM_ORDER => '会员确认收货',
            OrderLogEnum::USER_PAID_ORDER => '会员支付订单',
        ];

        if ($log === true) {
            return $desc;
        }

        return isset($desc[$log]) ? $desc[$log] : $log;
    }


    /**
     * @notes 订单赠送积分场景
     * @param $log
     * @return mixed|string|string[]
     * @author ljj
     * @date 2022/2/18 4:01 下午
     */
    public static function getOrderAward($log)
    {
        $desc = [
            self::ORDER_PAY  => '订单付款',
            self::ORDER_DELIVERY => '订单发货',
            self::ORDER_FINISH    => '订单完成',
            self::ORDER_AFTER_SALE_OVER => '订单超过售后期',
        ];

        if ($log === true) {
            return $desc;
        }

        return isset($desc[$log]) ? $desc[$log] : $log;
    }


    /**
     * @Notes: 订单状态
     * @Author: 张无忌
     * @param bool $type
     * @return array|mixed|string
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
     * @Notes: 支付状态
     * @Author: 张无忌
     * @param bool $type
     * @return array|mixed|string
     */
    public static function getPayStatus($type=true)
    {
        $desc = [
            self::PAY_STATUS_NO_PAID  => '待支付',
            self::PAY_STATUS_PAID     => '已支付',
            self::PAY_STATUS_REFUND   => '已退款',
            self::PAY_STATUS_REFUSE_REFUND => '拒绝退款'
        ];

        if ($type === true) {
            return $desc;
        }
        return $desc[$type] ?? '未知来源';
    }

    /**
     * @Notes: 退款状态
     * @Author: 张无忌
     * @param bool $type
     * @return array|mixed|string
     */
    public static function getRefundStatus($type=true)
    {
        $desc = [
            self::REFUND_STATUS_NO_REFUND   => '未退款',
            self::REFUND_STATUS_PART_REFUND => '部分退款',
            self::REFUND_STATUS_ALL_REFUND  => '全部退款',
        ];

        if ($type === true) {
            return $desc;
        }
        return $desc[$type] ?? '未知来源';
    }


    /**
     * @notes 核销状态描述
     * @param bool $value
     * @return string|string[]
     * @author 段誉
     * @date 2022/11/2 14:08
     */
    public static function getVerificationStatusDesc($value = true)
    {
        $data = [
            self::NOT_WRITTEN_OFF => '待核销',
            self::WRITTEN_OFF => '已核销',
        ];
        if (true === $value) {
            return $data;
        }
        return $data[$value];
    }
    
    static function getChangeDeliveryTypeItem($goods_delivery_type)
    {
        switch ($goods_delivery_type) {
            case GoodsEnum::DELIVERY_EXPRESS:
                return static::DELIVERY_TYPE_EXPRESS;
                break;
            case GoodsEnum::DELIVERY_VIRTUAL:
                return static::DELIVERY_TYPE_VIRTUAL;
                break;
            case GoodsEnum::DELIVERY_SELF:
                return static::DELIVERY_TYPE_SELF;
                break;
            default:
                break;
        }
        
        return static::DELIVERY_TYPE_EXPRESS;
    }


}