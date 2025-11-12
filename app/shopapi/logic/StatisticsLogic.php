<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 开源版本可自由商用，可去除界面版权logo
// | 商业版本务必购买商业授权，以免引起法律纠纷
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | gitee下载：https://gitee.com/multshop_gitee
// | github下载：https://github.com/multshop-github
// | 访问官网：https://www.multshop.cn
// | 访问社区：https://home.multshop.cn
// | 访问手册：http://doc.multshop.cn
// | 微信公众号：multshop技术社区
// | multshop团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshopTeam
// +----------------------------------------------------------------------
namespace app\shopapi\logic;
use app\common\enum\OrderEnum;
use app\common\enum\PayEnum;
use app\common\enum\VerificationEnum;
use app\common\model\order\Order;
use app\common\model\shop\Shop;
use app\common\server\UrlServer;
use think\facade\Db;

/**
 * 数据逻辑层
 * Class StatisticsLogic
 * @package app\shopapi\logic
 */
class StatisticsLogic{

    /**
     * @notes 工作台
     * @param int $shop_id
     * @return array
     * @author cjhao
     * @date 2021/11/11 15:23
     */
    public function workbench(int $shop_id){
        //头部数据统计
        $where = [];
        $where[] = ['pay_status', '>', PayEnum::UNPAID];
        $where[] = ['shop_id','=',$shop_id];

        //成交笔数
        $order_num = Db::name('order')
            ->where($where)
            ->whereDay('create_time')
            ->count('id');

        //销售金额
        $order_amount = Db::name('order')
                ->where($where)
                ->whereDay('create_time')
                ->sum('order_amount') ?? 0;

        //进店人数
        $shop_user = Db::name('shop_stat')
            ->where(['shop_id'=>$shop_id])
            ->whereDay('create_time')
            ->group(['ip'])
            ->count('id');

        //当前时间戳
        $start_t = time();
        //echarts图表数据
        $echarts_order_amount = [];
        $echarts_user_pv = [];
        $dates = [];
        for ($i = 7; $i >= 1; $i--) {
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

        $self_order_num = Order::where([
            'delivery_type' => OrderEnum::DELIVERY_TYPE_SELF,
            'pay_status' => PayEnum::ISPAID,
            'shop_id' => $shop_id
        ])->count();
        $is_verification = Order::where([
            'delivery_type' => OrderEnum::DELIVERY_TYPE_SELF,
            'pay_status' => PayEnum::ISPAID,
            'verification_status' => OrderEnum::NOT_WRITTEN_OFF,
            'shop_id' => $shop_id
        ])->count();
        $no_verification = Order::where([
            'delivery_type' => OrderEnum::DELIVERY_TYPE_SELF,
            'pay_status' => PayEnum::ISPAID,
            'verification_status' => OrderEnum::WRITTEN_OFF,
            'shop_id' => $shop_id
        ])->count();

        return [
                'now'                   => date('Y-m-d H:i:s', time()),
                'shop_name'             => Shop::where(['id'=>$shop_id])->value('name'),
                'order_num'             => $order_num,
                'order_amount'          => $order_amount,
                'shop_user'             => $shop_user,
                'self_order'            => $self_order_num,
                'is_verification'       => $is_verification,
                'no_verification'       => $no_verification,
                'echarts_order_amount'  => $echarts_order_amount,
                'echarts_user_visit'    => $echarts_user_pv,
                'dates'                 => $dates,
        ];
    }


    /**
     * @notes 交易接口
     * @param int $shop_id
     * @return array
     * @author cjhao
     * @date 2021/11/11 14:39
     */
    public function trading(int $shop_id){
        //获取今天的时间戳
        $today = strtotime('today');
        //近七天的开始日期
        $start_time = $today - 86400 * 7;
        //近七天的结束日期
        $end_time = $today - 1;

        $order_num = Db::name('order')
            ->where([['pay_status', '>', PayEnum::UNPAID],['shop_id', '=', $shop_id]])
            ->whereDay('create_time')
            ->count('id');
        $order_amount = Db::name('order')
                ->where([['pay_status', '>', PayEnum::UNPAID],['shop_id', '=', $shop_id]])
                ->whereDay('create_time')
                ->sum('order_amount') ?? 0;

        //当前时间戳
        $start_t = time();
        //echarts图表数据
        $echarts_order_num_add = [];
        $echarts_order_amount_add = [];
        $dates = [];
        for ($i = 7; $i >= 1; $i--) {
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

            $echarts_order_num_add[] = $order_num_add;
            $echarts_order_amount_add[] = sprintf("%.2f",substr(sprintf("%.3f", $order_amount_add), 0, -2));
        }

        return [
            'order_num'                    => $order_num,
            'order_amount'                 => '￥'.number_format($order_amount,2),
            'echarts_order_num_add'        => $echarts_order_num_add,
            'echarts_order_amount_add'     => $echarts_order_amount_add,
            'days'                         => $dates,
        ];
    }


    /**
     * @notes 商品分析
     * @param int $shop_id
     * @return array
     * @author cjhao
     * @date 2021/11/11 15:09
     */
    public function goodslist(int $shop_id){
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
            ->limit(20)
            ->order('sales_volume desc')
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

    /**
     * @notes 访问分析
     * @param int $shop_id
     * @return array
     * @author cjhao
     * @date 2021/11/11 15:09
     */
    public function visit(int $shop_id){

        //获取今天的时间戳
        $today = strtotime('today');
        //近七天的开始日期
        $start_time = $today - 86400 * 7;
        //近七天的结束日期
        $end_time = $today - 1;

        $user_count = Db::name('shop_stat')
            ->where([['create_time', 'between', [$start_time, $end_time]],['shop_id', '=', $shop_id]])
            ->count('id');
        //当前时间戳
        $start_t = time();
        //echarts图表数据
        $echarts_add = [];
        $dates = [];
        for ($i = 7; $i >= 1; $i--) {
            $where_start = strtotime("- " . $i . "day", $start_t);
            $dates[] = date('m-d', $where_start);
            $start_now = strtotime(date('Y-m-d', $where_start));
            $end_now = strtotime(date('Y-m-d 23:59:59', $where_start));

            $add = Db::name('shop_stat')
                ->where([['create_time', 'between', [$start_now, $end_now]],['shop_id', '=', $shop_id]])
                ->count('id');
            $echarts_add[] = $add;
        }

        return [
            'user_count'      => $user_count,
            'echarts_add'     => $echarts_add,
            'days'            => $dates,
        ];
    }
}