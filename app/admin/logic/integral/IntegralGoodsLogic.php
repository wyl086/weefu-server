<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\admin\logic\integral;

use app\common\basics\Logic;
use app\common\enum\IntegralGoodsEnum;
use app\common\model\integral\IntegralGoods;
use app\common\server\UrlServer;


/**
 * 积分商品
 * Class IntegralGoodsLogic
 * @package app\admin\logic\kefu
 */
class IntegralGoodsLogic extends Logic
{

    public static function getLists($get)
    {
        $where = [
            ['del', '=', 0]
        ];
        if (isset($get['type']) && is_numeric($get['type'])) {
            $where[] = ['type', '=', $get['type']];
        }
        if (isset($get['status']) && is_numeric($get['status'])) {
            $where[] = ['status', '=', $get['status']];
        }
        if (isset($get['name']) && $get['name'] != '') {
            $where[] = ['name', 'like', '%' . $get['name'] . '%'];
        }

        $model = new IntegralGoods();
        $lists = $model->field(true)
            ->where($where)
            ->order(['sort' => 'desc','id'=>'desc'])
            ->paginate([
                'page' => $get['page'],
                'list_rows' => $get['limit'],
                'var_page' => 'page'
            ])
            ->toArray();

        foreach ($lists['data'] as &$item) {
            $item['need'] = $item['need_integral'] . '积分+' . $item['need_money'] . '元';
            if ($item['type'] == IntegralGoodsEnum::TYPE_BALANCE || $item['exchange_way'] == IntegralGoodsEnum::EXCHANGE_WAY_INTEGRAL) {
                $item['need'] = $item['need_integral'] . '积分';
            }
            $item['type'] = IntegralGoodsEnum::getTypeDesc($item['type']);
            $item['market_price'] = empty($item['market_price']) ? '-' : '￥' . $item['market_price'];
        }

        return ['count' => $lists['total'], 'lists' => $lists['data']];
    }


    /**
     * @notes 添加商品
     * @param $post
     * @return bool
     * @author 段誉
     * @date 2022/2/25 18:27
     */
    public static function add($post)
    {
        try {
            $storageUrl = UrlServer::getFileUrl();
            $content = str_replace($storageUrl, '/', $post['content']);
            IntegralGoods::create([
                'name' => $post['name'],
                'code' => $post['code'] ?? '',
                'image' => $post['image'],
                'type' => $post['type'],
                'market_price' => $post['market_price'] ?? '',
                'stock' => $post['stock'],
                'status' => $post['status'],
                'exchange_way' => $post['exchange_way'] ?? 1,
                'need_integral' => $post['need_integral'],
                'need_money' => $post['need_money'] ?? 0,
                'delivery_way' => $post['delivery_way'] ?? 0,
                'balance' => $post['balance'] ?? 0,
                'express_type' => $post['express_type'] ?? 0,
                'express_money' => $post['express_money'] ?? 0,
                'content' => $content,
                'sort' => $post['sort'] ?? 0,
            ]);
            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 编辑积分商品
     * @param $post
     * @return bool
     * @author 段誉
     * @date 2022/3/1 15:57
     */
    public static function edit($post)
    {
        try {
            $storageUrl = UrlServer::getFileUrl();
            $content = str_replace($storageUrl, '/', $post['content']);

            // 包邮或无需快递,运费重置为0
            if ($post['delivery_way'] == IntegralGoodsEnum::DELIVERY_NO_EXPRESS
                || $post['express_type'] == IntegralGoodsEnum::EXPRESS_TYPE_FREE) {
                $post['express_money'] = 0;
                $post['express_type'] = IntegralGoodsEnum::EXPRESS_TYPE_FREE;
            }

            IntegralGoods::update([
                'id'=>$post['id'],
                'name' => $post['name'],
                'code' => $post['code'] ?? '',
                'image' => $post['image'],
                'market_price' => $post['market_price'] ?? '',
                'stock' => $post['stock'],
                'status' => $post['status'],
                'exchange_way' => $post['exchange_way'] ?? 1,
                'need_integral' => $post['need_integral'],
                'need_money' => $post['need_money'] ?? 0,
                'delivery_way' => $post['delivery_way'] ?? 0,
                'balance' => $post['balance'] ?? 0,
                'express_type' => $post['express_type'],
                'express_money' => $post['express_money'],
                'content' => $content,
                'sort' => $post['sort'] ?? 0,
            ]);
            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }


    public static function detail($id)
    {
        $detail = IntegralGoods::where(['id' => $id])->findOrEmpty();
        return $detail;
    }


    /**
     * @notes 切换状态
     * @param $post
     * @return IntegralGoods
     * @author 段誉
     * @date 2022/2/25 18:25
     */
    public static function switchStatus($post)
    {
         return IntegralGoods::update([
             'status' => $post['status'],
             'id' => $post['id']
         ]);
    }


    /**
     * @notes 删除商品
     * @param $post
     * @return IntegralGoods
     * @author 段誉
     * @date 2022/2/25 18:26
     */
    public static function del($post)
    {
        return IntegralGoods::update([
            'id' => $post['id'],
            'del' => 1,
            'update_time' => time()
        ]);
    }


}