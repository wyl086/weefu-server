<?php
namespace app\api\validate;

use think\Validate;

class CouponValidate extends Validate
{
    protected $rule = [
        'coupon_id' => 'require',
        'user_id' => 'require'
    ];

    protected  $message = [
        'coupon_id.require' => '优惠券ID不能为空',
        'user_id.require' => '用户ID不能为空',
    ];

    public function sceneGetCoupon()
    {
        return $this->only(['coupon_id', 'user_id']);
    }
}