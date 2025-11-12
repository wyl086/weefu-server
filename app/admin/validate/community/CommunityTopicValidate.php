<?php


namespace app\admin\validate\community;


use app\common\basics\Validate;
use app\common\model\community\CommunityTopic;

/**
 * 种草社区话题验证
 * Class CommunityTopicValidate
 * @package app\admin\validate\community
 */
class CommunityTopicValidate extends Validate
{
    protected $rule = [
        'id' => 'require|number',
        'name' => 'require|max:12|unique:' . CommunityTopic::class . ',name^del',
        'image' => 'require',
        'cid' => 'require|number',
        'is_show' => 'require|in:0,1',
        'is_recommend' => 'require|in:0,1',
        'sort' => 'egt:0',
        'field' => 'require|checkUpdateField',
        'value' => 'require|in:0,1',
    ];

    protected $message = [
        'id.require' => 'id不可为空',
        'id.number' => 'id必须为数字',
        'name.require' => '请填写话题名称',
        'name.max' => '话题名称长度不能超过12位',
        'name.unique' => '话题名称已存在',
        'image.require' => '请选择话题图标',
        'cid.require' => '请选择关联分类',
        'is_recommend.require' => '请选择是否推荐',
        'is_recommend.in' => '推荐状态异常',
        'is_show.require' => '请选择是否显示',
        'is_show.in' => '显示状态异常',
        'sort.egt' => '请填写大于等于0的排序值',
        'field.egt' => '参数缺失',
        'value.egt' => '参数缺失',
        'value.in' => '状态值异常',
    ];

    protected $scene = [
        'id' => ['id'],
        'status' => ['id', 'field', 'value'],
        'add' => ['name', 'image', 'cid', 'is_recommend', 'is_show', 'sort'],
        'edit' => ['id', 'name', 'image', 'cid', 'is_recommend', 'is_show', 'sort']
    ];


    /**
     * @notes 校验更新字段
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2022/4/28 15:13
     */
    protected function checkUpdateField($value, $rule, $data)
    {
        $allow_field = ['is_show', 'is_recommend'];
        if (in_array($value, $allow_field)) {
            return true;
        }
        return '非法字段';
    }
}