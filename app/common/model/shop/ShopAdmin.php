<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\common\model\shop;


use app\common\basics\Models;

/**
 * 商家管理员
 * Class ShopAdmin
 * @package app\shop\model
 */
class ShopAdmin extends Models
{
    /**
     * Notes: 登录时间
     * @param $value
     * @author 段誉(2021/4/13 14:27)
     * @return false|string
     */
    public function getLoginTimeAttr($value)
    {
        return empty($value) ?  '' : date('Y-m-d H:i:s', $value);
    }

}