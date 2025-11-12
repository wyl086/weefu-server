<?php


namespace app\common\model\community;


use app\common\basics\Models;
use app\common\enum\CommunityCommentEnum;
use app\common\model\user\User;


/**
 * 种草社区评论
 * Class CommunityComment
 * @package app\common\model\community
 */
class CommunityComment extends Models
{


    /**
     * @notes 关联用户
     * @return \think\model\relation\HasOne
     * @author 段誉
     * @date 2022/5/7 15:22
     */
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id')
            ->bind(['nickname', 'avatar', 'sn']);
    }


    /**
     * @notes 子级
     * @return \think\model\relation\HasMany
     * @author 段誉
     * @date 2022/5/10 11:45
     */
    public function child()
    {
        return $this->hasMany(self::class, 'id', 'pid');
    }


    /**
     * @notes 关联文章
     * @return \think\model\relation\HasOne
     * @author 段誉
     * @date 2022/5/10 11:46
     */
    public function article()
    {
        return $this->hasOne(CommunityArticle::class, 'id', 'article_id');
    }


    /**
     * @notes 一级评论的所有子级评论
     * @param $value
     * @param $data
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/5/9 14:28
     */
    public function getChildAttr($value, $data)
    {
        $lists = self::with(['user'])
            ->whereFindInSet('ancestor_relation', $data['id'])
            ->where(['del' => 0, 'status' => CommunityCommentEnum::STATUS_SUCCESS])
            ->order(['like' => 'desc'])
            ->select()->toArray();
        return $lists;
    }


    /**
     * @notes 增加点赞数量
     * @param $id
     * @return mixed
     * @author 段誉
     * @date 2022/5/9 15:35
     */
    public static function incLike($id)
    {
        return self::where(['id' => $id])->inc('like')->update();
    }


    /**
     * @notes 减少点赞数量
     * @param $id
     * @return mixed
     * @author 段誉
     * @date 2022/5/9 15:38
     */
    public static function decLike($id)
    {
        $where = [
            ['id', '=', $id],
            ['like', '>=', 1]
        ];
        return self::where($where)->dec('like')->update();
    }

}