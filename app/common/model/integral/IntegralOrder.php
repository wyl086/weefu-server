<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\common\model\integral;

use app\common\basics\Models;
use app\common\enum\IntegralGoodsEnum;
use app\common\enum\IntegralOrderEnum;
use app\common\enum\PayEnum;
use app\common\model\user\User;
use app\common\server\AreaServer;


/**
 * 积分订单
 * Class IntegralOrder
 * @package app\common\model\integral
 */
class IntegralOrder extends Models
{
    // 设置json类型字段
    protected $json = ['goods_snap'];

    // 设置JSON数据返回数组
    protected $jsonAssoc = true;

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
     * @notes 收货地址
     * @param $value
     * @param $data
     * @return mixed|string
     * @author 段誉
     * @date 2022/3/2 10:33
     */
    public function getDeliveryAddressAttr($value, $data)
    {
        return AreaServer::getAddress([
            $data['province'],
            $data['city'],
            $data['district']
        ], $data['address']);
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


    /**
     * @notes 按钮
     * @param $value
     * @param $data
     * @return array
     * @author 段誉
     * @date 2022/3/2 9:43
     */
    public function getBtnsAttr($value, $data)
    {
        $goods_snap = $data['goods_snap'];

        $btns = [
            'pay_btn' => $this->getPayBtn($data),
            'cancel_btn' => $this->getCancelBtn($data, $goods_snap),
            'delivery_btn' => $this->getDeliveryBtn($data, $goods_snap),
            'confirm_btn' => $this->getConfirmBtn($data, $goods_snap),
            'del_btn' => $this->getDelBtn($data),
        ];
        return $btns;
    }


    /**
     * @notes 是否显示支付按钮
     * @param $data
     * @return int
     * @author 段誉
     * @date 2022/3/2 9:36
     */
    public function getPayBtn($data)
    {
        $btn = 0;
        if ($data['order_status'] == IntegralOrderEnum::ORDER_STATUS_NO_PAID && $data['pay_status'] == PayEnum::UNPAID) {
            $btn = 1;
        }
        return $btn;
    }


    /**
     * @notes 返回是否显示取消订单按钮
     * @param $data
     * @return int
     * @author 段誉
     * @date 2022/3/2 9:37
     */
    public function getCancelBtn($data, $goods_snap)
    {
        $btn = 0;
        // 积分订单 商品类型为红包时 不可取消
        if ($goods_snap['type'] == IntegralGoodsEnum::TYPE_BALANCE) {
            return $btn;
        }

        // 未支付的订单 或 已支付但未发货 可以取消
        if (is_string($data['create_time'])) {
            $data['create_time'] = strtotime($data['create_time']);
        }
        if (($data['order_status'] == IntegralOrderEnum::ORDER_STATUS_NO_PAID && $data['pay_status'] == PayEnum::UNPAID)
            || ($data['pay_status'] == PayEnum::ISPAID && $data['order_status'] == IntegralOrderEnum::ORDER_STATUS_DELIVERY)) {
            $btn = 1;
        }
        return $btn;
    }


    /**
     * @notes 返回是否显示物流按钮
     * @param $data
     * @return mixed
     * @author 段誉
     * @date 2022/3/2 9:37
     */
    public function getDeliveryBtn($data, $goods_snap)
    {
        // 红包类型 或 商品无需物流
        if ($goods_snap['type'] == IntegralGoodsEnum::TYPE_BALANCE || $goods_snap['delivery_way'] == 0) {
            return 0;
        }
        return $data['shipping_status'];
    }


    /**
     * @notes 返回是否显示确认收货按钮
     * @param $data
     * @return int
     * @author 段誉
     * @date 2022/3/2 9:37
     */
    public function getConfirmBtn($data, $goods_snap)
    {
        $btn = 0;

        // 红包类型 或 订单无需物流
        if ($goods_snap['type'] == IntegralGoodsEnum::TYPE_BALANCE || $goods_snap['delivery_way'] == 0) {
            return $btn;
        }

        // 订单待收货 且 已发货状态
        if ($data['order_status'] == IntegralOrderEnum::ORDER_STATUS_GOODS && $data['shipping_status'] == 1) {
            $btn = 1;
        }
        return $btn;
    }


    /**
     * @notes 返回是否显示删除按钮
     * @param $data
     * @return int
     * @author 段誉
     * @date 2022/3/2 9:37
     */
    public function getDelBtn($data)
    {
        $btn = 0;
        if ($data['order_status'] == IntegralOrderEnum::ORDER_STATUS_DOWN) {
            if ($data['pay_status'] == PayEnum::UNPAID || $data['refund_status'] == 1) {
                $btn = 1;
            }
        }
        return $btn;
    }

    /**
     * @notes 兑换类型
     * @param $value
     * @param $data
     * @return string|string[]
     * @author ljj
     * @date 2022/3/2 6:06 下午
     */
    public function getTypeDescAttr($value,$data)
    {
        return IntegralGoodsEnum::getTypeDesc($data['exchange_type']);
    }

    /**
     * @notes 订单状态
     * @param $value
     * @param $data
     * @return array|mixed|string|string[]
     * @author ljj
     * @date 2022/3/2 6:16 下午
     */
    public function getOrderStatusDescAttr($value,$data)
    {
        return IntegralOrderEnum::getOrderStatus($data['order_status']);
    }

    /**
     * @notes 支付状态
     * @param $value
     * @param $data
     * @return string|string[]
     * @author ljj
     * @date 2022/3/3 11:06 上午
     */
    public function getPayStatusDescAttr($value,$data)
    {
        return PayEnum::getPayStatus($data['pay_status']);
    }

    /**
     * @notes 支付方式
     * @param $value
     * @param $data
     * @return array|mixed|string|string[]
     * @author ljj
     * @date 2022/3/3 11:10 上午
     */
    public function getPayWayDescAttr($value,$data)
    {
        return PayEnum::getPayWay($data['pay_way']);
    }
}