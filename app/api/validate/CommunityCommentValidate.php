<?php

namespace app\api\validate;

use app\common\basics\Validate;
use app\common\enum\CommunityArticleEnum;
use app\common\model\community\CommunityArticle;
use app\common\model\community\CommunityComment;

/**
 * 种草社区评论验证
 * Class CommunityCommentValidate
 * @package app\api\validate
 */
class CommunityCommentValidate extends Validate
{
    protected $rule = [
        'article_id' => 'require|checkArticle',
        'comment' => 'require|max:150',
        'pid' => 'checkComment',
    ];

    protected $message = [
        'article_id.require' => '参数缺失',
        'comment.require' => '请输入评论内容',
        'comment.max' => '评论内容不可超过150字符',
    ];


    public function sceneAdd()
    {
        return $this->only(['article_id', 'comment', 'pid']);
    }

    public function sceneLists()
    {
        return $this->only(['article_id']);
    }

    /**
     * @notes 校验文章
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2022/5/7 11:19
     */
    protected function checkArticle($value, $rule, $data)
    {
        $article = CommunityArticle::findOrEmpty($value);

        if ($article->isEmpty()) {
            return '种草内容不存在';
        }

        if ($article['del'] == 1)  {
            return '该种草内容已被删除';
        }

        return true;
    }


    /**
     * @notes 校验评论
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2022/5/7 11:22
     */
    protected function checkComment($value, $rule, $data)
    {
        if (empty($value)) {
            return true;
        }
        $comment = CommunityComment::findOrEmpty($value);
        if ($comment->isEmpty()) {
            return '回复评论不存在';
        }
        return true;
    }


}