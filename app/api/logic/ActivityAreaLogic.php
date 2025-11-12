<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\api\logic;

use app\common\model\goods\Goods;
use app\common\model\activity_area\ActivityAreaGoods;
use app\common\basics\Logic;
use think\Db;

/**
 * Class ActivityAreaLogic
 * @package app\api\logic
 */
class ActivityAreaLogic extends Logic
{

    /**
     * @notes 活动专区商品列表
     * @param $id
     * @param $page
     * @param $size
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/13 6:04 下午
     */
    public static function activityGoodsList($id, $page, $size)
    {

        $where[] = ['AG.del', '=', 0];
        $where[] = ['G.del', '=', 0];
        $where[] = ['G.status', '=', 1];
        $where[] = ['AG.audit_status', '=', 1];
        $where[] = ['AG.activity_area_id', '=', $id];

        $goods = new ActivityAreaGoods();
        $count = $goods->alias('AG')
            ->join('goods G', 'G.id = AG.goods_id')
            ->where($where)
            ->count();

        $list = $goods->alias('AG')
            ->join('goods G', 'G.id = AG.goods_id')
            ->where($where)
            ->field('AG.id as agid,G.id,G.name,G.image,G.min_price as price,
            (G.clicks + G.clicks_virtual) as views,G.market_price,AG.activity_area_id,(G.sales_actual + G.sales_virtual) as sales_total')
            ->page($page,$size)
            ->select();

        $more = is_more($count, $page, $size);  //是否有下一页

        $data = [
            'list' => $list,
            'page_no' => $page,
            'page_size' => $size,
            'count' => $count,
            'more' => $more
        ];

        return $data;
    }

}