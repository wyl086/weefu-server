<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\admin\logic;


use app\common\basics\Logic;
use app\common\server\UrlServer;
use app\common\enum\PayEnum;
use think\facade\Db;

class StatisticsLogic extends Logic
{

    /**
     * Notes: 访问分析
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function visit($post)
    {
        //获取今天的时间戳 
        $today = strtotime('today');
        //近七天的开始日期
        $start_time = $today - 86400 * 7;
        //近七天的结束日期
        $end_time = $today - 1;

        if (isset($post['start_time']) && $post['start_time'] && isset($post['end_time']) && $post['end_time']) {
            $start_time = strtotime($post['start_time']);
            $end_time   = strtotime($post['end_time']);
        }
        $user_count = Db::name('stat')
            ->where([['create_time', 'between', [$start_time, $end_time]]])
            ->count('id');
            
        //当前时间戳
        $start_t = time();
        //echarts图表数据
        $echarts_count = [];
        $echarts_add = [];
        $dates = [];
        for ($i = 15; $i >= 1; $i--) {
            $where_start = strtotime("- " . $i . "day", $start_t);
            $dates[] = date('m-d', $where_start);
            $start_now = strtotime(date('Y-m-d', $where_start));
            $end_now = strtotime(date('Y-m-d 23:59:59', $where_start));
            $add = Db::name('stat')
                ->where([['create_time', 'between', [$start_now, $end_now]]])
                ->count('id');
            $echarts_count[] = 0;
            $echarts_add[] = $add;
        }

        return [
            'user_count'      => $user_count,
            'start_time'      => date('Y-m-d H:i:s', $start_time),
            'end_time'        => date('Y-m-d H:i:s', $end_time),
            'echarts_count'   => $echarts_count,
            'echarts_add'     => $echarts_add,
            'days'            => $dates,
        ];
    }

    /**
     * Notes: 会员分析
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function member($post)
    {
        //获取今天的时间戳 
        $today = strtotime('today');
        //近七天的开始日期
        $start_time = $today - 86400 * 7;
        //近七天的结束日期
        $end_time = $today - 1;

        if (isset($post['start_time']) && $post['start_time'] && isset($post['end_time']) && $post['end_time']) {
            $start_time = strtotime($post['start_time']);
            $end_time   = strtotime($post['end_time']);
        }
        $user_num = Db::name('user')
            ->count('id');

        $user_add = Db::name('user')
            ->where([['create_time', 'between', [$start_time, $end_time]]])
            ->count('id');

        //当前时间戳
        $start_t = time();
        //echarts图表数据
        $echarts_count = [];
        $echarts_add = [];
        $dates = [];
        for ($i = 15; $i >= 1; $i--) {
            $where_start = strtotime("- " . $i . "day", $start_t);
            $dates[] = date('m-d', $where_start);
            $start_now = strtotime(date('Y-m-d', $where_start));
            $end_now = strtotime(date('Y-m-d 23:59:59', $where_start));
            $add = Db::name('user')
                ->where([['create_time', 'between', [$start_now, $end_now]]])
                ->count('id');
            $echarts_count[] = 0;
            $echarts_add[] = $add;
        }

        return [
            'user_num'        => $user_num,
            'user_add'        => $user_add,
            'start_time'      => date('Y-m-d H:i:s', $start_time),
            'end_time'        => date('Y-m-d H:i:s', $end_time),
            'echarts_count'   => $echarts_count,
            'echarts_add'     => $echarts_add,
            'days'            => $dates,
        ];
    }

    /**
     * Notes: 交易分析
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function trading($post)
    {
        //获取今天的时间戳 
        $today = strtotime('today');
        //近七天的开始日期
        $start_time = $today - 86400 * 7;
        //近七天的结束日期
        $end_time = $today - 1;

        if (isset($post['start_time']) && $post['start_time'] && isset($post['end_time']) && $post['end_time']) {
            $start_time = strtotime($post['start_time']);
            $end_time   = strtotime($post['end_time']);
        }
        $order_num = Db::name('order')
            ->where([['create_time', 'between', [$start_time, $end_time]], ['pay_status', '>', PayEnum::UNPAID]])
            ->count('id');
        $order_amount = Db::name('order')
            ->where([['create_time', 'between', [$start_time, $end_time]], ['pay_status', '>', PayEnum::UNPAID]])
            ->sum('order_amount');

        //当前时间戳
        $start_t = time();
        //echarts图表数据
        $echarts_count = [];
        $echarts_order_num_add = [];
        $echarts_order_amount_add = [];
        $dates = [];
        for ($i = 15; $i >= 1; $i--) {
            $where_start = strtotime("- " . $i . "day", $start_t);
            $dates[] = date('m-d', $where_start);
            $start_now = strtotime(date('Y-m-d', $where_start));
            $end_now = strtotime(date('Y-m-d 23:59:59', $where_start));
            $order_num_add = Db::name('order')
                ->where([['create_time', 'between', [$start_now, $end_now]], ['pay_status', '>', PayEnum::UNPAID]])
                ->count('id');
            $order_amount_add = Db::name('order')
                ->where([['create_time', 'between', [$start_now, $end_now]], ['pay_status', '>', PayEnum::UNPAID]])
                ->sum('order_amount');

            $echarts_count[] = 0;
            $echarts_order_num_add[] = $order_num_add;
            $echarts_order_amount_add[] = sprintf("%.2f",substr(sprintf("%.3f", $order_amount_add), 0, -2));
        }

        return [
            'order_num'                    => $order_num,
            'order_amount'                 => '￥'.number_format($order_amount,2),
            'start_time'                   => date('Y-m-d H:i:s', $start_time),
            'end_time'                     => date('Y-m-d H:i:s', $end_time),
            'echarts_count'                => $echarts_count,
            'echarts_order_num_add'        => $echarts_order_num_add,
            'echarts_order_amount_add'     => $echarts_order_amount_add,
            'days'                         => $dates,
        ];
    }

    /**
     * Notes: 商家分析
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function shop($get)
    {
        if (!isset($get['search_key'])) {
            $get['search_key'] = 'sales_price';
        }

        // 商家列表
        $shop_count = Db::name('shop')
            ->count();

        $shop_list = Db::name('shop')
            ->page($get['page'], $get['limit'])
            ->column('id,logo,type,name,visited_num');
        $shop_ids = array_column($shop_list, 'id');

        $sales_price_list = Db::name('order')
            ->where([['shop_id', 'in', $shop_ids], ['pay_status', '>', PayEnum::UNPAID]])
            ->group('shop_id')
            ->column('sum(order_amount) as sales_price', 'shop_id');

        $shop_follow_list = Db::name('shop_follow')
            ->where([['shop_id', 'in', $shop_ids], ['status', '=', 1]])
            ->group('shop_id')
            ->column('count(id) as follow_num', 'shop_id');

        foreach ($shop_list as $k => $shop) {
            $shop_list[$k]['logo'] = UrlServer::getFileUrl($shop['logo']);
            $shop_list[$k]['sales_price'] = '￥0';
            $shop_list[$k]['follow_num'] = 0;

            if (isset($sales_price_list[$shop['id']])) {
                $shop_list[$k]['sales_price'] =  '￥' . number_format($sales_price_list[$shop['id']], 2);
            }
            if (isset($shop_follow_list[$shop['id']])) {
                $shop_list[$k]['follow_num'] = $shop_follow_list[$shop['id']];
            }
            if ($shop['type'] == 1) {
                $shop_list[$k]['type_desc'] = '官方自营';
            } else {
                $shop_list[$k]['type_desc'] = '入驻商家';
            }
        }

        //排序
        $sort_field = array_column($shop_list, $get['search_key']);
        array_multisort($sort_field, SORT_DESC, $shop_list);

        foreach ($shop_list as $k => $shop) {
            $shop_list[$k]['number'] = $k + 1;
        }

        return ['count' => $shop_count, 'lists' => $shop_list];
    }

    /**
     * Notes: 商品分析
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function goods($get)
    {
        if (!isset($get['search_key'])) {
            $get['search_key'] = 'sales_volume';
        }

        // 商品列表      
        $goods_count = Db::name('order')->alias('o')
            ->join('order_goods og', 'og.order_id = o.id')
            ->join('shop s', 's.id = o.shop_id')
            ->where([['o.pay_status', '=', 1]])
            ->group('og.goods_id')
            ->count();

        $goods_list = Db::name('order')->alias('o')
            ->join('order_goods og', 'og.order_id = o.id')
            ->join('shop s', 's.id = o.shop_id')
            ->where([['o.pay_status', '=', 1]])
            ->group('og.goods_id')
            ->page($get['page'], $get['limit'])
            ->order($get['search_key'].' desc')
            ->column('s.id,s.logo,s.type,s.name,o.shop_id,count(o.id) as sales_volume,sum(o.order_amount) as sales_price,og.image,og.goods_name');

        foreach ($goods_list as $k => $item) {
            $goods_list[$k]['number'] = $k + 1;
            $goods_list[$k]['sales_price'] = '￥' . number_format($item['sales_price'], 2);
            $goods_list[$k]['goods_image'] = UrlServer::getFileUrl($item['image']);
            $goods_list[$k]['logo'] = UrlServer::getFileUrl($item['logo']);

            if ($item['type'] == 1) {
                $goods_list[$k]['type_desc'] = '官方自营';
            } else {
                $goods_list[$k]['type_desc'] = '入驻商家';
            }
        }

        return ['count' => $goods_count, 'lists' => $goods_list];
    }
}
