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

use app\common\basics\Validate;
use app\common\model\distribution\DistributionLevel;

class DistributionLevelValidate extends  Validate
{
    protected $rule = [
        'name' => 'require|checkName',
        'weights' => 'require|integer|gt:1|checkWeights',
        'first_ratio' => 'require|between:0,100',
        'second_ratio' => 'require|between:0,100',
        'update_relation' => 'require|in:1,2',
        'update_condition' => 'require|array|checkCondition',
        'singleConsumptionAmount' => 'gt:0',
        'cumulativeConsumptionAmount' => 'gt:0',
        'cumulativeConsumptionTimes' => 'integer|gt:0',
        'returnedCommission' => 'gt:0',
        'id' => 'require'
    ];


    protected  $message = [
        'name.require' => '请填写等级名称',
        'weights.require' => '请输入级别',
        'weights.integer' => '级别须为整型',
        'weights.gt' => '级别须大于1',
        'first_ratio.require' => '请输入一级佣金比例',
        'first_ratio.between' => '一级佣金比例须在0-100之间',
        'second_ratio.require' => '请输入二级佣金比例',
        'second_ratio.between' => '二级佣金比例须在0-100之间',
        'update_relation.require' => '请选择升级关系',
        'update_relation.in' => '升级关系状态值错误',
        'update_condition.require' => '请选择升级条件',
        'update_condition.array' => '升级条件数据格式错误',
        'singleConsumptionAmount.gt' => '单笔消费金额须大于0',
        'cumulativeConsumptionAmount.gt' => '累计消费金额须大于0',
        'cumulativeConsumptionTimes.gt' => '累计消费次数须大于0',
        'cumulativeConsumptionTimes.integer' => '累计消费次数须为整数',
        'returnedCommission.gt' => '已结算佣金收入须大于0',
        'id.require' => '参数缺失',
    ];

    /**
     * @notes 添加分销等级
     * @return DistributionLevelValidate
     * @author Tab
     * @date 2021/9/1 14:45
     */
    public function sceneAdd()
    {
        return $this->only(['name', 'weights', 'self_ratio', 'first_ratio', 'second_ratio', 'update_condition', 'update_relation', 'singleConsumptionAmount', 'cumulativeConsumptionAmount', 'cumulativeConsumptionTimes', 'returnedCommission']);
    }

    /**
     * @notes 编辑分销等级
     * @return DistributionLevelValidate
     * @author Tab
     * @date 2021/9/1 15:49
     */
    public function sceneEdit()
    {
        return $this->only(['id', 'name', 'weights', 'first_ratio', 'second_ratio'])
            ->remove('weights', 'gt');
    }

    /**
     * @notes 校验等级名称
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author Tab
     * @date 2021/9/1 14:42
     */
    public function checkName($value, $rule, $data)
    {
        $where = [['name', '=', $value]];
        if(isset($data['id'])) {
            // 编辑的场景
            $where[] = ['id', '<>', $data['id']];
        }
        $level = DistributionLevel::where($where)->findOrEmpty();
        if(!$level->isEmpty()) {
            return '等级名称已存在';
        }
        return true;
    }

    /**
     * @notes 校验等级级别
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author Tab
     * @date 2021/9/1 14:42
     */
    public function checkWeights($value, $rule, $data)
    {
        $where = [['weights', '=', $value]];
        if(isset($data['id'])) {
            // 编辑的场景
            $where[] = ['id', '<>', $data['id']];
        }
        $level = DistributionLevel::where($where)->findOrEmpty();
        if(!$level->isEmpty()) {
            return '等级级别已存在';
        }
        return true;
    }

    /**
     * @notes 校验升级条件
     * @param $value
     * @return bool|string
     * @author Tab
     * @date 2021/9/1 14:43
     */
    public function checkCondition($value, $rule, $data)
    {
        if(!count($value)) {
            return '请选择升级条件';
        }
        foreach($value as $v) {
            if(!isset($data[$v]) || empty($data[$v])) {
                return '升级条件数据未填写';
            }
        }
        return true;
    }
}