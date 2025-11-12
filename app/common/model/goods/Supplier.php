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
 * 供货商
 * Class Supplier
 * @package app\common\model
 */
class Supplier extends Models
{

    /**
     * Notes: 获取以id为键的名称
     * @author 段誉(2021/4/19 18:31)
     * @return array
     */
    public static function getNameColumn($shop_id = 0)
    {
        $condition[] = ['del', '=', 0];

        if ($shop_id > 0) {
            $condition[] = ['shop_id', '=', $shop_id];
        }

        $lists = self::where($condition)->column('id,name', 'id');

        return empty($lists) ? [] : $lists;
    }

}
