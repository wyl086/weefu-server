<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\common\logic;


use app\common\basics\Logic;
use app\common\cache\CommunityArticleCache;
use app\common\enum\CommunityArticleEnum;
use app\common\model\community\CommunityFollow;


class CommunityArticleLogic extends Logic
{

    /**
     * @notes 用户发布新文章通知粉丝
     * @param $user_id
     * @param $status
     * @return bool
     * @author 段誉
     * @date 2022/5/12 16:45
     */
    public static function noticeFans($user_id, $status)
    {
        if ($status != CommunityArticleEnum::STATUS_SUCCESS) {
            return true;
        }

        $fans = CommunityFollow::where(['follow_id' => $user_id, 'status' => 1])
            ->column('user_id');

        if (empty($fans)) {
            return true;
        }

        // 设置未读缓存
        foreach ($fans as $item) {
            $cache = new CommunityArticleCache('unread_user'. $item, ['has_new' => 1]);
            $cache->set();
        }
        return true;
    }


    /**
     * @notes 用户是否有未读文章
     * @param $user_id
     * @return int
     * @author 段誉
     * @date 2022/5/12 16:47
     */
    public static function hasNew($user_id)
    {
        if (empty($user_id)) {
            return 0;
        }
        $cache = new CommunityArticleCache('unread_user'. $user_id);
        $isEmpty = $cache->isEmpty();
        return !$isEmpty ? 1 : 0;
    }


    /**
     * @notes 删除未读状态
     * @param $user_id
     * @return bool
     * @author 段誉
     * @date 2022/5/12 16:51
     */
    public static function delUnRead($user_id)
    {
        $cache = new CommunityArticleCache('unread_user'. $user_id);
        return $cache->del();
    }

}