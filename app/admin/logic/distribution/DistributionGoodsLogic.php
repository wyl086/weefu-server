<?php
namespace app\admin\logic\distribution;

use app\common\basics\Logic;
use app\common\model\distribution\DistributionGoods;
use app\common\model\distribution\DistributionLevel;
use app\common\model\goods\Goods;

class DistributionGoodsLogic extends Logic
{
    /**
     * @notes 分销商品列表
     * @param $params
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/9/2 17:41
     */
    public static function lists($params)
    {
        $where = [
            ['del', '<>', '1'],
        ];
        // 商品信息
        if (isset($params['keyword']) && !empty($params['keyword'])) {
            $where[] = ['name|code', 'like', '%'. $params['keyword']. '%'];
        }
        // 平台商品分类
        if (isset($params['platform_cate_id']) && $params['platform_cate_id'] != 'all') {
            $where[] = ['first_cate_id|second_cate_id|third_cate_id', '=', $params['platform_cate_id']];
        }

        $field = [
            'id',
            'code',
            'name',
            'image',
            'max_price',
            'min_price',
            'id' => 'distribution_flag',
            'shop_id',
        ];
        $lists = Goods::with(['Shop'])
            ->field($field)
            ->where($where)
            ->withSearch('is_distribution', ["is_distribution" => 1])
            ->page($params['page'], $params['limit'])
            ->order([
                'shop_id' => 'desc',
                'id' => 'desc'
            ])
            ->select()
            ->toArray();

        $count = Goods::field($field)
            ->where($where)
            ->withSearch('is_distribution', ["is_distribution" => 1])
            ->count();

        return [
            'count' => $count,
            'lists' => $lists
        ];
    }

    /**
     * @notes 商品详情
     * @param $params
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/9/1 19:59
     */
    public static function detail($params)
    {
        // 商品信息
        $goods = Goods::field('id,code,name')->with('goods_item')->findOrEmpty($params['id'])->toArray();
        // 分销等级信息
        $distributionLevelLists = DistributionLevel::order('weights', 'asc')->select()->toArray();
        // 商品分销信息
        $distributionGoods = DistributionGoods::where('goods_id', $params['id'])->select()->toArray();
        if(empty($distributionGoods)) {
            // 未参与分销
            $goods['is_distribution'] = 0;
            $goods['rule'] = 1;
            $ratio = self::formatLevel($distributionLevelLists, $goods);
        } else {
            $goods['is_distribution'] = $distributionGoods[0]['is_distribution'];
            $goods['rule'] = $distributionGoods[0]['rule'];
            if($distributionGoods[0]['rule'] == 1) {
                $ratio = self::formatLevel($distributionLevelLists, $goods);
            } else {
                $ratio = self::formatGoods($distributionLevelLists, $goods);
            }
        }

        return [
            'goods' => $goods,
            'ratio' => $ratio
        ];
    }

    /**
     * @notes 拼装分销等级佣金比例
     * @param $distributionLevelLists
     * @param $goods
     * @return array
     * @author Tab
     * @date 2021/9/1 19:44
     */
    public static function formatLevel($distributionLevelLists, $goods)
    {
        $ratio = [];
        foreach($distributionLevelLists as $level) {
            foreach($goods['goods_item'] as $item) {
                $temp = [
                    'level_id' => $level['id'],
                    'level_name' => $level['name'],
                    'first_ratio' => $level['first_ratio'],
                    'second_ratio' => $level['second_ratio'],
                    'goods_id' => $item['goods_id'],
                    'item_id' => $item['id'],
                    'spec_value_str' => $item['spec_value_str'],
                    'price' => $item['price']
                ];
                $ratio[] = $temp;
            }
        }
        return $ratio;
    }

    /**
     * @notes 拼装单独设置的佣金比例
     * @param $distributionLevelLists
     * @param $goods
     * @param $distributionGoods
     * @return array
     * @author Tab
     * @date 2021/9/2 9:28
     */
    public static function formatGoods($distributionLevelLists, $goods)
    {
        $ratio = [];
        foreach($distributionLevelLists as $level) {
            foreach($goods['goods_item'] as $item) {
                $record = DistributionGoods::where([
                    'level_id' => $level['id'],
                    'item_id' =>  $item['id'],
                ])->findOrEmpty()->toArray();
                $temp = [
                    'level_id' => $level['id'],
                    'level_name' => $level['name'],
                    'first_ratio' => $record['first_ratio'],
                    'second_ratio' => $record['second_ratio'],
                    'goods_id' => $item['goods_id'],
                    'item_id' => $item['id'],
                    'spec_value_str' => $item['spec_value_str'],
                    'price' => $item['price']
                ];
                $ratio[] = $temp;
            }
        }
        return $ratio;
    }
}