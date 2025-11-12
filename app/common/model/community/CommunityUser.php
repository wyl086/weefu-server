<?php


namespace app\common\model\community;


use app\common\basics\Models;

/**
 * 种草社区用户信息
 * Class CommunityUser
 * @package app\common\model\community
 */
class CommunityUser extends Models
{

    /**
     * @notes 获取用户信息
     * @param $user_id
     * @return array|\think\Model
     * @author 段誉
     * @date 2022/5/5 18:00
     */
    public static function getUserInfo($user_id)
    {
        $user = self::where(['user_id' => $user_id])->findOrEmpty();

        if ($user->isEmpty()) {
            $user = self::create([
                'user_id' => $user_id
            ]);
        }

        return $user;
    }

}