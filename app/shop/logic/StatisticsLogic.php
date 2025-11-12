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


namespace app\shop\logic;

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
    public static function visit($post,$shop_id)
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
        $user_count = Db::name('shop_stat')
            ->where([['create_time', 'between', [$start_time, $end_time]],['shop_id', '=', $shop_id]])
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

            $add = Db::name('shop_stat')
                ->where([['create_time', 'between', [$start_now, $end_now]],['shop_id', '=', $shop_id]])
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
     * Notes: 交易分析
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function trading($post,$shop_id)
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
            ->where([['create_time', 'between', [$start_time, $end_time]], ['pay_status', '>', PayEnum::UNPAID],['shop_id', '=', $shop_id]])
            ->count('id');
        $order_amount = Db::name('order')
            ->where([['create_time', 'between', [$start_time, $end_time]], ['pay_status', '>', PayEnum::UNPAID],['shop_id', '=', $shop_id]])
            ->sum('order_amount') ?? 0;

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
                ->where([['create_time', 'between', [$start_now, $end_now]], ['pay_status', '>', PayEnum::UNPAID],['shop_id', '=', $shop_id]])
                ->count('id');
            $order_amount_add = Db::name('order')
                ->where([['create_time', 'between', [$start_now, $end_now]], ['pay_status', '>', PayEnum::UNPAID],['shop_id', '=', $shop_id]])
                ->sum('order_amount') ?? 0;

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
     * Notes: 商品分析
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function goods($get,$shop_id)
    {
        if (!isset($get['search_key'])) {
            $get['search_key'] = 'sales_volume';
        }

        // 商品列表      
        $goods_count = Db::name('order')->alias('o')
            ->join('order_goods og', 'og.order_id = o.id')
            ->join('shop s', 's.id = o.shop_id')
            ->where([['o.pay_status', '=', 1],['o.shop_id', '=', $shop_id]])
            ->group('og.goods_id')
            ->count();

        $goods_list = Db::name('order')->alias('o')
            ->join('order_goods og', 'og.order_id = o.id')
            ->join('shop s', 's.id = o.shop_id')
            ->where([['o.pay_status', '=', 1],['o.shop_id', '=', $shop_id]])
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
