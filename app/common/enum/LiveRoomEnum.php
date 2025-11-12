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

class LiveRoomEnum
{
    // 审核状态
    const AUDIT_STATUS_WAIT = 0;
    const AUDIT_STATUS_SUCCESS = 1;
    const AUDIT_STATUS_FAIL = 2;


    // 直播间状态
    const LIVE_STATUS_WAIT = 102;
    const LIVE_STATUS_ING = 101;
    const LIVE_STATUS_END = 103;
    const LIVE_STATUS_DISABLE = 104;
    const LIVE_STATUS_STOP = 105;
    const LIVE_STATUS_ERROR = 106;
    const LIVE_STATUS_EXPIRE = 107;


    // 审核状态
    const AUDIT_STATUS = [
        self::AUDIT_STATUS_WAIT,
        self::AUDIT_STATUS_SUCCESS,
        self::AUDIT_STATUS_FAIL,
    ];


    // 直播间状态
    const LIVE_STATUS = [
        self::LIVE_STATUS_WAIT,
        self::LIVE_STATUS_ING,
        self::LIVE_STATUS_END,
        self::LIVE_STATUS_DISABLE,
        self::LIVE_STATUS_STOP,
        self::LIVE_STATUS_ERROR,
        self::LIVE_STATUS_EXPIRE,
    ];

    /**
     * @notes 审核状态描述
     * @param bool $from
     * @return string|string[]
     * @author 段誉
     * @date 2023/2/15 18:49
     */
    public static function getAuditStatusDesc($from = true)
    {
        $desc = [
            self::AUDIT_STATUS_WAIT => '待审核',
            self::AUDIT_STATUS_SUCCESS => '审核通过',
            self::AUDIT_STATUS_FAIL => '审核未通过',
        ];
        if (true === $from) {
            return $desc;
        }
        return $desc[$from];
    }


    /**
     * @notes 获取直播间状态描述
     * @param bool $from
     * @return string|string[]
     * @author 段誉
     * @date 2023/2/15 18:59
     */
    public static function getLiveStatusDesc($from = true)
    {
        $desc = [
            self::LIVE_STATUS_WAIT => '未开始',
            self::LIVE_STATUS_ING => '直播中',
            self::LIVE_STATUS_END => '已结束',
            self::LIVE_STATUS_DISABLE => '禁播',
            self::LIVE_STATUS_STOP => '暂停',
            self::LIVE_STATUS_ERROR => '异常',
            self::LIVE_STATUS_EXPIRE => '已过期',
        ];
        if (true === $from) {
            return $desc;
        }
        return $desc[$from];
    }

}