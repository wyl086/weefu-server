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
 * 支付
 * Class PayEnum
 * @package app\common\enum
 */
class PayEnum
{
    /**
     * 支付方式
     */
    const WECHAT_PAY    = 1; //微信支付
    const ALI_PAY       = 2; //支付宝支付
    const BALANCE_PAY   = 3; //余额支付
    const OFFLINE_PAY   = 4; //线下支付
    // 汇付斗拱微信
    const HFDG_WECHAT = 55;
    // 汇付斗拱支付宝
    const HFDG_ALIPAY = 66;

    const UNPAID = 0;//待支付
    const ISPAID = 1;//已支付
    const REFUNDED = 2;//已退款
    const REFUSED_REFUND = 3;//拒绝退款


    /**
     * Notes: 支付方式
     * @param bool $type
     * @author 段誉(2021/5/7 15:01)
     * @return array|string
     */
    public static function getPayWay($type = true)
    {
        $data = [
            self::WECHAT_PAY    => '微信支付',
            self::ALI_PAY       => '支付宝支付',
            self::BALANCE_PAY   => '余额支付',
            // self::OFFLINE_PAY   => '线下支付',
            self::HFDG_WECHAT   => '汇付斗拱(微信)',
            self::HFDG_ALIPAY   => '汇付斗拱(支付宝)',
        ];
        if (true === $type) {
            return $data;
        }
        return $data[$type] ?? '-';
    }


    //支付状态
    public static function getPayStatus($type)
    {
        $data = [
            PayEnum::UNPAID => '待支付',
            PayEnum::ISPAID => '已支付',
            PayEnum::REFUNDED => '已退款',
            PayEnum::REFUSED_REFUND => '拒绝退款',
        ];

        if ($type === true) {
            return $data;
        }
        return $data[$type] ?? '未知';
    }
}