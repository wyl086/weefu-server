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
use app\common\model\distribution\DistributionGoods;
use app\common\model\distribution\DistributionLevel;
use app\common\model\goods\Goods;
use app\common\model\shop\Shop;
use app\common\server\ConfigServer;
use think\facade\Db;

class GoodsLogic extends Logic
{
    /**
     * @notes 分销商品列表
     * @param $params
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/9/1 18:02
     */
    public static function lists($params)
    {
        $where = [
            ['del', '<>', '1'],
            ['shop_id', '=', $params['shop_id']]
        ];
        // 商品信息
        if (isset($params['keyword']) && !empty($params['keyword'])) {
            $where[] = ['name|code', 'like', '%'. $params['keyword']. '%'];
        }
        // 平台商品分类
        if (isset($params['platform_cate_id']) && $params['platform_cate_id'] != 'all') {
            $where[] = ['first_cate_id|second_cate_id|third_cate_id', '=', $params['platform_cate_id']];
        }
        // 商家商品分类
        if (isset($params['shop_cate_id']) && $params['shop_cate_id'] != 'all') {
            $where[] = ['shop_cate_id', '=', $params['shop_cate_id']];
        }

        $field = [
            'id',
            'code',
            'name',
            'image',
            'max_price',
            'min_price',
            'id' => 'distribution_flag',
        ];
        $lists = Goods::field($field)
            ->where($where)
            ->withSearch('is_distribution', $params)
            ->page($params['page'], $params['limit'])
            ->order('id', 'desc')
            ->select()
            ->toArray();
        $count = Goods::field($field)
            ->where($where)
            ->withSearch('is_distribution', $params)
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
                    'first_ratio' => $record['first_ratio'] ?? 0,
                    'second_ratio' => $record['second_ratio'] ?? 0,
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
     * @notes 设置佣金
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/9/1 20:52
     */
    public static function set($params)
    {
        Db::startTrans();
        try {
            if (false === self::ableDistribution($params['shop_id'])) {
                throw new \Exception('平台已关闭分销功能');
            }

            switch($params['rule']) {
                // 根据分销会员等级比例分佣
                case 1:
                    self::setRuleOne($params);
                    break;

                // 单独设置
                case 2:
                    self::setRuleTwo($params);
                    break;
            }

            Db::commit();
            return true;
        }catch(\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 设置佣金 - 根据分销会员等级比例分佣
     * @param $params
     * @author Tab
     * @date 2021/9/1 21:04
     */
    public static function setRuleOne($params)
    {
        // 删除旧数据
        $deleteIds = DistributionGoods::where('goods_id', $params['id'])->column('id');
        DistributionGoods::destroy($deleteIds);

        // 生成新数据
        $data = [
            'shop_id' => $params['shop_id'],
            'goods_id' => $params['id'],
            'is_distribution' => $params['is_distribution'],
            'rule' => $params['rule'],
        ];
        DistributionGoods::create($data);
    }

    /**
     * @notes 设置佣金 - 单独自定义
     * @param $params
     * @throws \Exception
     * @author Tab
     * @date 2021/9/1 21:04
     */
    public static function setRuleTwo($params)
    {
        // 删除旧数据
        $deleteIds = DistributionGoods::where('goods_id', $params['id'])->column('id');
        DistributionGoods::destroy($deleteIds);

        // 生成新数据
        $data= [];
        foreach($params['first_ratio'] as $k => $v) {
            if ($params['first_ratio'][$k] < 0 || $params['second_ratio'][$k] < 0) {
                throw new \Exception('分销比例不能小于0');
            }
            $temp = [
                'shop_id' => $params['shop_id'],
                'goods_id' => $params['id'],
                'item_id' => $params['items'][$k],
                'level_id' => $params['levels'][$k],
                'first_ratio' => !empty($params['first_ratio'][$k]) ? round($params['first_ratio'][$k], 2) : 0,
                'second_ratio' => !empty($params['second_ratio'][$k]) ? round($params['second_ratio'][$k], 2) : 0,
                'is_distribution' => $params['is_distribution'],
                'rule' => $params['rule'],
            ];
            $data[] = $temp;
        }
        (new DistributionGoods())->saveAll($data);
    }

    /**
     * @notes 参与分销/取消分销
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/9/2 10:11
     */
    public static function isDistribution($params)
    {
        Db::startTrans();
        try{
            if (false === self::ableDistribution($params['shop_id'])) {
                throw new \Exception('平台已关闭分销功能');
            }

            $existedIds = DistributionGoods::distinct(true)->column('goods_id');
            $updateIds = array_intersect($params['ids'], $existedIds);
            $insertIds = array_diff($params['ids'], $existedIds);

            // 有分销数据，直接修改
            DistributionGoods::where('goods_id', 'in', $updateIds)->update(['is_distribution' => $params['is_distribution']]);

            // 无分销数据，新增
            $insertData = [];
            foreach($insertIds as $id) {
                $item['goods_id'] = $id;
                $item['is_distribution'] = $params['is_distribution'];
                $item['rule'] = 1;
                $item['shop_id'] = $params['shop_id'];
                $insertData[] = $item;
            }

            (new DistributionGoods())->saveAll($insertData);

            Db::commit();
            return true;
        }catch(\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 能否参与分销
     * @param $shopId
     * @return bool
     * @author 段誉
     * @date 2022/8/1 16:04
     */
    public static function ableDistribution($shopId)
    {
        // 平台分销功能开关
        $open = ConfigServer::get('distribution', 'is_open', 0);
        // 商家信息-获取商家是否被禁用分销功能(is_distribution)
        $shop = Shop::findOrEmpty($shopId)->toArray();

        if (!$open || !$shop['is_distribution']) {
            return false;
        }
        return true;
    }

}