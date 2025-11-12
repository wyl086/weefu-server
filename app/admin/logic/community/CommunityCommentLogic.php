<?php


namespace app\admin\logic\community;


use app\common\basics\Logic;
use app\common\enum\CommunityCommentEnum;
use app\common\model\community\CommunityArticle;
use app\common\model\community\CommunityComment;
use app\common\server\UrlServer;


/**
 * 种草社区评论
 * Class CommunityCommentLogic
 * @package app\admin\logic\community
 */
class CommunityCommentLogic extends Logic
{

    /**
     * @notes 评论列表
     * @param $get
     * @return array
     * @author 段誉
     * @date 2022/5/10 12:06
     */
    public static function lists($get)
    {
        $where = [
            ['c.del', '=', 0]
        ];

        if (!empty($get['keyword'])) {
            $where[] = ['u.sn|u.nickname|u.mobile', 'like', '%' . $get['keyword'] . '%'];
        }

        if (!empty($get['comment'])) {
            $where[] = ['c.comment', 'like', '%' . $get['comment'] . '%'];
        }

        if (isset($get['status']) && $get['status'] != '') {
            $where[] = ['c.status', '=', $get['status']];
        }

        $model = new CommunityComment();
        $lists = $model->alias('c')
            ->with(['article' => function($query) {
                $query->field('id,content,topic_id');
            }])
            ->field('c.*,u.nickname,u.avatar,u.sn')
            ->join('user u', 'u.id = c.user_id')
            ->where($where)
            ->order(['id' => 'desc'])
            ->append(['status_desc'])
            ->paginate([
                'page' => $get['page'],
                'list_rows' => $get['limit'],
                'var_page' => 'page'
            ]);
        foreach ($lists as &$item) {
            $item['avatar'] = !empty($item['avatar']) ? UrlServer::getFileUrl($item['avatar']) : '';
            $item['status_desc'] = CommunityCommentEnum::getStatusDesc($item['status']);
            $item['topic_name'] = $item['article']['topic']['name'] ?? '';
        }

        return ['count' => $lists->total(), 'lists' => $lists->getCollection()];
    }


    /**
     * @notes 详情
     * @param $id
     * @return array
     * @author 段誉
     * @date 2022/5/10 12:15
     */
    public static function detail($id)
    {
        return CommunityComment::with(['article'])->findOrEmpty($id)->toArray();
    }


    /**
     * @notes 审核成功
     * @param $post
     * @return CommunityComment
     * @author 段誉
     * @date 2022/5/10 15:11
     */
    public static function audit($post)
    {
        return CommunityComment::where(['id' => $post['id']])->update([
            'status' => $post['status'],
            'update_time' => time()
        ]);
    }



    /**
     * @notes 删除评论
     * @param $id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/5/10 15:23
     */
    public static function del($id)
    {
        // 删除评论
        $comment = CommunityComment::find($id);
        $comment->del     = 1;
        $comment->update_time    = time();
        $comment->save();

        // 更新文章评论数
        CommunityArticle::where([
            ['id', '=', $comment['article_id']],
            ['comment', '>=', 1]]
        )->dec('comment')->update();
    }


}