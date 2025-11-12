<?php

namespace app\api\logic;

use app\common\basics\Logic;
use app\common\logic\AccountLogLogic;
use app\common\model\AccountLog;
use app\common\model\sign_daily\SignDaily;
use app\common\model\sign_daily\UserSign;
use app\common\model\user\User;
use app\common\server\ConfigServer;
use app\common\server\UrlServer;
use think\facade\Db;


/**
 * 签到逻辑
 * Class SignLogic
 * @package app\api\logic
 */
class SignLogic extends Logic
{

    /**
     * @notes 签到列表
     * @param $user_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/2/17 18:29
     */
    public static function lists($user_id)
    {
        //用户信息
        $user = User::where(['id' => $user_id])
            ->field('id,nickname,avatar,user_integral')
            ->find();
        $user['avatar'] = UrlServer::getFileUrl($user['avatar']);
        $user['today_sign'] = 0;

        //今天签到记录
        $today_sign = UserSign::where(['del' => 0, 'user_id' => $user_id])
            ->whereTime('sign_time', 'today')
            ->find();

        //昨天签到记录
        $yester_sign = UserSign::where(['del' => 0, 'user_id' => $user_id])
            ->whereTime('sign_time', 'yesterday')
            ->find();

        //今天是否已签到
        $today_sign && $user['today_sign'] = 1;

        //昨天没签到，则签到中断重新计算连续天数
        if (!$yester_sign) {
            $today_start = strtotime(date('Y-m-d') . '00:00:00');
            UserSign::where(['del' => 0, 'user_id' => $user_id])
                ->where('sign_time', '<', $today_start)
                ->update(['del' => 1, 'update_time' => time()]);
        }

        //签到规则
        $sign_list = SignDaily::where(['del' => 0])
            ->order('type asc,days asc')
            ->column('*', 'days');

        // 根据签到奖励计算 (今天签到赠送的积分,累计签到天数,签到天列表)
        $data = self::formatSignList($user_id, $sign_list);
        // 累计签到天数
        $user['days'] = $data['total_sign_days'];
        // 赚积分描述
        $integral_tips = self::getInegralTips($user_id, $user['today_sign'], $data['today_sign_integral']);

        return [
            'user' => $user,
            'sign_list' => $data['days_list'],
            'integral_tips' => $integral_tips,
        ];
    }


    /**
     * @notes 计算签到信息
     * @param $user_id
     * @param $sign_list
     * @return array
     * @author 段誉
     * @date 2022/2/17 18:23
     */
    public static function formatSignList($user_id, $sign_list)
    {
        // 今天签到赠送的积分
        $today_sign_integral = 0;
        // 累计签到天数
        $total_sign_days = 0;
        // 签到天
        $days_list = [];

        if (empty($sign_list)) {
            return [
                'days_list' => $days_list,
                'today_sign_integral' => $today_sign_integral,
                'total_sign_days' => $total_sign_days
            ];
        }

        // 第一次签到规则
        $start_sign = current($sign_list);
        // 最后一次签到规则
        $end_sign = end($sign_list);

        // 每天赠送的积分
        $everyday_award_integral = 0;
        $start_sign['integral_status'] && $everyday_award_integral = $start_sign['integral'];

        // 累计签到的总天数
        $total_sign_days = UserSign::where(['del' => 0, 'user_id' => $user_id])
            ->order('id desc')
            ->value('days');

        for ($days = 1; $days <= $end_sign['days']; $days++) {
            $send_integral = $everyday_award_integral;
            // 连接签到赠送的积分
            if (isset($sign_list[$days]) && $sign_list[$days]['integral_status']) {
                $send_integral = $everyday_award_integral + $sign_list[$days]['integral'];
            }
            // 合并数据
            $days_list[$days] = [
                'days' => $days,
                'status' => 0,
                'integral' => $send_integral,
                'growth' => 0,
            ];
            // 更新签到天数之前的签到状态
            if ($days === $total_sign_days) {
                $today_sign_integral = $send_integral;// 今天签到获得的积分
                for ($sign_day = $days; $sign_day >= 1; $sign_day--) {
                    $days_list[$sign_day]['status'] = 1;
                }
            }
            // 如果连续签到天数大于总天数，则全部标记为已签到状态
            if ($total_sign_days > $end_sign['days']) {
                $days_list[$days]['status'] = 1;
            }
        }

        return [
            'days_list' => array_values($days_list),
            'today_sign_integral' => $today_sign_integral,
            'total_sign_days' => empty($total_sign_days) ? 0 : $total_sign_days
        ];
    }



    /**
     * @notes 赚积分描述
     * @param $user_id
     * @param $today_sign_status
     * @param $today_sign_integral
     * @return array
     * @author 段誉
     * @date 2022/2/17 18:29
     */
    public static function getInegralTips($user_id, $today_sign_status, $today_sign_integral)
    {
        //赚积分
        $tips[] = [
            'name' => '每日签到',
            'status' => $today_sign_status,
            'type' => 1,//类型，主要用前端显示图标
            'image' => UrlServer::getFileUrl('/static/common/image/default/sign.png')
        ];

        $open_award = ConfigServer::get('order_award', 'open_award', 0);
        //消费送积分
        if ($open_award > 0) {
            $order_award = AccountLog::where(['user_id' => $user_id, 'source_type' => AccountLog::consume_award_integral])
                ->whereDay('create_time')
                ->findOrEmpty();
            $tips[] = [
                'name' => '消费送积分',
                'status' => $order_award->isEmpty() ? 0 : 1,
                'type' => 2,
                'image' => UrlServer::getFileUrl('/static/common/image/default/place_order.png')
            ];
        }
        return $tips;
    }


    /**
     * @notes 签到
     * @param $user_id
     * @return array|false
     * @author 段誉
     * @date 2022/2/17 17:48
     */
    public static function sign($user_id)
    {
        Db::startTrans();
        try {
            // 连续签到天数
            $sign_list = SignDaily::where(['del' => 0, 'type' => 2])
                ->order('type asc,days asc')
                ->column('*', 'days');

            $now = time();
            $award_integral = 0;                    //签到赠送的总积分 (每天签到奖励 + 连续签到奖励)
            $award_growth = 0;                      //签到赠送的成长值 (每天签到奖励 + 连续签到奖励)
            $continuous_integral = 0;               //连续签到积分
            $continuous_growth = 0;                 //连续签到成长值

            // 每天签到的奖励
            $everyday_sign = SignDaily::where(['del' => 0, 'type' => 1])->findOrEmpty();
            // 每天签到奖励
            if ($everyday_sign) {
                if ($everyday_sign['integral_status'] && $everyday_sign['integral'] > 0) {
                    $award_integral += $everyday_sign['integral'];
                }
                if ($everyday_sign['growth_status'] && $everyday_sign['growth'] > 0) {
                    $award_growth += $everyday_sign['growth'];
                }
            }

            // 签到记录
            $last_sign = UserSign::where(['del' => 0, 'user_id' => $user_id])
                ->order('id desc')
                ->findOrEmpty();

            // 无签到记录找是否有连续签到1天的奖励,有签到记录则找(上次签到天数+1天)的连续奖励
            if ($last_sign->isEmpty()) {
                $sign_day = 1;
            } else {
                $sign_day = $last_sign['days'] + 1;
            }

            // 累计签到天数,计算连续签到奖励
            $continuous_sign = $sign_list[$sign_day] ?? [];
            if ($continuous_sign) {
                if ($continuous_sign['integral_status'] && $continuous_sign['integral'] > 0) {
                    $award_integral += $continuous_sign['integral'];
                    $continuous_integral = $continuous_sign['integral'];
                }
                if ($continuous_sign['growth_status'] && $continuous_sign['growth'] > 0) {
                    $award_growth += $continuous_sign['growth'];
                    $continuous_growth = $continuous_sign['growth'];
                }
            }

            UserSign::create([
                'user_id' => $user_id,
                'days' => $sign_day,
                'integral' => $everyday_sign['integral_status'] ? $everyday_sign['integral'] : 0,
                'growth' => $everyday_sign['growth_status'] ? $everyday_sign['growth'] : 0,
                'continuous_integral' => $continuous_integral,
                'continuous_growth' => $continuous_growth,
                'sign_time' => $now,
            ]);

            if ($award_integral) {
                User::where(['del' => 0, 'id' => $user_id])->inc('user_integral', $award_integral)->update();
                AccountLogLogic::AccountRecord($user_id, $award_integral, 1, AccountLog::sign_in_integral);
            }
            if ($award_growth) {
                User::where(['del' => 0, 'id' => $user_id])->inc('user_growth', $award_growth)->update();
                AccountLogLogic::AccountRecord($user_id, $award_growth, 1, AccountLog::sign_give_growth);
            }

            Db::commit();
            return [
                'days' => $sign_day,
                'integral' => $award_integral,
                'growth' => $award_growth
            ];
        } catch (\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }



    /**
     * @notes 获取签到规则
     * @return array|int|mixed|string|null
     * @author 段誉
     * @date 2022/2/17 14:47
     */
    public static function getRule()
    {
        return ['rule' => ConfigServer::get('sign_rule', 'instructions', '')];
    }

}