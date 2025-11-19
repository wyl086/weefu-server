<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\admin\validate;

use app\common\model\Agent;
use app\common\model\shop\Shop;
use app\common\model\user\User;
use think\Validate;

class AgentValidate extends Validate
{
    protected $rule = [
        'source' => 'require|in:1,2',
        'source_id' => 'require|checkSourceId',
        'mobile' => 'require|mobile|checkMobile',
        'province_id' => 'integer',
        'city_id' => 'integer',
        'district_id' => 'integer',
        'is_city_agent' => 'in:0,1',
        'is_district_agent' => 'in:0,1',
        'is_service' => 'in:0,1',
        'is_promoter' => 'in:0,1',
        'status' => 'require|in:0,1',
        'remark' => 'max:255',
        'id' => 'require|integer',
        'ids' => 'require|array',
    ];

    protected $message = [
        'source.require' => '请选择来源',
        'source.in' => '来源参数错误',
        'source_id.require' => '请选择来源对象',
        'mobile.require' => '请输入手机号码',
        'mobile.mobile' => '手机号码格式错误',
        'status.require' => '请选择状态',
        'status.in' => '状态参数错误',
        'remark.max' => '备注不能超过255个字符',
        'id.require' => '参数错误',
        'ids.require' => '请选择要操作的数据',
    ];

    public function sceneAdd()
    {
        return $this->only(['source', 'source_id', 'mobile', 'province_id', 'city_id', 'district_id', 'is_city_agent', 'is_district_agent', 'is_service', 'is_promoter', 'status', 'remark']);
    }

    public function sceneEdit()
    {
        return $this->only(['id', 'source', 'source_id', 'mobile', 'province_id', 'city_id', 'district_id', 'is_city_agent', 'is_district_agent', 'is_service', 'is_promoter', 'status', 'remark']);
    }

    /**
     * 验证来源ID是否存在
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     */
    protected function checkSourceId($value, $rule, $data)
    {
        if ($data['source'] == 1) {
            // 商户
            $shop = Shop::where(['id' => $value, 'del' => 0])->find();
            if (empty($shop)) {
                return '商户不存在';
            }
        } else {
            // 用户
            $user = User::where(['id' => $value, 'del' => 0])->find();
            if (empty($user)) {
                return '用户不存在';
            }
        }
        return true;
    }

    /**
     * 验证手机号是否重复
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     */
    protected function checkMobile($value, $rule, $data)
    {
        $where = [
            ['mobile', '=', $value],
            ['del', '=', 0]
        ];

        // 编辑时排除自己
        if (isset($data['id']) && $data['id']) {
            $where[] = ['id', '<>', $data['id']];
        }

        $agent = Agent::where($where)->find();
        if ($agent) {
            return '该手机号码已被使用';
        }

        return true;
    }
}


