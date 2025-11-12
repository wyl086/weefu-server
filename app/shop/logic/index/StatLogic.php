<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | gitee下载：https://gitee.com/multshop_gitee
// | github下载：https://github.com/multshop-github
// | 访问官网：https://www.multshop.cn
// | 访问社区：https://home.multshop.cn
// | 访问手册：http://doc.multshop.cn
// | 微信公众号：multshop技术社区
// | multshop系列产品在gitee、github等公开渠道开源版本可免费商用，未经许可不能去除前后端官方版权标识
// |  multshop系列产品收费版本务必购买商业授权，购买去版权授权后，方可去除前后端官方版权标识
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\shop\logic\index;

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
    public static function stat($shop_id)
    {
        //更新时间
        $time = date('Y-m-d H:i:s', time());
        //头部数据统计
        $data = $where = [];
        $where[] = ['pay_status', '>', PayEnum::UNPAID];
        $where[] = ['shop_id','=',$shop_id];

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

        //销售金额
        $order_price_all       = Db::name('order')
                                ->where($where)
                                ->sum('order_amount') ?? 0;
        $order_price_yesterday = Db::name('order')
                                ->where($where)
                                ->whereTime('create_time', 'yesterday')
                                ->sum('order_amount') ?? 0;
        $order_price_today     = Db::name('order')
                                ->where($where)
                                ->whereTime('create_time', 'today')
                                ->sum('order_amount') ?? 0;
        $order_price_change_red = 0;
        $order_price_change_add = $order_price_today - $order_price_yesterday;
        if($order_price_change_add < 0){
            $order_price_change_red = abs($order_price_change_add);
        }

        $where = [];
        $where[] = ['shop_id','=',$shop_id];
        //进店人数
        $add_user_all       = Db::name('shop_stat')
                                ->where($where)
                                ->group(['ip'])
                                ->count('id');
        $add_user_yesterday = Db::name('shop_stat')
                                ->where($where)
                                ->whereTime('create_time', 'yesterday')
                                ->group(['ip'])
                                ->count('id');
        $add_user_today     = Db::name('shop_stat')
                                ->where($where)
                                ->whereTime('create_time', 'today')
                                ->group(['ip'])
                                ->count('id');
        $add_user_change_red = 0;
        $add_user_change_add = $add_user_today - $add_user_yesterday;
        if($add_user_change_add < 0){
            $add_user_change_red = abs($add_user_change_add);
        }

        //商品浏览人数
        $visit_user_all       = Db::name('goods_click')
                                ->where($where)
                                ->group(['user_id'])
                                ->count('id');
        $visit_user_yesterday = Db::name('goods_click')
                                ->where($where)
                                ->whereTime('create_time', 'yesterday')
                                ->group(['user_id'])
                                ->count('id');
        $visit_user_today     = Db::name('goods_click')
                                ->where($where)
                                ->whereTime('create_time', 'today')
                                ->group(['user_id'])
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



   
    //图标数据 TODO
    public static function graphData($shop_id)
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
                    ->where([['shop_id','=',$shop_id],['create_time','between',[$start_now, $end_now]],['pay_status','>',PayEnum::UNPAID]])
                    ->sum('order_amount');
            $pv = Db::name('shop_stat')
                    ->where([['shop_id','=',$shop_id],['create_time','between',[$start_now, $end_now]]])
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


    // 工作台商品数据
    public static function goodsLists($get,$shop_id)
    {
        $goods_list = [];
        // 销冠商品、人气商品的商品列表
        if($get['type'] == 1){
            // 销冠商品
            $goods_list = Db::name('order')->alias('o')
                    ->join('order_goods og','og.order_id = o.id')
                    ->where([['o.shop_id','=',$shop_id],['o.pay_status','>',PayEnum::UNPAID]])
                    ->group('og.goods_id')
                    ->limit(5)
                    ->order('order_amount desc')
                    ->column('sum(o.order_amount) as order_amount, og.goods_id');
                  
                    foreach($goods_list as $k => $item){
                        $goods_list[$k]['number'] = $k+1;
                        $goods_list[$k]['order_amount'] = '￥'.number_format($item['order_amount'],2);
                        $goods_list[$k]['image'] = '';
                        $goods_list[$k]['name'] = '';

                        $goods_info = Db::name('goods')
                                    ->where(['id'=>$item['goods_id']])
                                    ->field('name,image')
                                    ->find();
                        if($goods_info){
                            $goods_list[$k]['image'] = UrlServer::getFileUrl($goods_info['image']);
                            $goods_list[$k]['name'] = $goods_info['name'];
                        }
                    }
        }else{
            // 人气商品
            $goods_list = Db::name('goods_click')
                                ->where([['shop_id','=',$shop_id]])
                                ->group(['goods_id'])
                                ->field('goods_id,count(DISTINCT user_id) as visited_num')
                                ->order('visited_num desc')
                                ->limit(5)
                                ->select()
                                ->toArray();
            
            $num = 0;
            foreach($goods_list as $k => $item){
                if($item['visited_num']){
                    $num++;
                    $goods_list[$k]['number'] = $num;
                    $goods_list[$k]['logo'] = $goods_list[$k]['name'] = '';
                    $goods = Db::name('goods')
                            ->where(['id'=>$item['goods_id']])
                            ->field('name,image')
                            ->find();
                    if($goods){
                        $goods_list[$k]['image'] = UrlServer::getFileUrl($goods['image']);
                        $goods_list[$k]['name'] = $goods['name'];
                    }
                }else{
                    unset($goods_list[$k]);
                }
            }
        }
        return ['count'=>0,'lists'=>$goods_list];
    }
}