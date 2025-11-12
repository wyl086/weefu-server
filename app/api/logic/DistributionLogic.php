<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\api\logic;


use app\common\enum\DistributionOrderGoodsEnum;
use app\common\enum\NoticeEnum;
use app\common\model\distribution\Distribution;
use app\common\model\distribution\DistributionLevel;
use app\common\server\ConfigServer;
use app\common\model\user\UserDistribution;
use app\common\model\user\User;
use app\common\model\distribution\DistributionMemberApply;
use app\common\model\distribution\DistributionOrderGoods;
use app\common\server\AreaServer;
use app\common\server\JsonServer;
use app\common\server\UrlServer;
use app\common\basics\Logic;
use app\common\logic\AccountLogLogic;
use app\common\model\AccountLog;
use think\Exception;
use think\facade\Db;

class DistributionLogic extends Logic
{
    /**
     * Notes: 根据后台设置返回当前生成用户的分销会员状态(设置了全员分销,新生成的用户即为分销会员)
     * @author 段誉(2021/4/7 14:48)
     * @return int
     */
    public static function isDistributionMember()
    {
        $is_distribution = 0;
        //分销会员申请--1,申请分销; 2-全员分销;
        $distribution = ConfigServer::get('distribution', 'member_apply', 1);
        if ($distribution == 2) {
            $is_distribution = 1;
        }
        return $is_distribution;
    }

    /**
     * Desc: 生成用户扩展表
     * @param $user_id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function createUserDistribution($user_id)
    {
        $user_distribution = UserDistribution::where(['user_id' => $user_id])->find();

        if ($user_distribution) {
            return true;
        }

        $data = [
            'user_id' => $user_id,
            'distribution_order_num' => 0,
            'distribution_money' => 0,
            'fans' => 0,
            'create_time' => time(),
        ];
        UserDistribution::create($data);
        return true;
    }


    /**
     * 申请分销会员
     */
    public static function apply($post)
    {
            $time = time();
            $data = [
                'user_id'       => $post['user_id'],
                'real_name'     => $post['real_name'],
                'mobile'        => $post['mobile'],
                'province'      => $post['province'],
                'city'          => $post['city'],
                'district'      => $post['district'],
                'reason'        => $post['reason'],
                'status'        => 0, // 待审核
                'create_time'   => $time,
                'update_time'   => $time,
            ];
            return DistributionMemberApply::create($data);
    }

    /**
     * 最新分销申请详情
     */
    public static function applyDetail($userId)
    {
        $result = DistributionMemberApply::field(['real_name','mobile','province', 'city', 'district', 'reason', 'denial_reason', 'status'])
            ->where('user_id', $userId)
            ->order('id', 'desc')
            ->findOrEmpty();

        if ($result->isEmpty()) {
            return [];
        }
        $result = $result->toArray();
        $result['province'] = AreaServer::getAddress($result['province']);
        $result['city'] = AreaServer::getAddress($result['city']);
        $result['district'] = AreaServer::getAddress($result['district']);

        switch ($result['status']) {
            case 0:
                $result['status_str'] = '已提交，等待客服审核...';
                break;
            case 1:
                $result['status_str'] = '已审核通过';
                break;
            case 2:
                $result['status_str'] = '审核失败，请重新提交审核';
                break;
        }
        return $result;
    }

    /**
     * 分销主页
     */
    public static function index($userId)
    {
        // 自身及上级信息
        $user_info = self::myLeader($userId);
        // 粉丝数(一级/二级)
        $fans = User::where([
            ['first_leader|second_leader', '=', $userId],
            ['del', '=', 0]
        ])->count();

        //今天的预估收益(待返佣)
        $today_earnings = DistributionOrderGoods::whereDay('create_time')
            ->where(['status' => 1, 'user_id' => $userId])
            ->sum('money');

        //本月预估收益(待返佣)
        $month_earnings = DistributionOrderGoods::whereMonth('create_time')
            ->where(['status' => 1, 'user_id' => $userId])
            ->sum('money');

        //累计收益(已结算)
        $history_earnings = DistributionOrderGoods::where(['status' => 2, 'user_id' => $userId])
            ->sum('money');

        // 用户分销会员等级
        $levelId = Distribution::where('user_id', $userId)->value('level_id');
        $levelName = DistributionLevel::getLevelNameTwo($levelId);

        $data = [
            'user' => $user_info['user'],
            'leader' => $user_info['leader'],
            'fans' => $fans,
            'able_withdrawal' => $user_info['user']['earnings'],//可提现佣金
            'today_earnings' => round($today_earnings, 2),//今天预估收益
            'month_earnings' => round($month_earnings, 2),//本月预估收益
            'history_earnings' => round($history_earnings, 2),//累计收益
            'level_name' => $levelName
        ];
        return $data;
    }

    /***
     * 获取自身及上级信息
     */
    public static function myLeader($userId)
    {
        $field = 'nickname,avatar,is_distribution,mobile,first_leader,distribution_code,earnings';

        $user = User::field($field)->where(['id' => $userId, 'del'=>0])->findOrEmpty();

        $first_leader = User::field('nickname,mobile')
            ->where(['id' => $user['first_leader'], 'del'=>0])
            ->findOrEmpty();

        $user['avatar'] = UrlServer::getFileUrl($user['avatar']);
        return [
            'user' => $user,
            'leader' => $first_leader,
        ];
    }

    /**
     * 填写邀请码
     */
    public static function code($post)
    {
        try {
            Db::startTrans();

            $firstLeader = User::field(['id', 'first_leader', 'second_leader', 'third_leader', 'ancestor_relation','user_integral'])
                ->where(['distribution_code' => $post['code']])
                ->findOrEmpty();
            if($firstLeader->isEmpty()) {
                throw new \think\Exception('无效的邀请码');
            }

            // 上级
            $first_leader_id = $firstLeader['id'];
            // 上上级
            $second_leader_id = $firstLeader['first_leader'];
            // 上上上级
            $third_leader_id = $firstLeader['second_leader'];
            // 拼接关系链
            $firstLeader['ancestor_relation'] = $firstLeader['ancestor_relation'] ? : ''; // 清空null值及0
            $my_ancestor_relation = $first_leader_id. ',' . $firstLeader['ancestor_relation'];
            // 去除两端逗号
            $my_ancestor_relation = trim($my_ancestor_relation, ',');

            $user = User::findOrEmpty($post['user_id']);
            // 旧关系链
            if (!empty($user->ancestor_relation)) {
                $old_ancestor_relation = $user->id . ',' .$user->ancestor_relation;
            } else {
                $old_ancestor_relation = $user->id;
            }

            $data = [
                'first_leader' => $first_leader_id,
                'second_leader' => $second_leader_id,
                'third_leader' => $third_leader_id,
                'ancestor_relation' => $my_ancestor_relation,
                'update_time' => time()
            ];

            // 更新当前用户的分销关系
            User::where(['id' => $post['user_id']])->update($data);

            //更新当前用户下级的分销关系
            $data = [
                'second_leader' => $first_leader_id,
                'third_leader' => $second_leader_id,
                'update_time'  => time()
            ];
            User::where(['first_leader' => $post['user_id']])->update($data);

            //更新当前用户下下级的分销关系
            $data = [
                'third_leader' => $first_leader_id,
                'update_time'  => time()
            ];
            User::where(['second_leader' => $post['user_id']])->update($data);

            //更新当前用户所有后代的关系链
            $posterityArr = User::field('id,ancestor_relation')
                ->whereFindInSet('ancestor_relation', $post['user_id'])
                ->select()
                ->toArray();
            $updateData = [];
            $replace_ancestor_relation = $post['user_id'] . ','. $my_ancestor_relation;
            foreach($posterityArr as $item) {
                $updateData[] = [
                    'id' => $item['id'],
                    'ancestor_relation' => trim(str_replace($old_ancestor_relation, $replace_ancestor_relation, $item['ancestor_relation']), ',')
                ];
            }
            // 批量更新
            (new User())->saveAll($updateData);

            //邀请会员赠送积分
            // $invited_award_integral = ConfigServer::get('marketing','invited_award_integral',0);
            // if($invited_award_integral > 0){
            //     // 增加上级积分
            //     $firstLeader->user_integral += (int)$invited_award_integral;
            //     $firstLeader->save();
            //     // 增加上级积分变动记录
            //     AccountLogLogic::AccountRecord($firstLeader['id'],$invited_award_integral,1, AccountLog::invite_add_integral);
            // }

            //通知用户
            event('Notice', [
                'scene' => NoticeEnum::INVITE_SUCCESS_NOTICE,
                'params' => [
                    'user_id' => $first_leader_id,
                    'lower_id' => $post['user_id'],
                    'join_time' => date('Y-m-d H:i:s', time())
                ]
            ]);

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * 分销订单
     */
    public static function order($get)
    {
        $where[] = ['d.user_id', '=', $get['user_id']];

        if (isset($get['status']) && in_array($get['status'], [1,2,3])) {
            $where[] = ['d.status', '=', $get['status']];
        }

        $field = 'd.create_time, d.money, d.goods_num, d.status, d.status as statusDesc, o.order_sn, og.total_pay_price as pay_price, g.image as goods_image, g.name as goods_name, gi.spec_value_str';

        $count = DistributionOrderGoods::alias('d')
            ->where($where)
            ->count();

        $lists = DistributionOrderGoods::alias('d')
            ->field($field)
            ->leftJoin('order_goods og', 'og.id = d.order_goods_id')
            ->leftJoin('order o', 'o.id = og.order_id')
            ->leftJoin('goods g', 'og.goods_id=g.id')
            ->leftJoin('goods_item gi', 'og.item_id=gi.id')
            ->where($where)
            ->order('d.create_time desc')
            ->page($get['page_no'], $get['page_size'])
            ->select()
            ->toArray();

        foreach ($lists as &$item) {
            $item['goods_image'] = empty($item['goods_image']) ? '' : UrlServer::getFileUrl($item['goods_image']);
        }

        $data = [
            'list' => $lists,
            'page' => $get['page_no'],
            'size' => $get['page_size'],
            'count' => $count,
            'more' => is_more($count, $get['page_no'], $get['page_size'])
        ];
        return $data;
    }

    /**
     * 月度账单
     */
    public static function monthBill($get)
    {
        $field = [
            "FROM_UNIXTIME(d.create_time,'%Y年%m月') as date",
            "FROM_UNIXTIME(d.create_time,'%Y') as year",
            "FROM_UNIXTIME(d.create_time,'%m') as month",
            'sum(d.money) as total_money',
            'count(d.id) as order_num'
        ];
        $count = DistributionOrderGoods::alias('d')
            ->field($field)
            ->leftJoin('order_goods g', 'g.id = d.order_goods_id')
            ->leftJoin('order o', 'o.id = g.order_id')
            ->where(['d.user_id' => $get['user_id']])
            ->where('d.status', 'in', [1, 2])
            ->group('date')
            ->count();

        $lists = DistributionOrderGoods::alias('d')
            ->field($field)
            ->leftJoin('order_goods g', 'g.id = d.order_goods_id')
            ->leftJoin('order o', 'o.id = g.order_id')
            ->where(['d.user_id' => $get['user_id']])
            ->where('d.status', 'in', [1, 2])
            ->order('d.create_time desc')
            ->page($get['page_no'], $get['page_size'])
            ->group('date')
            ->select()
            ->toArray();

        $data = [
            'list' => $lists,
            'page' => $get['page_no'],
            'size' => $get['page_size'],
            'count' => $count,
            'more' => is_more($count, $get['page_no'], $get['page_size'])
        ];
        return $data;
    }

    /**
     * 月度明细
     */
    public static function monthDetail($get)
    {
        $where[] = ['d.user_id', '=', $get['user_id']];

        $monthStr = $get['year'] . '-' . str_pad($get['month'], 2, '0', STR_PAD_LEFT);

        $field = 'd.create_time, d.money, d.goods_num, d.status, d.status as statusDesc, o.order_sn, og.total_pay_price as pay_price, g.image as goods_image, g.name as goods_name, gi.spec_value_str';

        $count = DistributionOrderGoods::alias('d')
            ->where($where)
            ->whereMonth('d.create_time', $monthStr)
            ->count();

        $lists = DistributionOrderGoods::alias('d')
            ->field($field)
            ->leftJoin('order_goods og', 'og.id = d.order_goods_id')
            ->leftJoin('order o', 'o.id = og.order_id')
            ->leftJoin('goods g', 'og.goods_id=g.id')
            ->leftJoin('goods_item gi', 'og.item_id=gi.id')
            ->where($where)
            ->whereMonth('d.create_time', $monthStr)
            ->order('d.create_time desc')
            ->page($get['page_no'], $get['page_size'])
            ->select()
            ->toArray();

        $data = [
            'list' => $lists,
            'page' => $get['page_no'],
            'size' => $get['page_size'],
            'count' => $count,
            'more' => is_more($count, $get['page_no'], $get['page_size'])
        ];
        return $data;
    }


    /**
     * Desc: 取消订单后更新分销订单为已失效
     * @param $order_id
     * @throws Exception
     * @throws \think\exception\PDOException
     */
    public static function setDistributionOrderFail($order_id)
    {
        //订单取消后更新分销订单为已失效状态
        return Db::name('distribution_order_goods d')
            ->join('order_goods og', 'og.id = d.order_goods_id')
            ->join('order o', 'o.id = og.order_id')
            ->where('o.id', $order_id)
            ->update([
                'd.status' => DistributionOrderGoodsEnum::STATUS_ERROR,
                'd.update_time' => time(),
            ]);
    }

    /**
     * @Notes: 分销佣金列表
     * @Author: 张无忌
     * @param $get
     * @param $user_id
     * @return bool|array
     */
    public static function commission($get, $user_id)
    {
        try {
            $where = [
                ['user_id', '=', $user_id],
                ['source_type', 'in', AccountLog::earnings_change]
            ];

            $model = new AccountLog();
            $count = $model->where($where)->count();
            $lists = $model->field(['id,source_type,change_amount,change_type,create_time'])
                ->where($where)
                ->order('id', 'desc')
                ->page($get['page_no'] ?? 1, $get['page_size'] ?? 20)
                ->select();

            foreach ($lists as &$item) {
                $symbol = $item['change_type'] == 1 ? '+' : '-';
                $item['change_amount'] = $symbol.$item['change_amount'];
            }

            return [
                'list'  => $lists,
                'page'  => $get['page_no']??1,
                'size'  => $get['page_size']??20,
                'count' => $count,
                'more'  => is_more($count, $get['page_no']??1, $get['page_size']??20)
            ];

        } catch (\Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }

    public static function fixAncestorRelation()
    {
        try {
            $userList = User::select()->toArray();
            if (empty($userList)) {
                throw new \Exception('没有用户，无需修复');
            }

            $updateEmptyData = [];
            $updateData = [];
            foreach($userList as $user) {
                $my_ancestor_relation = self::myAncestorRelation($user);
                $updateEmptyData[] = ['id' => $user['id'], 'ancestor_relation' => ''];
                $updateData[] = ['id' => $user['id'], 'ancestor_relation' => $my_ancestor_relation];
            }
            // 先清除所有关系链
            (new User())->saveAll($updateEmptyData);
            // 重新设置关系链
            (new User())->saveAll($updateData);

            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    public static function myAncestorRelation($user)
    {
        if (empty($user['first_leader'])) {
            return '';
        }

        return trim(self::findAncestorRelation($user['first_leader']), ',');
    }

    public static function findAncestorRelation($id, $flag = true)
    {
        static $ancestor_relation = '';
        if ($flag) {
            $ancestor_relation = '';
        }
        $ancestor_relation .= $id . ',';
        $user = User::findOrEmpty($id);
        if (empty($user['first_leader'])) {
            return $ancestor_relation;
        }
        return self::findAncestorRelation($user['first_leader'], false);
    }


    /**
     * @notes 获取背景海报
     * @return array|mixed|string|null
     * @author cjhao
     * @date 2021/11/29 11:35
     */
    public static function getPoster()
    {
        $poster = ConfigServer::get('invite', 'poster', '/images/share/share_user_bg.png');
        $poster = empty($poster) ? $poster : UrlServer::getFileUrl($poster);
        return ['poster'=>$poster];

    }
}