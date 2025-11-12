<?php
namespace app\admin\logic\distribution;

use app\common\basics\Logic;
use app\common\model\distribution\DistributionLevel;
use app\common\model\distribution\DistributionOrderGoods;
use app\common\model\user\User;
use app\common\server\UrlServer;

class DistributionOrderLogic extends Logic
{
    /**
     * @notes 分销订单列表
     * @param $params
     * @return int[]
     * @author Tab
     * @date 2021/9/3 14:53
     */
    public static function lists($params)
    {
        $where= [];
        // 订单信息
        if (isset($params['order_keyword']) && !empty($params['order_keyword'])) {
            $where[] = ['o.order_sn', '=', $params['order_keyword']];
        }
        // 商品名称
        if (isset($params['goods_keyword']) && !empty($params['goods_keyword'])) {
            $where[] = ['og.goods_name', 'like', '%'.$params['goods_keyword'].'%'];
        }
        // 分销会员
        if (isset($params['distribution_keyword']) && !empty($params['distribution_keyword'])) {
            $where[] = ['u.sn|u.nickname', 'like', '%'.$params['distribution_keyword'].'%'];
        }
        // 佣金状态
        if (isset($params['status']) && !empty($params['status'])) {
            $where[] = ['dog.status', '=', $params['status']];
        }

        $field = [
            'o.id' => 'order_id',
            'o.order_sn',
            'o.create_time' => 'order_create_time',
            'o.user_id' => 'order_user_id',
            'u.id' => 'distribution_user_id',
            'u.avatar' => 'distribution_avatar',
            'u.sn' => 'distribution_sn',
            'u.nickname' => 'distribution_nickname',
            'og.image' => 'goods_image',
            'og.goods_name' => 'goods_name',
            'og.spec_value' => 'spec_value',
            'og.goods_num' => 'goods_num',
            'og.total_pay_price' => 'total_pay_price',
            'dog.level_id',
            'dog.level',
            'dog.ratio',
            'dog.money',
            'dog.status' => 'status_desc',
            'dog.settlement_time',
            's.id' => 'shop_id',
            's.name' => 'shop_name',
            's.logo' => 'shop_logo',
        ];

        $lists = DistributionOrderGoods::alias('dog')
            ->leftJoin('order o', 'o.id = dog.order_id')
            ->leftJoin('user u', 'u.id = dog.user_id')
            ->leftJoin('order_goods og', 'og.id = dog.order_goods_id')
            ->leftJoin('distribution_level dl', 'dl.id = dog.level_id')
            ->leftJoin('shop s', 's.id = dog.shop_id')
            ->field($field)
            ->where($where)
            ->order('dog.id', 'desc')
            ->page($params['page'], $params['limit'])
            ->select()
            ->toArray();

        $count = DistributionOrderGoods::alias('dog')
            ->leftJoin('order o', 'o.id = dog.order_id')
            ->leftJoin('user u', 'u.id = dog.user_id')
            ->leftJoin('order_goods og', 'og.id = dog.order_goods_id')
            ->leftJoin('distribution_level dl', 'dl.id = dog.level_id')
            ->leftJoin('shop s', 's.id = dog.shop_id')
            ->field($field)
            ->where($where)
            ->count();

        foreach($lists as &$item) {
            $item['order_create_time'] = date('Y-m-d H:i:s', $item['order_create_time']);
            $item['user_info'] = User::getUserInfo($item['order_user_id']);
            if ($item['user_info'] == '系统') {
                // 用户不存在(已被删除的情况)
                $item['user_info'] = [
                    'avatar' => '',
                    'nickname' => '-',
                    'sn' => '-',
                ];
            }
            $item['distribution_avatar'] = empty($item['distribution_avatar']) ? '' : UrlServer::getFileUrl($item['distribution_avatar']);
            $item['user_info']['avatar'] = empty($item['user_info']['avatar']) ? '' : UrlServer::getFileUrl($item['user_info']['avatar']);
            $item['level_name'] = DistributionLevel::getLevelName($item['level_id']);
            $item['shop_logo'] = empty($item['shop_logo']) ? '' : UrlServer::getFileUrl($item['shop_logo']);
            $item['goods_image'] = empty($item['goods_image']) ? '' : UrlServer::getFileUrl($item['goods_image']);
        }

        return [
            'count' => $count,
            'lists' => $lists
        ];
    }
}