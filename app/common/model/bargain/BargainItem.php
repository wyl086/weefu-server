<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\common\model\bargain;


use app\common\basics\Models;
use app\common\model\goods\GoodsItem;

/**
 * 砍价活动 商品SKU模型
 * Class BargainItem
 * @Author 张无忌
 * @package app\common\model
 */
class BargainItem extends Models
{
    /**
     * @notes 关联商品规格
     * @return \think\model\relation\HasOne
     * @author suny
     * @date 2021/7/13 6:40 下午
     */
    public function goodsItem()
    {

        return $this->hasOne(GoodsItem::class, 'id', 'item_id');
    }
}