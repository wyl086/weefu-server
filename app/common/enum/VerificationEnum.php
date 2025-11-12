<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\common\enum;

/**
 * 自提核销
 * Class VerificationEnum
 * @package app\common\enum
 */
class VerificationEnum
{
    //操作人类型
    const TYPE_SYSTEM = 0;//系统
    const TYPE_SHOP = 1;//管理员

    //操作人类型
    public static function getVerificationScene($status = true)
    {
        $desc = [
            self::TYPE_SYSTEM => '系统',
            self::TYPE_SHOP => '商家',
        ];
        if ($status === true) {
            return $desc;
        }
        return $desc[$status] ?? '未知';
    }
}