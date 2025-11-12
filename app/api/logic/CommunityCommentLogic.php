<?php


namespace app\api\logic;


use app\common\basics\Logic;
use app\common\enum\CommunityCommentEnum;
use app\common\enum\CommunityLikeEnum;
use app\common\model\community\CommunityArticle;
use app\common\model\community\CommunityComment;
use app\common\model\community\CommunityLike;
use app\common\server\ConfigServer;
use app\common\server\UrlServer;
use think\facade\Db;


/**
 * 种草社区评论
 * Class CommunityCommentLogic
 * @package app\api\logic
 */
class CommunityCommentLogic extends Logic
{

    /**
     * @notes 评论列表
     * @param $get
     * @param $page
     * @param $size
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/5/9 14:22
     */
    public static function getCommentLists($user_id, $get, $page, $size)
    {
        $where = [
            'del' => 0,
            'pid' => 0,
            'article_id' => $get['article_id'] ?? 0,
            'status' => CommunityCommentEnum::STATUS_SUCCESS
        ];
        $count = CommunityComment::where($where)->count();
        $lists = CommunityComment::with(['user'])
            ->where($where)
            ->append(['child'])
            ->order(['like' => 'desc', 'id' => 'desc'])
            ->page($page, $size)
            ->select()
            ->toArray();

        $article_data = self::getArticleData($get['article_id']);
        $article = $article_data['article'];
        // 当前文章所有评论人
        $reply_user = $article_data['reply_user'];

        $likes = CommunityLike::where([
            'user_id' => $user_id,
            'type' => CommunityLikeEnum::TYPE_COMMENT
        ])->column('relation_id');

        foreach ($lists as $key => $item) {
            $comment = self::formatComment($item, $article, $reply_user, $likes);
            $comment = self::getCommentChildMore($comment);
            $lists[$key] = self::isSecondComment($comment);
        }

        $result = [
            'list' => $lists,
            'page' => $page,
            'size' => $size,
            'count' => $count,
            'more' => is_more($count, $page, $size)
        ];
        return $result;
    }


    /**
     * @notes 文章信息
     * @param $article_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/5/9 14:41
     */
    public static function getArticleData($article_id)
    {
        $article = CommunityArticle::with(['user'])->findOrEmpty($article_id);

        // 当前文章所有评论人
        $reply_user = CommunityComment::with(['user'])
            ->field('id,user_id,pid')
            ->where('article_id', $article_id)
            ->select()
            ->toArray();

        return [
            'article' => $article,
            'reply_user' => $reply_user,
        ];
    }


    /**
     * @notes 格式评论数据
     * @param $comment
     * @param $author
     * @return mixed
     * @author 段誉
     * @date 2022/5/7 16:02
     */
    public static function formatComment($comment, $article, $reply_user = [], $likes = [])
    {
        $author = $article['user_id'];
        $comment['avatar'] = UrlServer::getFileUrl($comment['avatar']);
        $comment['is_author'] = 0;
        if ($comment['user_id'] == $author) {
            $comment['is_author'] = 1;
        }

        $comment['is_like'] = in_array($comment['id'], $likes) ? 1 : 0;
        // 获取回复的上级评论人信息
        $comment = self::getRelyData($comment, $article, $reply_user);

        if (!empty($comment['child'])) {
            foreach ($comment['child'] as $key => $item) {
                $comment['child'][$key] = self::formatComment($item, $article, $reply_user, $likes);
            }
        }
        return $comment;
    }


    /**
     * @notes 获取评论列表子级大于2的数量
     * @param $comment
     * @return mixed
     * @author 段誉
     * @date 2022/5/10 10:34
     */
    public static function getCommentChildMore($comment)
    {
        $comment['more'] = 0;
        if (count($comment['child']) > 2) {
            $comment['more'] = count($comment['child']) - 2;
            $comment['child'] = array_splice($comment['child'], 0, 2);
        }
        return $comment;
    }


    /**
     * @notes 是否为二级评论
     * @param $comment
     * @return mixed
     * @author 段誉
     * @date 2022/5/10 14:54
     */
    public static function isSecondComment($comment)
    {
        if(!empty($comment['child'])) {
            foreach ($comment['child'] as $key => $item) {
                $is_second = 0;
                if ($comment['id'] == $item['pid']) {
                    $is_second = 1;
                }
                $comment['child'][$key]['is_second'] = $is_second;
            }
        }
        return $comment;
    }


    /**
     * @notes 获取评论回复的上级评论人信息
     * @param $comment
     * @param $article
     * @param $reply_user
     * @return mixed
     * @author 段誉
     * @date 2022/5/9 14:18
     */
    public static function getRelyData($comment, $article, $reply_user)
    {
        // 回复谁的评论
        $comment['reply_id'] = $article['user']['id'];
        $comment['reply_nickname'] = $article['user']['nickname'];
        $comment['reply_avatar'] = UrlServer::getFileUrl($article['user']['avatar']);
        if (!empty($comment['pid'])) {
            foreach ($reply_user as $reply) {
                if ($reply['id'] == $comment['pid']) {
                    $comment['reply_id'] = $reply['user_id'];
                    $comment['reply_nickname'] = $reply['nickname'];
                    $comment['reply_avatar'] = UrlServer::getFileUrl($reply['avatar']);
                }
            }
        }
        return $comment;
    }

    
    /**
     * @notes 添加评论
     * @param $user_id
     * @param $post
     * @return array|false
     * @author 段誉
     * @date 2022/5/10 11:29
     */
    public static function addComment($user_id, $post)
    {
        Db::startTrans();
        try {

            $article = CommunityArticle::with(['user' => function($query) {
                $query->field(['id','nickname', 'avatar']);
            }])->findOrEmpty($post['article_id']);

            if ($article['status'] != CommunityCommentEnum::STATUS_SUCCESS) {
                throw new \Exception('暂不可评论');
            }

            $data = [
                'user_id' => $user_id,
                'article_id' => $post['article_id'],
                'pid' => $post['pid'] ?? 0,
                'comment' => $post['comment'],
                'status' => CommunityCommentEnum::STATUS_WAIT,
                'ancestor_relation' => ''
            ];

            // 如果是无需审核的，状态直接为已审核
            $config = ConfigServer::get('community', 'audit_comment', 1);
            if ($config == 0) {
                $data['status'] = CommunityCommentEnum::STATUS_SUCCESS;
            }

            // 上级评论id关系链
            if (!empty($post['pid'])) {
                $relation = CommunityComment::with(['user'])->findOrEmpty($post['pid']);
                $ancestor = $relation->isEmpty() ? '' : trim($relation['ancestor_relation']);
                $data['ancestor_relation'] = trim($post['pid'] . ',' . $ancestor, ',');
            }

            $comment = CommunityComment::create($data);

            // 增加文章评论数
            CommunityArticle::where(['id' => $post['article_id']])
                ->inc('comment')
                ->update();

            // 评论信息
            $info = CommunityComment::with(['user'])
                ->withoutField(['ancestor_relation','update_time', 'del'])
                ->where(['id' => $comment['id']])
                ->findOrEmpty()
                ->toArray();
            $info['avatar'] = UrlServer::getFileUrl($info['avatar']);
            $info['create_time'] = friend_date(strtotime($info['create_time']));
            $info['reply_id'] = $relation['user_id'] ?? $article['user_id'];
            $info['reply_nickname'] = $relation['nickname'] ?? $article['user']['nickname'];
            $reply_avatar = !empty($relation['avatar']) ? $relation['avatar'] : $article['user']['avatar'];
            $info['reply_avatar'] = UrlServer::getFileUrl($reply_avatar);
            $info['is_like'] = 0;

            Db::commit();
            return [
                'msg' => $data['status'] ? '评论成功' : '评论成功,正在审核中',
                'data' => $info
            ];

        } catch (\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 一级评论下的所有评论
     * @param $get
     * @param $page
     * @param $size
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/5/9 14:33
     */
    public static function getChildComment($user_id, $get, $page, $size)
    {
        $comment_id = (int)$get['comment_id'] ?? 0;
        $comment = CommunityComment::findOrEmpty($comment_id);
        if ($comment->isEmpty()) {
            return [
                'list' => [],
                'page' => 0,
                'size' => 0,
                'count' => 0,
                'more' => 0
            ];
        }

        $count = CommunityComment::with(['user'])
            ->whereFindInSet('ancestor_relation', $comment_id)
            ->where(['del' => 0, 'status' => CommunityCommentEnum::STATUS_SUCCESS])
            ->count();

        $lists = CommunityComment::with(['user'])
            ->whereFindInSet('ancestor_relation', $comment_id)
            ->where(['del' => 0, 'status' => CommunityCommentEnum::STATUS_SUCCESS])
            ->order(['like' => 'desc'])
            ->page($page, $size)
            ->select()->toArray();

        $likes = CommunityLike::where([
            'user_id' => $user_id,
            'type' => CommunityLikeEnum::TYPE_COMMENT
        ])->column('relation_id');

        $article_data = self::getArticleData($comment['article_id']);
        $article = $article_data['article'];
        // 当前文章所有评论人
        $reply_user = $article_data['reply_user'];

        foreach ($lists as $key => $item) {
            $item['is_second'] = 0;
            if ($item['pid'] == $comment_id) {
                $item['is_second'] = 1;
            }
            $lists[$key] = self::formatComment($item, $article, $reply_user, $likes);
        }

        return [
            'list' => $lists,
            'page' => $page,
            'size' => $size,
            'count' => $count,
            'more' => is_more($count, $page, $size)
        ];
    }


}