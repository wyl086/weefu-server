<?php


namespace app\common\model\community;


use app\common\basics\Models;
use app\common\model\user\User;

/**
 * 种草社区点赞
 * Class CommunityLike
 * @package app\common\model\community
 */
class CommunityLike extends Models
{

    /**
     * @notes 关联用户
     * @return \think\model\relation\HasOne
     * @author 段誉
     * @date 2022/5/10 19:03
     */
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id')
            ->bind(['nickname', 'avatar', 'sn']);
    }


}