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
 * 平台商品分类
 * Class GoodsBrand
 * @package app\common\model\goods
 */
class GoodsCategory extends Models
{
  /**
   * 子分类
   */
  public function sons()
  {
    return $this->hasMany(self::class, 'pid', 'id')->where(['del' => 0]);
  }
}