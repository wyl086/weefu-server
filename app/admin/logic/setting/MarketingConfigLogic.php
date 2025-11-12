<?php

namespace app\admin\logic\setting;

use app\common\basics\Logic;
use app\common\server\ConfigServer;
use think\facade\Validate;

/**
 * 营销逻辑
 * Class MarketingConfigLogic
 * @package app\admin\logic\setting
 */
class MarketingConfigLogic extends Logic
{
    /**
     * @notes 设置消费奖励
     * @param $post
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2022/2/18 4:22 下午
     */
    public static function setOrderAward($post)
    {
        $open_award = isset($post['open_award']) && $post['open_award'] == 'on' ? 1 : 0;
        $award_event = $post['award_event'] ?? 0;
        $award_ratio = $post['award_ratio'] ?? '';
        if ($open_award == 1) {
            if (!isset($post['award_event']) || $post['award_event'] == '') {
                return '开启消费送积分时，赠送积分事件不能为空';
            }
            if (!isset($post['award_ratio']) || $post['award_ratio'] == '') {
                return '开启消费送积分时，赠送积分比例不能为空';
            }
            if (!Validate::isNumber($award_ratio)) {
                return '赠送积分比率必须为纯数字';
            }
        }
        ConfigServer::set('order_award','open_award',$open_award);
        ConfigServer::set('order_award','award_event',$award_event);
        ConfigServer::set('order_award','award_ratio',$award_ratio);

        return true;
    }
}