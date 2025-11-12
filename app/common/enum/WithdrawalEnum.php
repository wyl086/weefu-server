<?php


namespace app\common\enum;


class WithdrawalEnum
{
    const APPLY_STATUS   = 0; //申请中
    const HANDLE_STATUS  = 1; //提现中
    const SUCCESS_STATUS = 2; //提现成功
    const ERROR_STATUS   = 3; //提现失败

    /**
     * @Notes: 获取提现状态文案
     * @Author: 张无忌
     * @param bool $from
     * @return array|mixed
     */
    public static function getStatusDesc($from = true){
        $desc = [
            self::APPLY_STATUS    => '待提现',
            self::HANDLE_STATUS   => '提现中',
            self::SUCCESS_STATUS  => '提现成功',
            self::ERROR_STATUS    => '提现失败',
        ];
        if(true === $from){
            return $desc;
        }
        return $desc[$from];
    }
}