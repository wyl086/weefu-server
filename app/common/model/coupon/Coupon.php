<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\common\model\coupon;

use app\common\basics\Models;
use app\common\model\coupon\CouponList;
use app\common\model\shop\Shop;

class Coupon extends Models{
    // 使用场景
    public function getUseGoodsTypeDescAttr($value,$data){
        switch ($value){
            case 1:
                return '全店通用';
            case 2:
                return '部分商品可用';
            case 3:
                return '部分商品不可用';
            default:
                return '';
        }
    }

    // 优惠金额
    public function getConditionTypeDescAttr($value,$data){
        if($value == 1){
            return '无门槛,减'.$data['money'].'元';
        }
        if($value == 2){
            return '订单满'.$data['condition_money'].'元,减'.$data['money'].'元';
        }
    }

    // 使用门槛
    public function getConditionTypeDescTwoAttr($value,$data){
        if($value == 1){
            return '无门槛';
        }
        if($value == 2){
            return '订单满'.$data['condition_money'].'元可用';
        }
    }

    // 已领取/剩余
    public static function getSendTotalTypeDescAttr($value,$data){
        $send_total = CouponList::where(['coupon_id'=>$data['id']])->count();
        $tips = $send_total;
        if($data['send_total_type'] == 1){
            $tips .= '/不限';
        }else{
            $residue = $data['send_total'] - $send_total;
            $tips .= '/'.$residue;
        }
        return $tips;
    }

    // 发放情况
    public function getSendInfoAttr($value, $data)
    {
        switch($value) {
            case 1: // 不限制
                $already_issue = CouponList::where(['coupon_id'=>$data['id']])->count();
                $wait_issue = '不限量';
                $total_issue = '不限量';
                break;
            case 2: // 限制数量
                $already_issue = CouponList::where(['coupon_id'=>$data['id']])->count();
                $wait_issue = $data['send_total'] - $already_issue;
                $total_issue = $data['send_total'];
                break;
        }
        $info = <<<EOD
已发放:{$already_issue} <br />
待发放:{$wait_issue} <br />
发放总量：{$total_issue} <br />
EOD;
        return $info;
    }

    //领取方式
    public function getGetTypeDescAttr($value,$data){
        switch ($value){
            case 1:
                return '直接领取';
            case 2:
                return '商家赠送';
            default:
                return '';
        }
    }

    // 状态
    public function getStatusDescAttr($value){
        $desc = [0=>'已下架', 1=>'上架中'];
        return $desc[$value];
    }

    // 发放时间
    public function getSendTimeAttr($value, $data)
    {
        $start_time = date('Y-m-d H:i:s', $data['send_time_start']);
        $end_time = date('Y-m-d H:i:s', $data['send_time_end']);
        return $start_time.' 至 '.$end_time;
    }

    //用券时间
    public function getUseTimeDescAttr($value,$data){
        if($data['use_time_type'] == 1){ // 固定时间
            return date('Y-m-d H:i:s',$data['use_time_start']).' 至 ' .date('Y-m-d H:i:s',$data['use_time_end']);
        }
        if($data['use_time_type'] == 2){
            return '领取当天起'.$data['use_time'].'天内可用';
        }
        if($data['use_time_type'] == 3){
            return '领取次日起'.$data['use_time'].'天内可用';
        }
    }

    // 创建时间
//    public function getCreateTimeAttr($value, $data)
//    {
//        return date('Y-m-d H:i:s', $value);
//    }

    /**
     * 关联模型
     */
    public function shop()
    {
        return $this->hasOne(Shop::class, 'id', 'shop_id')
            ->field('id,name');
    }
}