<?php

namespace app\common\model\after_sale;

use app\common\basics\Models;
use app\common\enum\PayEnum;
use app\common\model\Client_;
use app\common\model\Pay;
use app\common\model\shop\Shop;
use app\common\model\user\User;
use app\common\model\order\Order;
use app\common\model\order\OrderGoods;
use app\common\server\ConfigServer;
use think\facade\Db;

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

    //退款类型
    const TYPE_ONLY_REFUND      = 0;//仅退款
    const TYPE_REFUND_RETURN    = 1;//退款退货

    /**
     * @notes 售后状态描述
     * @param $state
     * @return string|string[]
     * @author suny
     * @date 2021/7/13 6:37 下午
     */
    public static function getStatusDesc($state)
    {
        $data = [
            self::STATUS_APPLY_REFUND           => '申请退款',
            self::STATUS_REFUSE_REFUND          => '商家拒绝',
            self::STATUS_WAIT_RETURN_GOODS      => '商品待退货',
            self::STATUS_WAIT_RECEIVE_GOODS     => '商家待收货',
            self::STATUS_REFUSE_RECEIVE_GOODS   => '商家拒收货',
            self::STATUS_WAIT_REFUND            => '等待退款',
            self::STATUS_SUCCESS_REFUND         => '退款成功',
        ];
        if ($state === true) {
            return $data;
        }
        return $data[$state] ?? '';
    }


    /**
     * @notes 售后类型描述
     * @param $type
     * @return string|string[]
     * @author suny
     * @date 2021/7/13 6:37 下午
     */
    public static function getRefundTypeDesc($type)
    {
        $data = [
            self::TYPE_ONLY_REFUND      => '仅退款',
            self::TYPE_REFUND_RETURN    => '退款退货',
        ];
        if ($type === true) {
            return $data;
        }
        return $data[$type] ?? '';
    }


    /**
     * @notes 售后原因
     * @return string[]
     * @author suny
     * @date 2021/7/13 6:37 下午
     */
    public static function getReasonLists()
    {
        $data = [
            '7天无理由退换货',
            '大小尺寸与商品描述不符',
            '颜色/图案/款式不符',
            '做工粗糙/有瑕疵',
            '质量问题',
            '卖家发错货',
            '少件（含缺少配件）',
            '不喜欢/不想要',
            '快递/物流一直未送到',
            '空包裹',
            '快递/物流无跟踪记录',
            '货物破损已拒签',
            '其他',
        ];
        return $data;
    }


    /**
     * @notes 预载入OrderGoods
     * @return \think\model\relation\HasMany
     * @author suny
     * @date 2021/7/13 6:38 下午
     */
    public function orderGoods()
    {
        return $this->hasMany(OrderGoods::class, 'id', 'order_goods_id');
    }

    /**
     * @notes 预载入user
     * @return \think\model\relation\HasOne
     * @author suny
     * @date 2021/7/13 6:38 下午
     */
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id')
            ->field('id,sn,nickname,avatar,mobile,sex,create_time');
    }

    /**
     * @notes 预载入order
     * @return AfterSale|\think\model\relation\HasOne
     * @author suny
     * @date 2021/7/13 6:38 下午
     */
    public function order()
    {
        return $this->hasOne(Order::class, 'id', 'order_id')
            ->field('id,shop_id,order_sn,total_amount,order_amount,pay_way,order_status,delivery_type')
            ->append(['delivery_type_text']);
    }

    /**
     * @notes 预载入shop
     * @return \think\model\relation\HasOne
     * @author suny
     * @date 2021/7/13 6:38 下午
     */
    public function shop()
    {
        return $this->hasOne(Shop::class, 'id', 'shop_id')
            ->field('id,type,name,logo')->append(['type_desc']);
    }

    /**
     * @notes 预载入after_sale_log
     * @return \think\model\relation\HasMany
     * @author suny
     * @date 2021/7/13 6:39 下午
     */
    public function logs()
    {
        return $this->hasMany('after_sale_log', 'after_sale_id', 'id')->order('id desc');
    }
}