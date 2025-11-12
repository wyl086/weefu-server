<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\admin\validate\distribution;

use think\Validate;
use app\common\model\user\User;

class MemberValidate extends Validate
{
    protected $rule = [
        // 添加分销会员
        'sn' => 'require|max:10',
        'remarks' => 'max:100',
        // 更新上级
        'user_id' => 'require',
        'change_type' => 'require',
        'referrer_sn' => 'requireIf:change_type,appoint|checkReferrer',
        // 冻结、解冻资格/ 审核分销会员
        'id' => 'require',
        'type' => 'require',
    ];


    protected  $message = [
        'sn.require' => '请输入会员编号',
        'sn.max' => '会员编号不能超过10个字符',
        'remarks.max' => '备注不能超过100个字符',
        'user_id.require' => '会员id不能为空',
        'change_type.require' => '调整方式不能为空',
        'referrer_sn.requireIf' => '指定上级不能为空',
        'id.require' => '请输入会员id',
        'type.require' => '请输入类型',
    ];

    public function sceneAdd()
    {
        return $this->only(['sn', 'remarks']);
    }

    public function sceneUpdateLeader()
    {
        return $this->only(['user_id', 'change_type', 'referrer_sn']);
    }

    public function sceneFreeze()
    {
        return $this->only(['id', 'type']);
    }

    public function sceneAudit()
    {
        return $this->only(['id', 'type']);
    }

    public function checkReferrer($value, $rule, $data)
    {
        if (empty($value) && $data['change_type'] == 'clear'){
            return true;
        }

        $referrer = User::where('sn', $value)->findOrEmpty();

        if ($referrer->isEmpty()){
            return '推荐人不存在';
        }

        $referrer = $referrer->toArray();

        if ($referrer['id'] == $data['user_id']){
            return '上级推荐人不能是自己';
        }

        if ($referrer['is_distribution'] == 0){
            return '对方不是分销会员';
        }

        $ancestor_relation = explode(',', $referrer['ancestor_relation']);
        if (!empty($ancestor_relation) && in_array($data['user_id'], $ancestor_relation)) {
            return '推荐人不能是自己的任意下级';
        }

        return true;
    }
}