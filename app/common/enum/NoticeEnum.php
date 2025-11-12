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
 * Class NoticeEnum
 * @package app\common\enum
 */
class NoticeEnum
{
    //通知类型
    const SYSTEM_NOTICE     = 1; //系统通知
    const SMS_NOTICE        = 2; //短信通知
    const OA_NOTICE         = 3; //公众号模板通知
    const MNP_NOTICE        = 4; //小程序订阅消息通知


    //通知对象
    const NOTICE_USER       = 1; //通知会员
    const NOTICE_SHOP       = 2; //通知商家
    const NOTICE_PLATFORM   = 3; //通知平台
    const NOTICE_OTHER      = 4; //通知游客(如新用户注册)


    //*************************************************通知会员********************************************************
    //通知会员-短信验证码
    const REGISTER_NOTICE = 100;  //注册会员短信验证码通知
    const CHANGE_MOBILE_NOTICE = 101;  //变更手机短信验证码通知
    const GET_BACK_MOBILE_NOTICE = 102;  //找回密码短信验证码通知
    const GET_GODE_LOGIN_NOTICE = 110;  //验证码登录
    const BIND_MOBILE_NOTICE = 111;  //绑定手机号
    const GET_BACK_PAY_CODE_NOTICE = 112;  //找回支付密码
    //通知会员-订单相关
    const ORDER_PAY_NOTICE = 103;  //成功下单通知
    const ORDER_DELIVERY_NOTICE = 104;  //订单发货通知
    const AFTER_SALE_NOTICE = 105; //售后退款申请状态通知
    //通知会员-佣金相关
    const GET_FUTURE_EARNINGS_NOTICE = 107; //获得预估推广佣金通知
    const GET_EARNINGS_NOTICE = 113;   //推广佣金到账通知
    //通知会员-其他
    const INVITE_SUCCESS_NOTICE = 106;  //成功邀请粉丝通知
    const SHOP_APPLY_ERROR_NOTICE = 108; //商家入驻申请失败通知
    const SHOP_APPLY_SUCCESS_NOTICE = 109; //商家入驻申请成功通知
    const SHOP_APPLY_CHECK_CODE = 114; //商家入驻手机短信验证通知
    //**************************************************************************************************************


    //*************************************************通知商家和平台********************************************************
    //通知商家
    const USER_PAID_NOTICE_SHOP = 200;//会员支付下单通知商家
    const AFTER_SALE_NOTICE_SHOP = 201;//会员发起售后退款通知商家

    //通知平台
    const SHOP_APPLY_NOTICE_PLATFORM = 300; //商家入驻通知
    const SHOP_WITHDRAWAL_NOTICE_PLATFORM = 301; //商家提现通知
    //*************************************************通知会员********************************************************

    //通知平台的场景
    const NOTICE_PLATFORM_SCENE = [
        self::SHOP_APPLY_NOTICE_PLATFORM,
        self::SHOP_WITHDRAWAL_NOTICE_PLATFORM,
    ];

    //通知商家的场景
    const NOTICE_SHOP_SCENE = [
        self::USER_PAID_NOTICE_SHOP,
        self::AFTER_SALE_NOTICE_SHOP,
    ];


    //通知会员的场景
    const NOTICE_USER_SCENE = [
        self::CHANGE_MOBILE_NOTICE,
        self::GET_BACK_MOBILE_NOTICE,
        self::ORDER_PAY_NOTICE,
        self::ORDER_DELIVERY_NOTICE,
        self::AFTER_SALE_NOTICE,
        self::INVITE_SUCCESS_NOTICE,
        self::GET_FUTURE_EARNINGS_NOTICE,
        self::GET_EARNINGS_NOTICE,
        self::SHOP_APPLY_ERROR_NOTICE,
        self::SHOP_APPLY_SUCCESS_NOTICE,
        self::GET_GODE_LOGIN_NOTICE,
        self::BIND_MOBILE_NOTICE,
        self::GET_BACK_PAY_CODE_NOTICE,
        self::SHOP_APPLY_CHECK_CODE,
    ];

    //通知游客(还不存在当前系统的人)
    const NOTICE_OTHER_SCENE = [
        self::REGISTER_NOTICE
    ];


    //订单相关场景
    const ORDER_SCENE = [
        self::ORDER_PAY_NOTICE,
        self::ORDER_DELIVERY_NOTICE,
        self::AFTER_SALE_NOTICE,
    ];


    //验证码的场景
    const NOTICE_NEED_CODE = [
        self::REGISTER_NOTICE,
        self::CHANGE_MOBILE_NOTICE,
        self::GET_BACK_MOBILE_NOTICE,
        self::GET_GODE_LOGIN_NOTICE,
        self::BIND_MOBILE_NOTICE,
        self::GET_BACK_PAY_CODE_NOTICE,
        self::SHOP_APPLY_CHECK_CODE,
    ];

    //场景值-短信场景
    const SMS_SCENE = [
        'DDZFTZ' => self::ORDER_PAY_NOTICE, //订单支付通知
        'DDFHTZ' => self::ORDER_DELIVERY_NOTICE, //订单发货通知
        'ZCYZ' => self::REGISTER_NOTICE, //注册验证
        'ZHMM' => self::GET_BACK_MOBILE_NOTICE, //找回密码
        'YZMDL' => self::GET_GODE_LOGIN_NOTICE, //验证码登录
        'BGSJHM' => self::CHANGE_MOBILE_NOTICE, //变更手机号码
        'BDSJHM' => self::BIND_MOBILE_NOTICE,  //绑定手机号码
        'ZHZFMM' => self::GET_BACK_PAY_CODE_NOTICE, // 找回支付密码
        'SJRZSB' => self::SHOP_APPLY_ERROR_NOTICE, //商家入驻失败
        'SJRZCG' => self::SHOP_APPLY_SUCCESS_NOTICE, //商家入驻成功
        'SJSQYZ' => self::SHOP_APPLY_CHECK_CODE,//商家入驻申请验证码验证

        'DDTZSJ' => self::USER_PAID_NOTICE_SHOP,//下单通知商家
        'SHTZSJ' => self::AFTER_SALE_NOTICE_SHOP,//售后通知商家

        'SJSQTZPT' => self::SHOP_APPLY_NOTICE_PLATFORM, //商家申请通知平台
        'SJTXTZPT' => self::SHOP_WITHDRAWAL_NOTICE_PLATFORM, //商家提现通知平台
    ];


    /**
     * Notes: 通知描述
     * @param $state
     * @author 段誉(2021/6/4 15:14)
     * @return array|mixed|string
     */
    public static function getSceneDesc($state)
    {
        $data = [
            //会员-短信验证码
            self::REGISTER_NOTICE => '注册会员短信验证码通知',
            self::CHANGE_MOBILE_NOTICE => '变更手机短信验证码通知',
            self::GET_BACK_MOBILE_NOTICE => '找回登录密码短信验证码通知',
            self::GET_GODE_LOGIN_NOTICE => '手机短信验证码登录通知',
            self::BIND_MOBILE_NOTICE => '手机绑定短信验证码验证通知',
            self::GET_BACK_PAY_CODE_NOTICE => '找回支付密码短信验证码通知',
            //会员-订单相关通知
            self::ORDER_PAY_NOTICE => '成功下单通知',
            self::ORDER_DELIVERY_NOTICE => '订单发货通知',
            self::AFTER_SALE_NOTICE => '售后退款申请状态通知',
            //会员-佣金相关通知
            self::GET_FUTURE_EARNINGS_NOTICE => '获得预估推广佣金通知',
            self::GET_EARNINGS_NOTICE => '推广佣金到账通知',
            //会员-其他通知
            self::INVITE_SUCCESS_NOTICE => '成功邀请粉丝通知',
            self::SHOP_APPLY_ERROR_NOTICE => '商家入驻申请失败通知',
            self::SHOP_APPLY_SUCCESS_NOTICE => '商家入驻申请成功通知',
            self::SHOP_APPLY_CHECK_CODE => '商家入驻手机短信验证通知',

            //平台
            self::SHOP_APPLY_NOTICE_PLATFORM => '商家入驻通知',
            self::SHOP_WITHDRAWAL_NOTICE_PLATFORM => '商家提现通知',

            //商家
            self::USER_PAID_NOTICE_SHOP => '订单通知',
            self::AFTER_SALE_NOTICE_SHOP => '售后退款申请通知',
        ];
        if ($state === true) {
            return $data;
        }
        return $data[$state] ?? '';
    }

}