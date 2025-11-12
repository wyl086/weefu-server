<?php
namespace app\api\controller;

use app\common\basics\Api;
use app\api\logic\CouponLogic;
use app\common\server\JsonServer;
use think\exception\ValidateException;
use app\api\validate\CouponValidate;

class Coupon extends Api
{
    public $like_not_need_login = ['getCouponList'];

    /**
     * 领券中心
     */
    public function getCouponList()
    {
        $get = $this->request->get();
        $get['page_no'] = $this->page_no;
        $get['page_size'] = $this->page_size;
        $get['user_id'] = $this->user_id;
        $data = CouponLogic::getCouponList($get);
        return JsonServer::success('', $data);
    }

    /**
     * 领取优惠券
     */
    public function getCoupon()
    {
        $post = $this->request->post();
        $post['user_id'] = $this->user_id ? $this->user_id : '';

        try{
            validate(CouponValidate::class)->scene('getCoupon
            ')->check($post);
        }catch(ValidateException $e) {
            return JsonServer::error($e->getError());
        }
        $result = CouponLogic::getCoupon($post);
        if($result === true) {
            return JsonServer::success('已领取');
        }
        return JsonServer::error(CouponLogic::getError());
    }

    /**
     * 我的优惠券
     */
    public function myCouponList()
    {
        $get = $this->request->get();
        $get['page_no'] = $this->page_no;
        $get['page_size'] = $this->page_size;
        $get['user_id'] = $this->user_id;
        $data = CouponLogic::myCouponList($get);
        return JsonServer::success('', $data);
    }

    /**
     * 结算页优惠券
     */
    public function getBuyCouponList()
    {
        $post = $this->request->post();
        $post['page_no'] = $this->page_no;
        $post['page_size'] = $this->page_size;
        $post['user_id'] = $this->user_id;
        $data = CouponLogic::getBuyCouponList($post);
        return JsonServer::success('', $data);
    }
}