<?php
namespace app\admin\logic\distribution;

use app\common\basics\Logic;
use app\common\model\distribution\Distribution;
use app\common\model\distribution\DistributionOrderGoods;
use app\common\server\UrlServer;

class CenterLogic extends Logic
{
    /**
     * @notes 数据概览
     * @return array
     * @author Tab
     * @date 2021/9/6 14:40
     */
    public static function center()
    {
        $data = [
            'earnings' => self::earnings(),
            'members' => self::members(),
            'topGoods' => self::topGoods(),
            'topMembers' => self::topMembers(),
        ];

        return $data;
    }

    /**
     * @notes 佣金数据
     * @return array
     * @author Tab
     * @date 2021/9/6 14:46
     */
    public static function earnings()
    {
        // 累计已入账佣金
        $totalSuccess = DistributionOrderGoods::where([
            'status' => 2,
        ])->sum('money');
        // 今日已入账佣金
        $totalTodaySuccess = DistributionOrderGoods::where([
            'status' => 2,
        ])->whereDay('settlement_time')->sum('money');
        // 累计待结算佣金
        $totalWait = DistributionOrderGoods::where([
            'status' => 1,
        ])->sum('money');
        // 今日待结算佣金
        $totalTodayWait = DistributionOrderGoods::where([
            'status' => 1,
        ])->whereDay('create_time')->sum('money');

        return [
            'total_success' => $totalSuccess,
            'total_today_success' => $totalTodaySuccess,
            'total_wait' => $totalWait,
            'total_today_wait' => $totalTodayWait,
        ];
    }

    /**
     * @notes 分销会员数据
     * @author Tab
     * @date 2021/9/6 14:57
     */
    public static function members()
    {
        $members = Distribution::where('is_distribution', 1)->count();
        $users = Distribution::count();
        $proportion = 0;
        if ($users) {
            $proportion = round(($members / $users), 2) * 100;
        }


        return [
            'members' => $members,
            'proportion' => $proportion,
        ];
    }

    /**
     * @notes 分销商品排行榜
     * @author Tab
     * @date 2021/9/6 14:59
     */
    public static function topGoods()
    {
        $field = [
            'sum(dog.money)' => 'total_money',
            'og.image' => 'goods_image',
            'og.goods_name',
        ];
        $where = [
            'dog.status' => 2, // 已入账
        ];
        $topGoods = DistributionOrderGoods::alias('dog')
            ->leftJoin('order_goods og', 'og.id = dog.order_goods_id')
            ->field($field)
            ->where($where)
            ->group('dog.money,og.image,og.goods_name')
            ->order('total_money', 'desc')
            ->limit(10)
            ->select()
            ->toArray();

        return $topGoods;
    }

    /**
     * @notes 分销会员排行榜
     * @return mixed
     * @author Tab
     * @date 2021/9/6 15:01
     */
    public static function topMembers()
    {
        $field = [
            'sum(dog.money)' => 'total_money',
            'u.avatar',
            'u.nickname',
        ];
        $where = [
            'dog.status' => 2, // 已入账
        ];
        $topMembers = DistributionOrderGoods::alias('dog')
            ->leftJoin('user u', 'u.id = dog.user_id')
            ->field($field)
            ->where($where)
            ->group('dog.money,u.avatar,u.nickname')
            ->order('total_money', 'desc')
            ->limit(10)
            ->select()
            ->toArray();

        foreach($topMembers as &$item) {
            $item['avatar'] = empty($item['avatar']) ? '' : UrlServer::getFileUrl($item['avatar']);
        }

        return $topMembers;
    }
}