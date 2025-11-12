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
 * 种草社区评论枚举
 * Class CommunityCommentEnum
 * @package app\common\enum
 */
class CommunityCommentEnum
{
    const STATUS_WAIT = 0;  //待审核
    const STATUS_SUCCESS = 1;  //审核通过
    const STATUS_REFUSE = 2;  //审核拒绝


    /**
     * @notes 获取审核状态描述
     * @param bool $status
     * @return string|string[]
     * @author 段誉
     * @date 2022/5/6 10:41
     */
    public static function getStatusDesc($status = true)
    {
        $desc = [
            self::STATUS_WAIT => '审核中',
            self::STATUS_SUCCESS => '审核通过',
            self::STATUS_REFUSE => '审核拒绝',
        ];
        if (true === $status) {
            return $desc;
        }
        return $desc[$status];
    }

}