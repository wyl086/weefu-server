<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\common\model\goods;


use app\common\basics\Models;


/**
 * 商品规格
 * Class GoodsSpec
 * @package app\common\model\goods
 */
class GoodsSpec extends Models
{
    /**
     * 规格值 关联模型
     */
    public function specValue()
    {
        return $this->hasMany('GoodsSpecValue', 'spec_id', 'id');
    }
}