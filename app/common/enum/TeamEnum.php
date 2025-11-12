<?php


namespace app\common\enum;


class TeamEnum
{
    /**
     * 团的状态
     */
    const TEAM_STATUS_START = 1; //进行中
    const TEAM_STATUS_STOP  = 0; //已停止

    /**
     * 审核状态
     */
    const TEAM_AUDIT_WAIT    = 0; //等待审核
    const TEAM_AUDIT_SUCCESS = 1; //审核通过
    const TEAM_AUDIT_REFUSE  = 2; //审核拒绝

    /**
     * 拼团状态
     */
    const TEAM_STATUS_CONDUCT = 0; //进行中
    const TEAM_STATUS_SUCCESS = 1; //拼团成功
    const TEAM_STATUS_FAIL    = 2; //拼团失败

    /**
     * @Notes: 获取团活动状态
     * @Author: 张无忌
     * @param $type
     * @return array|mixed|string
     */
    public static function getTeamStatusDesc($type)
    {
        $desc = [
            self::TEAM_STATUS_START => '进行中',
            self::TEAM_STATUS_STOP  => '已停止',
        ];

        if ($type === true){
            return $desc;
        }
        return $desc[$type] ?? '';
    }

    /**
     * @Notes: 审核状态
     * @Author: 张无忌
     * @param $type
     * @return array|mixed|string
     */
    public static function getTeamAuditDesc($type)
    {
        $desc = [
            self::TEAM_AUDIT_WAIT    => '待审核',
            self::TEAM_AUDIT_SUCCESS => '审核通过',
            self::TEAM_AUDIT_REFUSE  => '审核拒绝',
        ];

        if ($type === true){
            return $desc;
        }
        return $desc[$type] ?? '';
    }
    /**
     * @Notes: 拼团状态
     * @Author: 张无忌
     * @param $type
     * @return array|mixed|string
     */
    public static function getStatusDesc($type)
    {
        $desc = [
            self::TEAM_STATUS_CONDUCT => '拼购中',
            self::TEAM_STATUS_SUCCESS => '拼团成功',
            self::TEAM_STATUS_FAIL    => '拼团失败',
        ];

        if ($type === true){
            return $desc;
        }
        return $desc[$type] ?? '';
    }

}