<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\admin\logic\coupon;

use app\common\server\UrlServer;
use app\common\basics\Logic;
use app\common\model\coupon\Coupon;
use app\common\model\coupon\CouponGoods;
use app\common\model\coupon\CouponList;
use app\common\model\user\UserLevel;


class ShopCouponLogic extends Logic{
    public static function lists($get)
    {
        $where = [
            ['c.del','=',0]
        ];

        if(empty($get['type'])) {
            // 已下架
            $where[] = ['c.status', '=', '0'];
        }else{
            $where[] = ['c.status', '=', '1'];
        }

        // 商家名称
        if(isset($get['shop_name']) && !empty($get['shop_name'])) {
            $where[] = ['s.name', 'like', '%'.trim($get['shop_name']).'%' ];
        }

        // 优惠券名称
        if(isset($get['name']) && !empty($get['name'])) {
            $where[] = ['c.name', 'like', '%'.trim($get['name']).'%' ];
        }

        // 领取方式
        if(isset($get['get_type']) && !empty($get['get_type'])) {
            $where[] = ['c.get_type', '=', $get['get_type'] ];
        }

        // 创建时间
        if(isset($get['start_time']) && !empty($get['start_time'])) {
            $where[] = ['c.create_time', '>=', strtotime($get['start_time']) ];
        }

        if(isset($get['end_time']) && !empty($get['end_time'])) {
            $where[] = ['c.create_time', '<=', strtotime($get['end_time']) ];
        }

        $coupon_count = Coupon::alias('c')
            ->leftJoin('shop s', 's.id=c.shop_id')
            ->where($where)->count();

        $coupon_list = Coupon::alias('c')
            ->leftJoin('shop s', 's.id=c.shop_id')
            ->field('c.id,c.name,c.money,c.use_goods_type,c.use_goods_type as use_goods_type_desc,c.condition_type,c.condition_money,c.condition_type as condition_type_desc,c.send_total_type,c.send_total_type as send_total_type_desc,c.send_total,c.get_type,c.get_type as get_type_desc,c.status,c.status as statusDesc,c.send_time_start,c.send_time_end,c.send_time_start as send_time,c.use_time_type,c.use_time_start,c.use_time_end,c.use_time,c.use_time_type as use_time_desc,c.create_time,s.id as shop_id,s.name as shop_name,s.logo as shop_logo,s.type as shop_type')
            ->where($where)
            ->page($get['page'], $get['limit'])
            ->order('id desc')
            ->select()
            ->toArray();

        $shopTypeDesc = [1=>'官方自营', 2=>'入驻商家'];
        foreach($coupon_list as &$item) {
            $item['shop_type_desc'] = $shopTypeDesc[$item['shop_type']];
            $item['logo'] = UrlServer::getFileUrl($item['shop_logo']);
        }

        return ['count' => $coupon_count, 'list' => $coupon_list];

    }

    public static function getCoupon($id,$get_data = false)
    {
        $coupon = Coupon::findOrEmpty($id);
        $coupon['goods_coupon'] = [];
        if($get_data) {
            $coupon = $coupon->getData();
            $coupon['send_time_start'] = date('Y-m-d H:i:s',$coupon['send_time_start']);
            $coupon['send_time_end'] = date('Y-m-d H:i:s',$coupon['send_time_end']);
            if($coupon['use_goods_type'] != 1){ // 非全部商品
                $goods_coupon= CouponGoods::alias('cg')
                    ->join('goods g','cg.goods_id = g.id')
                    ->where(['coupon_id'=>$id])
                    ->field('g.id,name,max_price,min_price,stock')
                    ->select();
                foreach ($goods_coupon as &$item){
                    $item['price'] = '￥'.$item['min_price'].'~'.'￥'.$item['max_price'];
                    if($item['max_price'] == $item['min_price']){
                        $item['price'] = '￥'.$item['min_price'];
                    }
                }
                $coupon['goods_coupon'] = $goods_coupon;
            }
            if($coupon['use_time_start']){
                $coupon['use_time_start'] = date('Y-m-d H:i:s',$coupon['use_time_start']);
                $coupon['use_time_end'] = date('Y-m-d H:i:s',$coupon['use_time_end']);
            }
        }

        return $coupon;
    }

    /*
 * 发放记录
 */
    public static function record($get)
    {
        $where[] = ['cl.del','=',0];
        $where[] = ['cl.coupon_id','=',$get['id']];

        if(isset($get['keyword']) && $get['keyword']){
            switch($get['search_type']) {
                case 'sn';
                    $where[] = ['u.sn', '=', $get['keyword']];
                    break;
                case 'nickname';
                    $where[] = ['u.nickname', '=', $get['keyword']];
                    break;
                case 'mobile';
                    $where[] = ['u.mobile', '=', $get['keyword']];
                    break;
            }
        }

        if(isset($get['status']) && $get['status'] != '') {
            $where[] = ['cl.status', '=', $get['status']];
        }

        $log_count = CouponList::alias('cl')
            ->join('user u','cl.user_id = u.id')
            ->where($where)
            ->count();

        $log_list = CouponList::alias('cl')
            ->join('user u','cl.user_id = u.id')
            ->where($where)
            ->field('cl.coupon_id,cl.status as cl_status,coupon_code,cl.create_time as cl_create_time,cl.use_time,u.nickname,u.avatar,u.mobile,u.sn,u.level')
            ->page($get['page'], $get['limit'])
            ->select();

        $coupon = Coupon::find($get['id']);
        $coupon_list = Coupon::where(['del'=>0])->column('name','id');
        $level_name =  UserLevel::where(['del'=>0])->column('name','id');

        foreach ($log_list as &$item)
        {
            // 计算过期时间
            switch($coupon->use_time_type) {
                case 1:
                    $item['expired_time_desc'] = date('Y-m-d H:i:s', $coupon['use_time_end']);
                    break;
                case 2: // 领券当天起
                    $datatime = '+'.$coupon['use_time']. ' day';
                    $expired_time = strtotime($datatime, $item['cl_create_time']);
                    $item['expired_time_desc'] = date('Y-m-d H:i:s', $expired_time);
                    break;
                case 3: // 领券次日起
                    $datatime = '+'.($coupon['use_time'] + 1). ' day';
                    $expired_time = strtotime($datatime, $item['cl_create_time']);
                    $item['expired_time_desc'] = date('Y-m-d H:i:s', $expired_time);
                    break;
            }

            $item['coupon_name'] = $coupon_list[$item['coupon_id']] ?? '';
            $item['avatar'] = UrlServer::getFileUrl($item['avatar']);
            $item['level_name'] = $level_name[$item['level']] ?? '';
            $item['status_desc'] = $item['cl_status'] ? '已使用' : '未使用';
            $item['cl_create_time'] = date('Y-m-d H:i:s',$item['cl_create_time']);
            $item['use_time_desc'] = $item['use_time'] ? date('Y-m-d H:i:s',$item['use_time']) : '';
        }
        return ['count'=>$log_count , 'lists'=>$log_list];
    }
}
