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

/**
 * 管理员模型
 * Class Admin
 * @Author FZR
 * @package app\common\model
 */
class Admin extends Models
{

    /**
     * Notes: 获取器-格式化登录时间
     * @param $value
     * @author FZR(2021/1/28 16:30)
     * @return false|string
     */
    public function getLoginTimeAttr($value)
    {
        return empty($value) ?  '' : date('Y-m-d H:i:s', $value);
    }

}