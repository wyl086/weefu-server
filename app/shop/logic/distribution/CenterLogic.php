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
// +------------------------------------------------------------------------

namespace app\shop\logic\distribution;

use app\common\basics\Logic;
use app\common\model\distribution\DistributionOrderGoods;

class CenterLogic extends Logic
{
    /**
     * @notes 分销概况
     * @author Tab
     * @date 2021/9/3 15:55
     */
    public static function center($shopId)
    {
        // 佣金数据
        $earnings = self::earnings($shopId);
        // 排行榜
        $top = self::top($shopId);

        return [
            'earnings' => $earnings,
            'top' => $top,
        ];
    }

    /**
     * @notes 分销商品排行榜
     * @param $shopId
     * @return mixed
     * @author Tab
     * @date 2021/9/3 16:07
     */
    public static function top($shopId)
    {
        $field = [
            'sum(dog.money)' => 'total_money',
            'og.image' => 'goods_image',
            'og.goods_name',
        ];
        $where = [
            'dog.shop_id' => $shopId,
            'dog.status' => 2, // 已入账
        ];
        $top = DistributionOrderGoods::alias('dog')
            ->leftJoin('order_goods og', 'og.id = dog.order_goods_id')
            ->field($field)
            ->where($where)
            ->group('dog.money,og.image,og.goods_name')
            ->order('total_money', 'desc')
            ->limit(10)
            ->select()
            ->toArray();

        return $top;
    }

    /**
     * @notes 佣金数据
     * @param $shopId
     * @return array
     * @author Tab
     * @date 2021/9/3 16:13
     */
    public static function earnings($shopId)
    {
        // 累计已入账佣金
        $totalSuccess = DistributionOrderGoods::where([
            'shop_id' => $shopId,
            'status' => 2,
        ])->sum('money');
        // 今日已入账佣金
        $totalTodaySuccess = DistributionOrderGoods::where([
            'shop_id' => $shopId,
            'status' => 2,
        ])->whereDay('settlement_time')->sum('money');
        // 累计待结算佣金
        $totalWait = DistributionOrderGoods::where([
            'shop_id' => $shopId,
            'status' => 1,
        ])->sum('money');
        // 今日待结算佣金
        $totalTodayWait = DistributionOrderGoods::where([
            'shop_id' => $shopId,
            'status' => 1,
        ])->whereDay('create_time')->sum('money');

        return [
            'total_success' => $totalSuccess,
            'total_today_success' => $totalTodaySuccess,
            'total_wait' => $totalWait,
            'total_today_wait' => $totalTodayWait,
        ];
    }
}