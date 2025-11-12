<?php
namespace app\api\logic;

use app\common\basics\Logic;
use app\common\model\shop\ShopGoodsCategory;

class ShopGoodsCategoryLogic extends Logic
{
    /**
     * 店铺分类
     */
    public static function getShopGoodsCategory($shopId)
    {
        $where = [
            'del' => 0,
            'shop_id' => $shopId,
            'is_show' => 1
        ];
        $order = [
            'sort' => 'asc',
            'id' => 'desc'
        ];
        $data = ShopGoodsCategory::field('id,name')
            ->where($where)->order($order)->select()->toArray();

        return $data;
    }
}