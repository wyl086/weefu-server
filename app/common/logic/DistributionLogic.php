<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\common\logic;

use app\common\model\distribution\Distribution;
use app\common\model\distribution\DistributionLevel;
use app\common\server\ConfigServer;

/**
 * 分销基础信息逻辑层
 * Class DistributionLogic
 * @package app\common\logic
 */
class DistributionLogic
{
    /**
     * @notes 添加分销基础信息记录
     * @param $userId
     * @author Tab
     * @date 2021/9/2 11:55
     */
    public static function add($userId)
    {
        // 默认分销会员等级
        $defaultLevelId = DistributionLevel::where('is_default', 1)->value('id');
        // 分销会员开通方式
        $apply_condition = ConfigServer::get('distribution', 'apply_condition', 2);
        $isDistribution = $apply_condition == 1 ? 1 : 0;

        $data = [
            'user_id' => $userId,
            'level_id' => $defaultLevelId,
            'is_distribution' => $isDistribution,
            'is_freeze' => 0,
            'remark' => '',
        ];

        if($isDistribution) {
            // 成为分销会员时间
            $data['distribution_time'] = time();
        }

        Distribution::create($data);
    }
}