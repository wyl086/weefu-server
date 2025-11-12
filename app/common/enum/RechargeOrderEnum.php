<?php
namespace app\common\enum;

class RechargeOrderEnum
{
    const WECHAT_PAY    = 1; //微信支付
    const ALI_PAY       = 2; //支付宝支付

    const PAY_STATUS_NO_PAID       = 0; //待支付
    const PAY_STATUS_PAID       = 1; //已支付
}