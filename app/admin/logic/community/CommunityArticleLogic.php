<?php


namespace app\admin\logic\community;


use app\common\basics\Logic;
use app\common\enum\CommunityLikeEnum;
use app\common\model\community\CommunityArticle;
use app\common\model\community\CommunityComment;
use app\common\model\community\CommunityLike;
use app\common\model\community\CommunityTopic;
use app\common\logic\CommunityArticleLogic as CommonArticleLogic;
use app\common\server\UrlServer;
use think\Exception;
use think\facade\Db;


/**
 * 种草社区文章逻辑
 * Class CommunityArticleLogic
 * @package app\admin\logic\community
 */
class CommunityArticleLogic extends Logic
{

    /**
     * @notes 文章列表
     * @param $get
     * @return array
     * @author 段誉
     * @date 2022/5/10 11:07
     */
    public static function lists($get)
    {
        $where = [
            ['a.del', '=', 0]
        ];

        if (!empty($get['keyword'])) {
            $where[] = ['u.sn|u.nickname|u.mobile', 'like', '%' . $get['keyword'] . '%'];
        }

        if (!empty($get['content'])) {
            $where[] = ['a.content', 'like', '%' . $get['content'] . '%'];
        }

        if (isset($get['status']) && $get['status'] != '') {
            $where[] = ['a.status', '=', $get['status']];
        }

        if (isset($get['start_time']) && $get['start_time'] != '') {
            $where[] = ['a.audit_time', '>=', strtotime($get['start_time'])];
        }

        if (isset($get['end_time']) && $get['end_time'] != '') {
            $where[] = ['a.audit_time', '<=', strtotime($get['end_time'])];
        }

        $model = new CommunityArticle();
        $lists = $model->with(['images'])->alias('a')
            ->field('a.*,u.nickname,u.avatar,u.sn')
            ->join('user u', 'u.id = a.user_id')
            ->where($where)
            ->order(['id' => 'desc'])
            ->append(['status_desc'])
            ->paginate([
                'page' => $get['page'],
                'list_rows' => $get['limit'],
                'var_page' => 'page'
            ])
            ->toArray();

        foreach ($lists['data'] as &$item) {
            $item['avatar'] = !empty($item['avatar']) ? UrlServer::getFileUrl($item['avatar']) : '';
        }

        return ['count' => $lists['total'], 'lists' => $lists['data']];
    }


    /**
     * @notes 文章详情
     * @param $id
     * @return array
     * @author 段誉
     * @date 2022/5/10 16:53
     */
    public static function detail($id)
    {
        $detail = CommunityArticle::with(['images', 'topic', 'user' => function ($query) {
            $query->field(['id', 'nickname', 'sn']);
        }])
            ->append(['shop_data', 'goods_data', 'status_desc'])
            ->findOrEmpty($id);

        $detail['cate_name'] = $detail['topic']['cate']['name'] ?? '';
        $detail['audit_time'] = date('Y-m-d H:i:s', $detail['audit_time']);
        return $detail->toArray();
    }


    /**
     * @notes 删除文章
     * @param $id
     * @return bool
     * @author 段誉
     * @date 2022/5/10 16:34
     */
    public static function del($id)
    {
        Db::startTrans();
        try {
            $article = CommunityArticle::find($id);
            $article->del = 1;
            $article->update_time = time();
            $article->save();

            if (!empty($article['topic_id'])) {
                CommunityTopic::decArticleNum($article['topic_id']);
            }

            Db::commit();
            return true;

        } catch (Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 审核文章
     * @param $post
     * @return bool
     * @author 段誉
     * @date 2022/5/12 16:57
     */
    public static function audit($post)
    {
        Db::startTrans();
        try {
            $article = CommunityArticle::findOrEmpty($post['id']);
            $article->status = $post['status'];
            $article->audit_remark = $post['audit_remark'] ?? '';
            $article->audit_time = time();
            $article->save();

            // 通知粉丝有新文章发布
            CommonArticleLogic::noticeFans($article['user_id'], $post['status']);

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 文章关联评论及点赞
     * @param $get
     * @return array
     * @throws \think\db\exception\DbException
     * @author 段誉
     * @date 2022/5/11 10:14
     */
    public static function getRelationData($get)
    {
        $type = $get['type'] ?? 'comment';
        if ($type == 'comment') {
            $lists = CommunityComment::with(['user'])
                ->where([
                    'del' => 0,
                    'article_id' => $get['id'],
                ])->paginate([
                    'page' => $get['page'],
                    'list_rows' => $get['limit'],
                    'var_page' => 'page'
                ])
                ->toArray();
        } else {
            $lists = CommunityLike::with(['user'])
                ->where([
                    'relation_id' => $get['id'],
                    'type' => CommunityLikeEnum::TYPE_ARTICLE
                ])
                ->paginate([
                    'page' => $get['page'],
                    'list_rows' => $get['limit'],
                    'var_page' => 'page'
                ])
                ->toArray();
        }
        return ['count' => $lists['total'], 'lists' => $lists['data']];
    }


}