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
 * 商品单位
 * Class GoodsUnit
 * @package app\common\model
 */
class GoodsUnit extends Models
{
    public static function getNameColumn()
    {
        $lists = self::where([
            'del' => 0,
        ])->column('id,name', 'id');

        return empty($lists) ? [] : $lists;
    }
}