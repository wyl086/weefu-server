<?php


namespace app\api\logic;


use app\common\basics\Logic;
use app\common\model\community\CommunityArticle;
use app\common\model\community\CommunityFollow;
use app\common\model\community\CommunityUser;
use app\common\model\user\User;
use app\common\server\ConfigServer;
use app\common\server\UrlServer;


/**
 * 社区用户相关
 * Class CommunityArticleLogic
 * @package app\api\logic
 */
class CommunityUserLogic extends Logic
{

    /**
     * @notes 获取个人中心信息
     * @param $user_id
     * @return mixed
     * @author 段誉
     * @date 2022/5/5 18:00
     */
    public static function getCenterInfo($user_id, $get)
    {
        // 是否获取当前登录者信息
        $is_self = true;
        // 当前登录者是否关注需获取信息的$get['user_id']
        $is_follow = false;
        if (!empty($get['user_id']) && $get['user_id'] != $user_id) {
            // 校验是否关注
            $relation = CommunityFollow::where([
                'user_id' => $user_id,
                'follow_id' => $get['user_id'],
                'status' => 1
            ])->findOrEmpty();
            if (!$relation->isEmpty()) {
                $is_follow = true;
            }

            $user_id = $get['user_id'];
            $is_self = false;
        }

        $user = User::field(['id', 'sn', 'nickname', 'avatar'])->findOrEmpty($user_id)->toArray();
        $community = CommunityUser::getUserInfo($user_id);

        $user['avatar'] = !empty($user['avatar']) ? UrlServer::getFileUrl($user['avatar']) : '';
        $user['image'] = !empty($community['image']) ? UrlServer::getFileUrl($community['image']) : '';
        $user['signature'] = $community['signature'];

        $user['follow'] = CommunityFollow::where(['user_id' => $user_id, 'status' => 1])->count();
        $user['fans'] = CommunityFollow::where(['follow_id' => $user_id, 'status' => 1])->count();
        $user['like'] = CommunityArticle::where(['user_id' => $user_id])->sum('like');

        // 是否为当前登录者
        $user['is_self'] = $is_self ? 1 : 0;
        // 是否关注
        $user['is_follow'] = $is_follow ? 1 : 0;

        return $user;
    }


    /**
     * @notes 获取设置
     * @param $user_id
     * @return array|\think\Model
     * @author 段誉
     * @date 2022/5/5 18:39
     */
    public static function getSetting($user_id)
    {
        $user = CommunityUser::getUserInfo($user_id);

        if (empty($user['image'])) {
            $user['image'] = ConfigServer::get('community', 'user_bg');
        }

        return $user->toArray();
    }



    /**
     * @notes 个人设置
     * @param $user_id
     * @param $post
     * @return CommunityUser|false
     * @author 段誉
     * @date 2022/5/5 18:34
     */
    public static function setSetting($user_id, $post)
    {
        $user = CommunityUser::getUserInfo($user_id);
        if (empty($user)) {
            self::$error = '系统错误';
            return false;
        }
        return CommunityUser::where(['user_id' => $user_id])->update([
            'signature' => $post['signature'] ?? '',
            'image' => $post['image'] ?? '',
        ]);
    }




}