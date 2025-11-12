<?php
namespace app\api\validate;

use app\admin\logic\distribution\DistributionSettingLogic;
use app\admin\logic\setting\UserLogic;
use app\common\model\distribution\Distribution;
use app\common\server\ConfigServer;
use think\Validate;
use app\common\model\distribution\DistributionMemberApply;
use app\common\model\user\User;

class DistributionValidate extends Validate
{
    protected $rule = [
        'user_id'   => 'require|apply',
        'real_name' => 'require',
        'mobile'    => 'require|mobile',
        'province'  => 'require|number',
        'city'      => 'require|number',
        'district'  => 'require|number',
        'reason'    => 'require',
        'code'      => 'require|checkCode',
    ];

    protected $message = [
        'user_id.require'   => '无法获取用户ID',
        'real_name.require' => '请填写真实姓名',
        'mobile.require'    => '请填写手机号',
        'mobile.mobile'     => '请填写正确的手机号码',
        'province.require'  => '请填写省份',
        'province.number'   => '省份需为数字代号',
        'city.require'      => '请填写城市',
        'city.number'       => '城市须为数字代号',
        'district.require'  => '请填写县区',
        'district.number'   => '县区段为数字代号',
        'reason.require'    => '请填写申请原因',
        'code.require'      => '请填写邀请码',
    ];

    /**
     * 申请分销会员场景
     */
    public function sceneApply()
    {
        return $this->only(['user_id','real_name','mobile','province','city','district','reason']);
    }

    /**
     * 填写邀请码
     */
    public function sceneCode()
    {
        return $this->only(['code']);
    }

    /**
     * 判断当前用户是否有待审核的申请记录
     */
    public function apply($value, $rule, $data)
    {
        $where = [
            'user_id' => $value,
            'status' => 0, // 审核中
        ];
        $apply = DistributionMemberApply::where($where)->findOrEmpty();
        if($apply->isEmpty()) {
           return true;
        }
        return '正在审核中，请勿重复提交申请';
    }

    /**
     * 邀请码验证
     */
    public function checkCode($value, $rule, $data)
    {
        $config = UserLogic::getConfig();
        // 总开关
        $config['switch'] = ConfigServer::get('distribution', 'is_open');
        if(!$config['switch']) {
            return '系统已关闭分销功能,无法邀请粉丝';
        }
        if(!$config['is_open']) {
            return '系统已关闭邀请功能';
        }
        $user = User::where(['id'=>$data['user_id'], 'del'=>0])->findOrEmpty();
        if($user->isEmpty()) {
            return '无法获取当前用户信息';
        }
        if(!empty($user['first_leader'])) {
            return '已有邀请人';
        }
        $firstLader = User::field('id,is_distribution,level,ancestor_relation,user_delete')
            ->where('distribution_code', $value)
            ->findOrEmpty();
        if($firstLader->isEmpty() ||  $firstLader->user_delete) {
            return '无效的邀请码';
        }
        if($firstLader['id'] == $data['user_id']) {
            return '不能填自己的邀请码';
        }

        // qualifications-邀请资格 【1-全部用户 2-指定等级用户】
        $invite_appoint_user = ConfigServer::get('invite', 'invite_appoint_user', []);
        if(in_array(2, $config['qualifications']) && !in_array($firstLader['level'],$invite_appoint_user)) {
            return '邀请下级资格未达到要求';
        }

        // 如果当前用户id出现在邀请人的祖先链路中，代表当前用户是已经是邀请人的上级或祖先级，同时意味着邀请人是当前用户的后代级别
        // 不能将自己的上级设置为自己的后代级用户
        $ancestorArr = explode(',', $firstLader['ancestor_relation']);
        if(!empty($ancestorArr) && in_array($data['user_id'], $ancestorArr)) {
            return '不能填写自己下级的邀请码';
        }
        return true;
    }
}