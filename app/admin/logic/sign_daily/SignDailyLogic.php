<?php


namespace app\admin\logic\sign_daily;


use app\common\basics\Logic;
use app\common\model\sign_daily\SignDaily;
use app\common\model\sign_daily\UserSign;
use app\common\server\ConfigServer;
use app\common\server\UrlServer;

/**
 * 签到逻辑
 * Class SignDailyLogic
 * @package app\admin\logic\sign_daily
 */
class SignDailyLogic extends Logic
{

    /**
     * @notes 连续签到列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/2/17 14:30
     */
    public static function lists()
    {
        $where[] = ['type', '=', 2];
        $where[] = ['del', '=', 0];
        $count = SignDaily::where($where)->count();
        $lists = SignDaily::where($where)->select();
        foreach ($lists as $key => $sign) {
            $tips = '';
            if (1 == $sign['integral_status'] && $sign['integral'] > 0) {
                $tips .= '赠送' . $sign['integral'] . '积分；';
            }
            if (1 == $sign['growth_status'] && $sign['growth'] > 0) {
                $tips .= '赠送' . $sign['growth'] . '成长值；';
            }
            $lists[$key]['award_tips'] = $tips;
        }
        return ['count' => $count, 'lists' => $lists];
    }


    /**
     * @notes 签到记录
     * @param $get
     * @return array
     * @author 段誉
     * @date 2022/2/17 14:31
     */
    public static function record($get)
    {
        $where = [];
        $where[] = ['us.del', '=', 0];
        $where[] = ['u.del', '=', 0];
        if (isset($get['keyword']) && $get['keyword']) {
            $where[] = [$get['type'], 'like', '%' . $get['keyword'] . '%'];
        }

        $field = 'us.user_id,sn,nickname,avatar,mobile,sex,u.create_time ,days,integral,growth,
            continuous_integral, continuous_growth,sign_time,mobile,us.sign_time';

        $count = UserSign::alias('us')
            ->join('user u', 'u.id = us.user_id')
            ->where($where)
            ->count();

        $lists = UserSign::alias('us')
            ->join('user u', 'u.id = us.user_id')
            ->where($where)
            ->field($field)
            ->order('us.id desc')
            ->page($get['page'], $get['limit'])
            ->select();

        foreach ($lists as &$item) {
            $item['sign_time'] = date('Y-m-d H:i:s', $item['sign_time']);
            $item['avatar'] = UrlServer::getFileUrl($item['avatar']);
            if ($item['sex'] == 1) {
                $item['sex'] = '男';
            } elseif ($item['sex'] == 2) {
                $item['sex'] = '女';
            } else {
                $item['sex'] = '未知';
            }
        }
        return ['count' => $count, 'lists' => $lists];
    }


    /**
     * @notes 获取每日签到规则
     * @return array
     * @author 段誉
     * @date 2022/2/17 14:31
     */
    public static function getSignRule()
    {
        $data = SignDaily::where(['type' => 1])->findOrEmpty();
        $config = [
            'instructions' => ConfigServer::get('sign_rule', 'instructions'),
            'dailySign' => $data
        ];
        return $config;
    }


    /**
     * @notes 设置每日签到规则
     * @param $post
     * @return bool
     * @author 段誉
     * @date 2022/2/17 14:31
     */
    public static function setSignRule($post)
    {
        try {
            $rule = SignDaily::where(['del' => 0, 'type' => 1])->findOrEmpty();

            $data = [
                'integral' => empty($post['integral']) ? 0 : $post['integral'],
                'growth' => empty($post['growth']) ? 0 : $post['growth'],
                'integral_status' => $post['integral_status'],
                'growth_status' => $post['growth_status'],
            ];

            if ($rule->isEmpty()) {
                $data['type'] = 1;
                $data['days'] = 0;
                SignDaily::create($data);
            } else {
                SignDaily::update($data, ['id' => $rule['id']]);
            }

            ConfigServer::set('sign_rule', 'instructions', $post['instructions']);

            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 添加连续签到奖励
     * @param $post
     * @return SignDaily|\think\Model
     * @author 段誉
     * @date 2022/2/17 14:31
     */
    public static function add($post)
    {
        return SignDaily::create(
            [
                'type' => '2',
                'days' => $post['days'],
                'integral' => $post['integral'],
                'integral_status' => $post['integral_status'],
                'growth' => $post['growth'],
                'growth_status' => $post['growth_status'],
            ]
        );
    }


    /**
     * @notes 编辑连续签到奖励
     * @param $post
     * @return SignDaily
     * @author 段誉
     * @date 2022/2/17 14:31
     */
    public static function edit($post)
    {
        return SignDaily::update([
            'id' => $post['id'],
            'days' => $post['days'],
            'integral' => $post['integral'],
            'integral_status' => $post['integral_status'],
            'growth' => $post['growth'],
            'growth_status' => $post['growth_status'],
        ]);
    }


    /**
     * @notes 删除连续签到奖励
     * @param $id
     * @return SignDaily
     * @author 段誉
     * @date 2022/2/17 14:32
     */
    public static function del($id)
    {
        return SignDaily::update(['del' => 1, 'id' => $id]);
    }


    /**
     * @notes 获取连续签到奖励详情
     * @param $id
     * @return array|\think\Model
     * @author 段誉
     * @date 2022/2/17 14:32
     */
    public static function getSignDaily($id)
    {
        return SignDaily::findOrEmpty($id);
    }


}