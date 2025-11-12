<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\admin\logic\index;

use app\common\basics\Logic;
use app\common\server\UrlServer;
use app\common\enum\PayEnum;
use think\facade\Db;

/**
 * 工作台统计
 * Class StatLogic
 * @package app\admin\logic\index
 */
class StatLogic extends Logic
{
    //工作台基本数据 TODO
    public static function stat()
    {
        //更新时间
        $time = date('Y-m-d H:i:s', time());
        //头部数据统计
        $data = $where = [];
        $where[] = ['pay_status', '>', PayEnum::UNPAID];

        //成交笔数
        $order_num_all        = Db::name('order')
                                ->where($where)
                                ->count('id');
        $order_num_yesterday  = Db::name('order')
                                ->where($where)
                                ->whereTime('create_time', 'yesterday')
                                ->count('id');
        $order_num_today      = Db::name('order')
                                ->where($where)
                                ->whereTime('create_time', 'today')
                                ->count('id');
        $order_num_change_red = 0;
        $order_num_change_add = $order_num_today - $order_num_yesterday;
        if($order_num_change_add < 0){
            $order_num_change_red = abs($order_num_change_add);
        }

        //营业额
        $order_price_all       = Db::name('order')
                                ->where($where)
                                ->sum('order_amount');
        $order_price_yesterday = Db::name('order')
                                ->where($where)
                                ->whereTime('create_time', 'yesterday')
                                ->sum('order_amount');
        $order_price_today     = Db::name('order')
                                ->where($where)
                                ->whereTime('create_time', 'today')
                                ->sum('order_amount');
        $order_price_change_red = 0;
        $order_price_change_add = $order_price_today - $order_price_yesterday;
        if($order_price_change_add < 0){
            $order_price_change_red = abs($order_price_change_add);
        }

        //新增会员
        $add_user_all       = Db::name('user')
                                ->count('id');
        $add_user_yesterday = Db::name('user')
                                ->whereTime('create_time', 'yesterday')
                                ->count('id');
        $add_user_today     = Db::name('user')
                                ->whereTime('create_time', 'today')
                                ->count('id');
        $add_user_change_red = 0;
        $add_user_change_add = $add_user_today - $add_user_yesterday;
        if($add_user_change_add < 0){
            $add_user_change_red = abs($add_user_change_add);
        }

        //用户访问量UV
        $visit_user_all       = Db::name('stat')
                                ->group('ip')
                                ->count('id');
        $visit_user_yesterday = Db::name('stat')
                                ->whereTime('create_time', 'yesterday')
                                ->group('ip')
                                ->count('id');
        $visit_user_today     = Db::name('stat')
                                ->whereTime('create_time', 'today')
                                ->group('ip')
                                ->count('id');
        $visit_user_change_red = 0;
        $visit_user_change_add = $visit_user_today - $visit_user_yesterday;
        if($visit_user_change_add < 0){
            $visit_user_change_red = abs($visit_user_change_add);
        }


        
        $data = [
            'order_num'         => [
                'yesterday'  => $order_num_yesterday,
                'today'      => $order_num_today,
                'change_add' => $order_num_change_add,
                'change_red' => $order_num_change_red,
                'all_num'    => $order_num_all
            ],
            'order_price'       => [
                'yesterday'  => number_format($order_price_yesterday,2),
                'today'      => number_format($order_price_today,2),
                'change_add' => number_format($order_price_change_add,2),
                'change_red' => number_format($order_price_change_red,2),
                'all_price'  => number_format($order_price_all,2)
            ],
            'add_user_num'      => [
                'yesterday'  => $add_user_yesterday,
                'today'      => $add_user_today,
                'change_add' => $add_user_change_add,
                'change_red' => $add_user_change_red,
                'all_num'    => $add_user_all
            ],
            'visit_user_num'    => [
                'yesterday'  => $visit_user_yesterday,
                'today'      => $visit_user_today,
                'change_add' => $visit_user_change_add,
                'change_red' => $visit_user_change_red,
                'all_num'    => $visit_user_all
            ],
        ];



               
        return [
            'time'      => $time,
            'data'      => $data,
        ];
    }


    //图标数据 
    public static function graphData()
    {
        //当前时间戳
        $start_t = time();
        //echarts图表数据
        $echarts_order_amount = [];
        $echarts_user_pv = [];
        $dates = [];
        for ($i = 15; $i >= 1; $i--) {
            $where_start = strtotime("- ".$i."day", $start_t);
            $dates[] = date('m-d',$where_start);
            $start_now = strtotime(date('Y-m-d',$where_start));
            $end_now = strtotime(date('Y-m-d 23:59:59',$where_start));
            $amount = Db::name('order')
                    ->where([['create_time','between',[$start_now, $end_now]],['pay_status', '>', PayEnum::UNPAID]])
                    ->sum('order_amount');
            $pv = Db::name('stat')
                    ->where([['create_time','between',[$start_now, $end_now]]])
                    ->group('ip')
                    ->count('id');
            $echarts_order_amount[] = sprintf("%.2f",substr(sprintf("%.3f", $amount), 0, -2));
            $echarts_user_pv[] = $pv;
        }
        return [
            'echarts_order_amount'  => $echarts_order_amount,
            'echarts_user_visit'    => $echarts_user_pv,
            'dates'                 => $dates,
        ];
    }


    // 工作台商家数据
    public static function shopLists($get)
    {
        $shop_list = [];
        // 销冠商家、人气商家的商家列表
        if($get['type'] == 1){
            // 销冠商家
            $shop_list = Db::name('order')->alias('o')
                    ->join('shop s','s.id = o.shop_id')
                    ->where([['order_amount','>',0], ['pay_status', '>', PayEnum::UNPAID]])
                    ->group('shop_id')
                    ->limit(5)
                    ->order('order_amount desc')
                    ->column('o.shop_id,sum(o.order_amount) as order_amount,s.logo,s.name');
                    foreach($shop_list as $k => $shop){
                        $shop_list[$k]['number'] = $k+1;
                        $shop_list[$k]['order_amount'] = '￥'.number_format($shop['order_amount'],2);
                        $shop_list[$k]['logo'] = UrlServer::getFileUrl($shop['logo']);
                    }
        }else{
            // 人气商家
            $shop_list = Db::name('shop')
            ->order('visited_num desc')
            ->where([['visited_num','>',0]])
            ->limit(5)
            ->column('id,logo,name,visited_num');
            foreach($shop_list as $k => $shop){
                $shop_list[$k]['number'] = $k+1;
                $shop_list[$k]['logo'] = UrlServer::getFileUrl($shop['logo']);
            }
        }
        return ['count'=>0,'lists'=>$shop_list];
    }
}