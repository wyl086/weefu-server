<?php

namespace app\api\logic;


use app\common\basics\Logic;
use app\common\enum\IntegralGoodsEnum;
use app\common\model\integral\IntegralGoods;
use app\common\model\user\User;
use app\common\server\UrlServer;


/**
 * 积分商品逻辑
 * Class IntegralGoodsLogic
 * @package app\api\logic
 */
class IntegralGoodsLogic extends Logic
{

    /**
     * @notes 积分商品列表
     * @param $user_id
     * @param $get
     * @param $page
     * @param $size
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/2/28 11:53
     */
    public static function lists($user_id, $get, $page, $size)
    {
        $order = [];
        // 所需积分排序
        if (!empty($get['sort_by_integral'])) {
            $order['need_integral'] = $get['sort_by_integral'];
        }
        // 兑换量排序
        if (!empty($get['sort_by_sales'])) {
            $order['sales'] = $get['sort_by_sales'];
        }
        // 最新排序
        if (!empty($get['sort_by_new'])) {
            $order['id'] = $get['sort_by_new'];
        }

        $where = [
            ['del', '=', IntegralGoodsEnum::DEL_NORMAL],  // 未删除
            ['status', '=', IntegralGoodsEnum::STATUS_SHELVES] // 上架中
        ];

        $field = ['id', 'image', 'name', 'need_integral', 'need_money', 'exchange_way'];

        $count = IntegralGoods::where($where)->count('id');
        $lists = IntegralGoods::where($where)->order(['sort' => 'desc','id'=>'desc'])->field($field)
            ->order($order)->page($page, $size)->select();

        foreach ($lists as $item) {
            $item['image'] = UrlServer::getFileUrl($item['image']);
        }

        // 当前积分
        $integral = User::field('user_integral')->findOrEmpty($user_id);

        return [
            'integral' => $integral['user_integral'] ?? 0,
            'goods' => [
                'page_no' => $page,
                'page_size' => $size,
                'count' => $count,
                'more' => is_more($count, $page, $size),
                'lists' => $lists
            ],
        ];
    }


    /**
     * @notes 商品详情
     * @param $goods_id
     * @return array
     * @author 段誉
     * @date 2022/2/28 15:14
     */
    public static function detail($goods_id)
    {
        $detail = IntegralGoods::where(['id' => $goods_id])->findOrEmpty();
        $detail['image'] = UrlServer::getFileUrl($detail['image']);
        return $detail->toArray();
    }

}
