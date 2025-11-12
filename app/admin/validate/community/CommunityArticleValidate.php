<?php


namespace app\admin\validate\community;


use app\common\basics\Validate;
use app\common\model\community\CommunityArticle;


/**
 * 仲裁社区文章验证
 * Class CommunityArticleValidate
 * @package app\admin\validate\community
 */
class CommunityArticleValidate extends Validate
{
    protected $rule = [
        'id' => 'require|number|checkArticle',
        'status' => 'require|in:1,2',
        'audit_remark' => 'requireIf:status,2|max:100',
    ];

    protected $message = [
        'id.require' => 'id不可为空',
        'id.number' => 'id必须为数字',
        'status.require' => '请选择审核状态',
        'status.in' => '审核状态值异常',
        'audit_remark.requireIf' => '请填写拒绝说明',
        'audit_remark.max' => '审核说明仅限100字内',
    ];

    protected $scene = [
        'audit' => ['id', 'status', 'audit_remark'],
        'id' => ['id'],
    ];


    /**
     * @notes 校验文章
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2022/5/10 15:08
     */
    protected function checkArticle($value, $rule, $data)
    {
        $comment = CommunityArticle::where(['del' => 0])->findOrEmpty($value);

        if ($comment->isEmpty()) {
            return '文章信息不存在';
        }

        return true;
    }





}