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

class Express extends Models
{
    const ZHONGTONG  = 'zhongtong';
    const SHENTONG   = 'shentong';


    /**
     * @notes 获取快递100编码
     * @param bool $from
     * @return string
     * @author 段誉
     * @date 2023/2/14 10:45
     */
    public static function getkuaidi100code($from = true)
    {
        $desc = [
//            self::ZHONGTONG         => 'ztoOpen',
            self::SHENTONG          => '44',
        ];
        return $desc[$from] ?? '';
    }
}