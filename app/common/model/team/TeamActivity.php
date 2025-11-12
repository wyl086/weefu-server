<?php


namespace app\common\model\team;


use app\common\basics\Models;

/**
 * 拼团活动模型
 * Class TeamActivity
 * @package app\common\model\team
 */
class TeamActivity extends Models
{
    /**
     * @Notes: 关联商品模型
     * @Author: 张无忌
     */
    public function goods()
    {
        return $this->hasOne('app\common\model\goods\Goods', 'id', 'goods_id')
            ->field(['id,name,image,max_price,min_price,market_price,stock']);
    }

    /**
     * @Notes: 关联拼团商品模型
     * @Author: 张无忌
     */
    public function teamGoods()
    {
        return $this->hasMany('TeamGoods', 'team_id', 'id')
            ->alias('TG')
            ->field(['TG.*', 'GI.spec_value_str,GI.price,GI.market_price,GI.stock'])
            ->join('goods_item GI', 'TG.item_id = GI.id');
    }
}