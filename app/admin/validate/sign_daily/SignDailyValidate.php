<?php

namespace app\admin\validate\sign_daily;

use app\common\basics\Validate;
use app\common\model\sign_daily\SignDaily;

/**
 * 签到验证
 * Class SignDailyValidate
 * @package app\admin\validate
 */
class SignDailyValidate extends Validate
{
    protected $rule = [
        'integral' => 'requireIf:integral_status,1|integer|checkIntegral',   //积分
        'growth' => 'requireIf:growth_status,1|integer|checkGrowth',   //成长值
        'days' => 'require|integer|gt:0|checkDays',   //连续签到天数
        'instructions' => 'require'
    ];

    protected $message = [
        'integral.requireIf' => '积分不能为空',
        'integral.integer' => '积分必须为整数',

        'growth.requireIf' => '成长值不能为空',
        'growth.integer' => '成长值必须为整数',

        'days.require' => '连续签到天数不能为空',
        'days.integer' => '连续签到天数必须为整数',
        'days.gt' => '连续签到天数必须大于0',
        'instructions' => '签到规则说明不能为空'
    ];

    public function sceneAdd()
    {
        $this->only(['integral', 'days', 'growth'])
            ->remove('instructions');

    }

    public function sceneEdit()
    {
        $this->only(['integral', 'days', 'growth'])
            ->remove('instructions');

    }

    public function sceneSign()
    {
        $this->only(['integral', 'growth', 'instructions'])
            ->remove('days');
    }


    /**
     * @notes 验证积分
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2022/3/17 14:45
     */
    public function checkIntegral($value, $rule, $data)
    {
        if (isset($data['integral_status']) && $data['integral_status'] && $value <= 0) {
            return '积分必须大于0';
        }
        return true;
    }


    /**
     * @notes 验证成长值
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2022/3/17 14:45
     */
    public function checkGrowth($value, $rule, $data)
    {
        if (isset($data['growth_status']) && $data['growth_status'] && $value <= 0) {
            return '成长值必须大于0';
        }
        return true;
    }


    /**
     * @notes 判断连续签到天数是否存在
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2022/2/17 10:46
     */
    public function checkDays($value, $rule, $data)
    {
        if (!isset($data['integral_status']) && !isset($data['growth_status'])) {
            return '请选择积分奖励或成才值奖励';
        }

        if (isset($data['id'])) {
            $where[] = ['id', '<>', $data['id']];
        }
        $where[] = ['days', '=', $value];
        $where[] = ['del', '=', 0];
        $sign_daily = SignDaily::where($where)->findOrEmpty();

        if (!$sign_daily->isEmpty()) {
            return '该连续签到天数已存在';
        }
        return true;
    }


}