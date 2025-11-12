<?php

namespace app\common\model\order;

use app\common\basics\Models;
use app\common\enum\OrderEnum;
use app\common\enum\OrderGoodsEnum;
use app\common\enum\PayEnum;
use app\common\model\Client_;
use app\common\model\Pay;
use app\common\model\shop\Shop;
use app\common\model\user\User;
use app\common\server\ConfigServer;
use think\facade\Db;

/**
 * Class order
 * @package app\common\model\order
 */
class Order extends Models
{
    //订单状态
    const STATUS_WAIT_PAY = 0;       //待付款
    const STATUS_WAIT_DELIVERY = 1;  //待发货
    const STATUS_WAIT_RECEIVE = 2;   //待收货
    const STATUS_FINISH = 3;         //已完成
    const STATUS_CLOSE = 4;          //已关闭

    /**
     * @notes 关联OrderGoods模型
     * @return \think\model\relation\HasMany
     * @author suny
     * @date 2021/7/13 6:47 下午
     */
    public function orderGoods()
    {
        return $this->hasMany('order_goods', 'order_id', 'id')
            ->field('id,order_id,goods_id,item_id,goods_name,goods_price,discount_price,spec_value,image,goods_num,is_comment,refund_status,commission_ratio,total_pay_price');
    }

    /**
     * @notes 订单用户
     * @return \think\model\relation\HasOne
     * @author suny
     * @date 2021/7/13 6:48 下午
     */
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id')
            ->field('id,sn,nickname,avatar,level,mobile,sex,create_time');
    }

    /**
     * @notes 关联Shop模型
     * @return \think\model\relation\HasOne
     * @author suny
     * @date 2021/7/13 6:49 下午
     */
    public function shop()
    {
        return $this->hasOne(Shop::class, 'id', 'shop_id')
            ->field('id,name,type,logo,mobile,open_invoice,spec_invoice,weekdays,run_start_time,run_end_time')
            ->append(['type_desc']);
    }

    /**
     * @notes 关联发票模型
     * @return \think\model\relation\HasOne
     * @author 段誉
     * @date 2022/4/11 18:39
     */
    public function invoice()
    {
        return $this->hasOne(OrderInvoice::class, 'order_id', 'id');
    }

    /**
     * @notes 订单状态
     * @param bool $status
     * @param int $shop_id
     * @return string|string[]
     * @author suny
     * @date 2021/7/13 6:46 下午
     */
    public static function getOrderStatus($status = true, $shop_id = 0)
    {

        $desc = [
            OrderEnum::ORDER_STATUS_NO_PAID => "待付款",
            OrderEnum::ORDER_STATUS_DELIVERY => "待发货",
            OrderEnum::ORDER_STATUS_GOODS => "待收货",
            OrderEnum::ORDER_STATUS_COMPLETE => "已完成",
            OrderEnum::ORDER_STATUS_DOWN => "已关闭",
        ];
        if ($status === true) {
            return $desc;
        }
        return $desc[$status] ?? '未知';
    }


    /**
     * @notes 订单类型
     * @param $type
     * @return string|string[]
     * @author suny
     * @date 2021/7/13 6:47 下午
     */
    public static function getOrderType($type)
    {

        $desc = [
            OrderEnum::NORMAL_ORDER => '普通订单',
            OrderEnum::SECKILL_ORDER => '秒杀订单',
            OrderEnum::TEAM_ORDER => '拼团订单',
            OrderEnum::BARGAIN_ORDER => '砍价订单',
        ];

        if ($type === true) {
            return $desc;
        }
        return $desc[$type] ?? '未知';
    }

    /**
     * @notes 配送方式
     * @param $type
     * @return string|string[]
     * @author suny
     * @date 2021/7/13 6:47 下午
     */
    public static function getDeliveryType($type)
    {
        $desc = [
            OrderEnum::DELIVERY_TYPE_EXPRESS => '快递发货',
            OrderEnum::DELIVERY_TYPE_VIRTUAL => '虚拟发货',
            OrderEnum::DELIVERY_TYPE_SELF => '线下自提',
        ];

        if ($type === true) {
            return $desc;
        }
        return $desc[$type] ?? '未知';
    }
    
    /**
     * @notes can_change_invoice_no 是否可修改物流单号
     * @param $value
     * @param $data
     * @return int
     * @author lbzy
     * @datetime 2024-04-02 15:31:40
     */
    function getCanChangeInvoiceNoAttr($value, $data)
    {
        return (int) in_array($data['order_status'], OrderEnum::CAN_CHANGE_INVOICE_NO_ARR);
    }

    /**
     * @notes 配送方式
     * @param $value
     * @param $data
     * @return string|string[]
     * @author suny
     * @date 2021/7/13 6:47 下午
     */
    public function getDeliveryTypeTextAttr($value, $data)
    {
        return self::getDeliveryType($data['delivery_type']);
    }

    /**
     * @notes 订单类型
     * @param $value
     * @param $data
     * @return string|string[]
     * @author suny
     * @date 2021/7/13 6:47 下午
     */
    public function getOrderTypeTextAttr($value, $data)
    {

        return self::getOrderType($data['order_type']);
    }

    /**
     * @notes 订单状态
     * @param $value
     * @param $data
     * @return string|string[]
     * @author suny
     * @date 2021/7/13 6:47 下午
     */
    public function getOrderStatusTextAttr($value, $data)
    {

        return self::getOrderStatus($data['order_status']);
    }

    /**
     * @notes 订单支付方式
     * @param $value
     * @param $data
     * @return array|mixed|string|string[]
     * @author suny
     * @date 2021/7/13 6:48 下午
     */
    public function getPayWayTextAttr($value, $data)
    {

        return PayEnum::getPayWay($data['pay_way']);
    }

    /**
     * @notes 订单支付状态
     * @param $value
     * @param $data
     * @return string|string[]
     * @author suny
     * @date 2021/7/13 6:48 下午
     */
    public function getPayStatusTextAttr($value, $data)
    {

        return PayEnum::getPayStatus($data['pay_status']);
    }

    /**
     * @notes 订单来源
     * @param $value
     * @param $data
     * @return string|string[]
     * @author suny
     * @date 2021/7/13 6:48 下午
     */
    public function getOrderSourceTextAttr($value, $data)
    {

        return Client_::getClient($data['order_source']);
    }

    /**
     * @notes 订单商品数量
     * @param $value
     * @param $data
     * @return int
     * @author suny
     * @date 2021/7/13 6:48 下午
     */
    public function getGoodsCountAttr($value, $data)
    {

        return count($this->order_goods);
    }


    /**
     * @notes 收货地址
     * @param $value
     * @param $data
     * @return string
     * @author suny
     * @date 2021/7/13 6:48 下午
     */
    public function getDeliveryAddressAttr($value, $data)
    {

        $region = Db::name('dev_region')
            ->where('id', 'IN', [$data['province'], $data['city'], $data['district']])
            ->order('level asc')
            ->column('name');

        $region_desc = implode('', $region);
        return $region_desc . $data['address'];
    }

    public function getPcAddressAttr($value, $data)
    {

        $region = Db::name('dev_region')
            ->where('id', 'IN', [$data['province'], $data['city'], $data['district']])
            ->order('level asc')
            ->column('name');

        $region_desc = implode(' ', $region);
        return $region_desc . $data['address'];
    }


    /**
     * @notes 返回是否显示支付按钮
     * @param $value
     * @param $data
     * @return int
     * @author suny
     * @date 2021/7/13 6:48 下午
     */
    public function getPayBtnAttr($value, $data)
    {

        $btn = 0;
        if ($data['order_status'] == OrderEnum::ORDER_STATUS_NO_PAID && $data['pay_status'] == PayEnum::UNPAID) {
            $btn = 1;
        }
        return $btn;
    }

    /**
     * @notes 返回是否显示取消订单按钮
     * @param $value
     * @param $data
     * @return int
     * @author suny
     * @date 2021/7/13 6:48 下午
     */
    public function getCancelBtnAttr($value, $data)
    {

        $btn = 0;
        if (is_string($data['create_time'])) {
            $data['create_time'] = strtotime($data['create_time']);
        }
        //多长时间内允许客户自动取消
        $cancel_limit = ConfigServer::get('transaction', 'paid_order_cancel_time', 60);
        $limit_time = $data['create_time'] + $cancel_limit * 60;
        if ($limit_time < time()) {
            return $btn;
        }

        if (($data['order_status'] == OrderEnum::ORDER_STATUS_NO_PAID && $data['pay_status'] == PayEnum::UNPAID)
            || ($data['pay_status'] == PayEnum::ISPAID && $data['order_status'] == OrderEnum::ORDER_STATUS_DELIVERY)) {
            $btn = 1;
        }
        return $btn;
    }

    /**
     * @notes 返回是否显示物流按钮
     * @param $value
     * @param $data
     * @return int
     * @author suny
     * @date 2021/7/13 6:49 下午
     */
    public function getDeliveryBtnAttr($value, $data)
    {

        $btn = 0;
        // 虚拟发货类型不显示物流查询按钮
        if ($data['delivery_type'] != OrderEnum::DELIVERY_TYPE_EXPRESS) {
            return $btn;
        }

        if ($data['order_status'] == OrderEnum::ORDER_STATUS_GOODS && $data['pay_status'] == PayEnum::ISPAID && $data['shipping_status'] == 1) {
            $btn = 1;
        }
        if ($data['order_status'] == OrderEnum::ORDER_STATUS_COMPLETE && $data['pay_status'] == PayEnum::ISPAID && $data['shipping_status'] == 1) {
            $btn = 1;
        }
        return $btn;

    }

    /**
     * @notes 返回是否显示确认收货按钮
     * @param $value
     * @param $data
     * @return int
     * @author suny
     * @date 2021/7/13 6:49 下午
     */
    public function getTakeBtnAttr($value, $data)
    {
        $btn = 0;
        if ($data['order_status'] == OrderEnum::ORDER_STATUS_GOODS
            && $data['pay_status'] == PayEnum::ISPAID
            && $data['shipping_status'] == 1
            && $data['delivery_type'] != OrderEnum::DELIVERY_TYPE_SELF
        ) {
            $btn = 1;
        }
        return $btn;
    }

    /**
     * @notes 返回是否显示删除按钮
     * @param $value
     * @param $data
     * @return int
     * @author suny
     * @date 2021/7/13 6:49 下午
     */
    public function getDelBtnAttr($value, $data)
    {

        $btn = 0;
        if (
            ($data['order_status'] == OrderEnum::ORDER_STATUS_DOWN && $data['pay_status'] == PayEnum::UNPAID) ||
            ($data['order_status'] == OrderEnum::ORDER_STATUS_DOWN && $data['pay_status'] == PayEnum::REFUNDED)
        ) {
            $btn = 1;
        }
        return $btn;
    }

    /**
     * @notes 返回是否显示已完成按钮
     * @param $value
     * @param $data
     * @return int
     * @author suny
     * @date 2021/7/13 6:49 下午
     */
    public function getFinishBtnAttr($value, $data)
    {

        $btn = 0;
        if ($data['order_status'] == OrderEnum::ORDER_STATUS_COMPLETE && $data['pay_status'] == PayEnum::ISPAID && $data['delivery_type'] != OrderEnum::DELIVERY_TYPE_SELF) {
            $btn = 1;
        }
        return $btn;
    }

    /**
     * @notes 返回是否显示去评论按钮
     * @param $value
     * @param $data
     * @return int
     * @author suny
     * @date 2021/7/13 6:49 下午
     */
    public function getCommentBtnAttr($value, $data)
    {

        $btn = 0;
        $comment_count = 0;
        if ($data['pay_status'] == PayEnum::ISPAID && $data['order_status'] == OrderEnum::ORDER_STATUS_COMPLETE) {
            $btn = 1;
            foreach ($this->order_goods as $item) {
                if ($item['is_comment'] == 1) {
                    $comment_count += 1;
                };
            }
            if (count($this->orderGoods) == $comment_count) {
                $btn = 0;
            }
        }

        return $btn;
    }

    /**
     * @notes 返回是否显示申请退款按钮
     * @param $value
     * @param $data
     * @return int
     * @author suny
     * @date 2021/7/13 6:49 下午
     */
    public function getRefundBtnAttr($value, $data)
    {

        $btn = 0;
        $data['confirm_take_time'] = strtotime($data['confirm_take_time']);
        $refund_days = $data['confirm_take_time'] + ConfigServer::get('after_sale', 'refund_days', '', 0) * 86400;
        $now = time();

        //订单已完成、在售后期内。未申请退款、
        if ($data['order_status'] == OrderEnum::ORDER_STATUS_COMPLETE && $refund_days > $now && $data['refund_status'] = OrderGoodsEnum::REFUND_STATUS_NO) {
            $btn = 1;
        }
        return $btn;
    }


    /**
     * @notes 发货内容(查看内容按钮)
     * @param $value
     * @param $data
     * @return int
     * @author 段誉
     * @date 2022/4/8 10:22
     */
    public function getContentBtnAttr($value, $data)
    {
        $btn = 0;
        if ($data['delivery_type'] == OrderEnum::DELIVERY_TYPE_VIRTUAL && $data['shipping_status'] == OrderEnum::SHIPPING_FINISH) {
            $btn = 1;
        }
        return $btn;
    }


    /**
     * @notes 申请开票按钮
     * @param $value
     * @param $data
     * @return int
     * @author 段誉
     * @date 2022/4/12 15:19
     */
    public function getSaveInvoiceBtnAttr($value, $data)
    {
        $btn = 0;
        $invoice = OrderInvoice::where(['order_id' => $data['id']])->findOrEmpty();
        if ($invoice->isEmpty()) {
            $btn = 1;
        }
        return $btn;
    }
    

    /**
     * @notes 查看开票按钮
     * @param $value
     * @param $data
     * @return int
     * @author 段誉
     * @date 2022/4/12 15:19
     */
    public function getViewInvoiceBtnAttr($value, $data)
    {
        $btn = 0;
        $invoice = OrderInvoice::where(['order_id' => $data['id']])->findOrEmpty();
        if (!$invoice->isEmpty()) {
            $btn = 1;
        }
        return $btn;
    }

    /**
     * @notes 取消订单时间
     * @param $value
     * @param $data
     * @return false|float|int|string
     * @author suny
     * @date 2021/7/13 6:49 下午
     */
    public function getOrderCancelTimeAttr($value, $data)
    {

        $end_time = '';
        if (is_string($data['create_time'])) {
            $data['create_time'] = strtotime($data['create_time']);
        }
        if ($data['order_status'] == 0 && $data['pay_status'] == 0) {
            $order_cancel_time = ConfigServer::get('transaction', 'unpaid_order_cancel_time', 60);
            $end_time = $data['create_time'] + $order_cancel_time * 60;
        }
        return $end_time;
    }

    /**
     * @notes 关联未评价子订单
     * @return \think\model\relation\HasMany
     * @author suny
     * @date 2021/7/13 6:50 下午
     */
    public function orderGoodsUnComment()
    {

        return $this->hasMany('order_goods', 'order_id', 'id')
            ->field('id,order_id,goods_id,item_id,goods_num,goods_name,goods_price,is_comment')
            ->where('is_comment', 0);
    }

    /**
     * @notes 返回是否显示修改地址按钮
     * @param $value
     * @param $data
     * @return int
     * @author ljj
     * @date 2021/11/10 11:37 上午
     */
    public function getEditAddressBtnAttr($value, $data)
    {

        $btn = 0;

        if ($data['delivery_type'] == OrderEnum::DELIVERY_TYPE_SELF) {
            return $btn;
        }

        if ($data['order_status'] == OrderEnum::ORDER_STATUS_NO_PAID) {
            $btn = 1;
        }
        if ($data['order_status'] == OrderEnum::ORDER_STATUS_DELIVERY && $data['pay_status'] == PayEnum::ISPAID) {
            $btn = 1;
        }
        return $btn;

    }

    /**
     * @notes 返回是否显示去发货按钮
     * @param $value
     * @param $data
     * @return int
     * @author ljj
     * @date 2021/11/10 11:41 上午
     */
    public function getToShipBtnAttr($value, $data)
    {

        $btn = 0;
        if ($data['order_status'] == OrderEnum::ORDER_STATUS_DELIVERY && $data['pay_status'] == PayEnum::ISPAID && $data['delivery_type'] != OrderEnum::DELIVERY_TYPE_SELF) {
            $btn = 1;
        }
        return $btn;

    }

    /**
     * @notes 返回是否显示取消订单(商家端)按钮
     * @param $value
     * @param $data
     * @return int
     * @author ljj
     * @date 2021/11/10 11:41 上午
     */
    public function getShopCancelBtnAttr($value, $data)
    {
        $btn = 0;
        if (($data['order_status'] == OrderEnum::ORDER_STATUS_NO_PAID && $data['pay_status'] == PayEnum::UNPAID)
            || ($data['pay_status'] == PayEnum::ISPAID && $data['order_status'] == OrderEnum::ORDER_STATUS_DELIVERY)) {
            $btn = 1;
        }
        return $btn;
    }


    /**
     * @notes 返回是否显示跳转核销(商家端)按钮
     * @param $value
     * @param $data
     * @return int
     * @author mjf
     * @date 2022/11/03 18:57
     */
    public function getToVerificationBtnAttr($value, $data)
    {
        $btn = 0;
        if ($data['pay_status'] == PayEnum::ISPAID
            && $data['order_status'] == OrderEnum::ORDER_STATUS_DELIVERY
            && $data['delivery_type'] == OrderEnum::DELIVERY_TYPE_SELF
            && $data['verification_status'] == OrderEnum::NOT_WRITTEN_OFF
        ) {
            $btn = 1;
        }
        return $btn;
    }


    /**
     * @notes 核销状态描述
     * @param $value
     * @param $data
     * @return string|string[]
     * @author 段誉
     * @date 2022/11/2 14:16
     */
    public function getVerificationStatusTextAttr($value, $data)
    {
        return OrderEnum::getVerificationStatusDesc($data['verification_status']);
    }
    
    /**
     * @notes 汇付斗拱参数
     * @param $fieldValue
     * @param $data
     * @return array
     * @author lbzy
     * @datetime 2023-10-23 17:28:25
     */
    function getHfdgParamsAttr($fieldValue, $data)
    {
        return $fieldValue ? ((array) json_decode($fieldValue, true)) : [];
    }
    
    function setHfdgParamsAttr($fieldValue, $data)
    {
        if (is_string($fieldValue)) {
            return $fieldValue;
        }
        return json_encode((array) $fieldValue, JSON_UNESCAPED_UNICODE);
    }
}