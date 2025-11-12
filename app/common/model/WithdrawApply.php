<?php

namespace app\common\model;

use app\common\basics\Models;
use app\common\model\user\User;

/**
 * Class WithdrawApply
 * @package app\common\model
 */
class WithdrawApply extends Models
{
    //提现状态
    const STATUS_WAIT = 1; // 待提现
    const STATUS_ING = 2; //  提现中
    const STATUS_SUCCESS = 3; // 提现成功
    const STATUS_FAIL = 4; //提现失败

    /**
     * @notes 管理User模型
     * @return \think\model\relation\HasOne
     * @author suny
     * @date 2021/7/13 6:45 下午
     */
    public function user()
    {

        return $this->hasOne(User::class, 'id', 'user_id')
            ->field('id,sn,nickname,avatar');
    }
}