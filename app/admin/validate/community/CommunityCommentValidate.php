<?php


namespace app\admin\validate\community;


use app\common\basics\Validate;
use app\common\model\community\CommunityComment;


/**
 * 社区种草评论验证
 * Class CommunityCommentValidate
 * @package app\admin\validate\community
 */
class CommunityCommentValidate extends Validate
{
    protected $rule = [
        'id' => 'require|number|checkComment',
        'status' => 'require|in:1,2',
    ];

    protected $message = [
        'id.require' => 'id不可为空',
        'id.number' => 'id必须为数字',
        'status.require' => '请选择审核状态',
        'status.in' => '审核状态值异常',
    ];

    protected $scene = [
        'audit' => ['id', 'status'],
        'id' => ['id'],
    ];


    /**
     * @notes 校验评论
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2022/5/10 15:08
     */
    protected function checkComment($value, $rule, $data)
    {
        $comment = CommunityComment::where(['del' => 0])->findOrEmpty($value);

        if ($comment->isEmpty()) {
            return '评论信息不存在';
        }

        return true;
    }





}