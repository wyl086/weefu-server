<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\common\model;


use app\common\basics\Models;
use app\common\model\goods\Goods;
use app\common\model\goods\GoodsItem;
use app\common\model\shop\Shop;

/**
 * 购物车
 * Class Cart
 * @package app\common\model
 */
class Cart extends Models
{

    public function goods()
    {
        return $this->hasOne(Goods::class, 'id', 'goods_id');
    }

    public function goodsItem()
    {
        return $this->hasOne(GoodsItem::class, 'id', 'item_id');
    }

    public function shop()
    {
        return $this->hasOne(Shop::class, 'id', 'shop_id');
    }

}