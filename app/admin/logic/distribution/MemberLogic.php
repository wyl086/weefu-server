<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\admin\logic\distribution;

use app\common\basics\Logic;
use app\common\model\user\User;
use app\common\model\user\UserLevel;
use app\common\server\UrlServer;
use app\common\model\WithdrawApply;
use app\common\model\distribution\DistributionOrderGoods;
use app\common\model\distribution\DistributionMemberApply;
use think\facade\Db;

class MemberLogic extends Logic
{
    public static function memberLists($get)
    {
        // 关键词
        $where[] = ['is_distribution', '=', 1];
        if (!empty($get['search_key']) && !empty($get['keyword'])) {
            $where[] = [$get['search_key'], '=', trim($get['keyword'])];
        }
        //分销状态
        if (isset($get['freeze_distribution']) && $get['freeze_distribution'] != '') {
            $where[] = ['freeze_distribution', '=', $get['freeze_distribution']];
        }

        $user = new User();
        $count = $user->where($where)->count();

        $lists = $user
            ->where($where)
            ->page($get['page'], $get['limit'])
            ->append(['fans', 'distribution_order', 'leader'])
            ->hidden(['password,pay_password,salt'])
            ->order('id desc')
            ->select()
            ->toArray();

        foreach ($lists as &$item) {
            $item['avatar'] = UrlServer::getFileUrl($item['avatar']);

            $item['distribution_num'] = $item['distribution_order']['num'] ?? 0;//分销订单数
            $item['distribution_amount'] = $item['distribution_order']['amount'] ?? 0;//分销订单金额
            $item['distribution_money'] = $item['distribution_order']['money'] ?? 0;//分销佣金
        }
        return ['count' => $count, 'lists' => $lists];
    }

    public static function addMember($post)
    {
        // 根据会员编号查询用户
        $user = User::field('id,sn,is_distribution,distribution_add_remarks,del')
            ->where(['sn'=>$post['sn']])->findOrEmpty();
        // 校验用户
        if ($user->isEmpty()) { return '该用户不存在!'; }
        $user = $user->toArray();
        if ($user['del'] === 1) { return '该用户已被删除!'; }
        if ($user['is_distribution']) { return '该用户已是分销会员,无需重复添加'; }
        $result =  User::where(['id' => (int)$user['id']])->update([
            'is_distribution'          => 1,
            'distribution_add_remarks' => $post['remarks'] ?? '',
            'update_time' => time()
        ]);
        return $result ? true : '添加失败';
    }

    public static function getMemberInfo($get)
    {
        $user_id = $get['id'];
        $user = User::alias('u')
            ->field('u.*,u.sn as user_sn')
            ->leftJoin('distribution_order_goods d', 'd.user_id = u.id')
            ->where('u.id', $user_id)
            ->append(['distribution_order'])
            ->hidden(['password', 'pay_password', 'salt'])
            ->find();

        $user['distribution_text'] = '否';
        if ($user['is_distribution'] == 1) {
            $user['distribution_text'] = '是';
        }

        //上级编号
        $user['first_leader_sn'] = User::where('id', $user['first_leader'])->value('sn');
        //直推会员数
        $user['first_fans'] = User::where(['first_leader' => $user_id, 'del' => 0])->count();
        // 已提现金额
        $have_withdraw = WithdrawApply::where(['status' => WithdrawApply::STATUS_SUCCESS, 'user_id' => $user_id])
            ->sum('money');

        $user['distribution_num'] = $user['distribution_order']['num'] ?? 0;//分销订单数
        $user['distribution_amount'] = $user['distribution_order']['amount'] ?? 0;//分销订单金额
        $user['distribution_money'] = $user['distribution_order']['money'] ?? 0;//分销佣金
        $user['have_withdraw'] = $have_withdraw;//已提现金额
        return $user;
    }

    public static function getFansLists($get)
    {
        $user_id = $get['id'];
        $where = [];
        if (!empty($get['search_key']) && !empty($get['keyword'])) {
            $keyword = $get['keyword'];
            $where[] = [$get['search_key'], 'like', '%' . $keyword . '%'];
        }

        $fans_type = $get['type'] ?? 'all';
        if ($fans_type == 'all') {
            $where[] = ['first_leader|second_leader|third_leader', '=', $user_id];
        } else {
            $where[] = [$fans_type, '=', $user_id];
        }

        $user = new User();
        $count = $user
            ->where($where)
            ->append(['fans', 'distribution_order'])
            ->hidden(['password,pay_password,salt'])
            ->count();

        $lists = $user
            ->where($where)
            ->append(['fans', 'distribution_order'])
            ->hidden(['password,pay_password,salt'])
            ->page($get['page'], $get['limit'])
            ->select()->toArray();

        // 用户等级列表
        $user_level = UserLevel::where(['del' => 0])->column('name', 'id');
        // 提取所有上级id
        $leader_ids = array_column($lists, 'first_leader');
        // 所有上级列表
        $leaders = User::where('id', 'in', $leader_ids)
            ->column('sn,nickname,mobile,level', 'id');

        foreach ($lists as &$item) {
            $item['avatar'] = UrlServer::getFileUrl($item['avatar']);
            $item['leader'] = $leaders[$item['first_leader']] ?? [];
            if (!empty($item['leader'])) {
                $leader_level = $item['leader']['level'] ?? 0;
                $item['leader']['level'] = $user_level[$leader_level] ?? '无等级';
            }
            $item['distribution_num'] = $item['distribution_order']['num'] ?? 0;//分销订单数
            $item['distribution_amount'] = $item['distribution_order']['amount'] ?? 0;//分销订单金额
            $item['distribution_money'] = $item['distribution_order']['money'] ?? 0;//分销佣金
        }

        return ['count' => $count, 'lists' => $lists];
    }

    public static function getEarningsDetail($get)
    {
        $user_id = $get['id'];
        $where = [];
        $where[] = ['d.user_id', '=', $user_id];
        $where[] = ['d.status', '=', DistributionOrderGoods::STATUS_SUCCESS];

        //记录时间
        if (isset($get['start_time']) && $get['start_time'] != '') {
            $where[] = ['d.create_time', '>=', strtotime($get['start_time'])];
        }
        if (isset($get['end_time']) && $get['end_time'] != '') {
            $where[] = ['d.create_time', '<=', strtotime($get['end_time'])];
        }

        $count = DistributionOrderGoods::alias('d')
            ->field('d.id as distribution_id, d.sn, o.order_sn, d.money, d.create_time')
            ->join('order_goods og', 'og.id = d.order_goods_id')
            ->join('order o', 'o.id = og.order_id')
            ->where($where)
            ->count();

        $lists = DistributionOrderGoods::alias('d')
            ->field('d.id as distribution_id, d.sn, o.order_sn, d.money, d.create_time')
            ->join('order_goods og', 'og.id = d.order_goods_id')
            ->join('order o', 'o.id = og.order_id')
            ->where($where)
            ->page($get['page'], $get['limit'])
            ->select();

        foreach ($lists as &$item) {
            $item['type'] = '分销佣金';
        }

        return ['count' => $count, 'lists' => $lists];
    }

    public static function getLeaderInfo($user_id)
    {
        $first_leader = User::alias('u')
            ->field('u1.nickname,u1.sn')
            ->join('user u1', 'u1.id = u.first_leader')
            ->where('u.id', $user_id)
            ->find();

        $leader_data = '无';
        if ($first_leader) {
            $leader_data = $first_leader['nickname'] . '(' . $first_leader['sn'] . ')';
        }
        return $leader_data;
    }

    public static function updateRelation($post)
    {
        Db::startTrans();
        try{
            $user_id = $post['user_id'];
            $referrer_sn =  $post['referrer_sn'];

            //清空上级
            $data = [
                'first_leader' => 0,
                'second_leader' => 0,
                'third_leader' => 0,
                'ancestor_relation' => '',
            ];
            $my_first_leader = 0;
            $my_second_leader = 0;
            $my_ancestor_relation = '';

            if ($post['change_type'] == 'appoint'){
                //指定上级
                $my_leader = User::where(['sn' => $referrer_sn])->findOrEmpty();

                //更新我的第一上级、第二上级、第三上级、关系链
                $my_first_leader = $my_leader['id'];
                $my_second_leader = $my_leader['first_leader'];
                $my_third_leader = $my_leader['second_leader'];
                $my_ancestor_relation = trim("{$my_first_leader},{$my_leader['ancestor_relation']}", ',');
                $data = [
                    'first_leader' => $my_first_leader,
                    'second_leader' => $my_second_leader,
                    'third_leader' => $my_third_leader,
                    'ancestor_relation' => $my_ancestor_relation,
                ];
            }
            // 更新我的上级、上上级、上上上级、关系链
            User::where(['id' => $user_id])->update($data);

            //更新我向下一级的第二上级、第三上级
            $data = [
                'second_leader' => $my_first_leader,
                'third_leader' => $my_second_leader,
            ];
            User::where(['first_leader' => $user_id])->update($data);

            //更新我向下二级的第三级
            $data = [
                'third_leader' => $my_first_leader,
            ];
            User::where(['second_leader' => $user_id])->update($data);

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
                    'ancestor_relation' => str_replace($post['user_id'], $replace_ancestor_relation, $item['ancestor_relation'])
                ];
            }
            // 批量更新
            (new User())->saveAll($updateData);

            Db::commit();
            return true;
        } catch (Exception $e){
            Db::rollback();
            return $e->getMessage();
        }
    }

    public static function freeze($post)
    {
        $user = User::where('id', $post['id'])->find();
        $user->freeze_distribution = 1;
        if ($post['type'] == 'unfreeze'){
            $user->freeze_distribution = 0;
        }
        return $user->save();
    }

    public static function del($post)
    {
        $user = User::find($post['id']);
        $user->is_distribution = 0;
        $user->update_time = time();
        return $user->save();
    }

    /**
     * 待审核会员列表
     */
    public static function auditLists($get)
    {
        $where = [];
        if (!empty($get['search_key']) && !empty($get['keyword'])) {
            $keyword = $get['keyword'];
            if ($get['search_key'] == 'mobile') {
                $where[] = ['u.mobile', 'like', '%' . $keyword . '%'];
            } else {
                $where[] = [$get['search_key'], 'like', '%' . $keyword . '%'];
            }
        }
        //审核状态
        if (isset($get['status']) && $get['status'] != '') {
            $where[] = ['status', '=', $get['status']];
        }


        $field = [
            'a.*', 'u.sn', 'u.nickname', 'u.mobile', 'u.level', 'u.sex', 'a.reason',
            'u.create_time' => 'register_time', 'u.avatar', 'u.first_leader'
        ];

        $count = DistributionMemberApply::alias('a')
            ->join('user u', 'u.id = a.user_id')
            ->where($where)
            ->count();

        $lists = DistributionMemberApply::alias('a')
            ->field($field)
            ->join('user u', 'u.id = a.user_id')
            ->order('a.id desc')
            ->page($get['page'], $get['limit'])
            ->where($where)
            ->select()
            ->toArray();

        $user_level = UserLevel::where(['del' => 0])->column('name', 'id');

        $leader_ids = array_column($lists, 'first_leader');
        $leaders = User::where('id', 'in', $leader_ids)
            ->column('sn,nickname,mobile,level', 'id');

        foreach ($lists as &$item) {
            $item['level'] = $user_level[$item['level']] ?? '无等级';
            $item['sex'] = self::getSexText($item['sex']);
            $item['register_time'] = date('Y-m-d H:i:s', $item['register_time']);
            $item['status_text'] = DistributionMemberApply::getApplyStatus($item['status']);
            $item['leader'] = $leaders[$item['first_leader']] ?? [];
            $item['avatar'] = UrlServer::getFileUrl($item['avatar']);
            if (!empty($item['leader'])) {
                $leader_level = $item['leader']['level'] ?? 0;
                $item['leader']['level'] = $user_level[$leader_level] ?? '无等级';
            }
        }
        return ['count' => $count, 'lists' => $lists];
    }

    public static function getSexText($value)
    {
        switch ($value) {
            case 1:
                return '男';
            case 2:
                return '女';
            default:
                return '未知';
        }
    }

    public static function auditPass($post)
    {
        Db::startTrans();
        try {
            $apply = DistributionMemberApply::where('id', $post['id'])->find();
            $apply->status = DistributionMemberApply::STATUS_AUDIT_SUCCESS;
            $apply->update_time = time();
            $apply->save();

            $user = User::where('id', $apply['user_id'])->find();
            $user->is_distribution = 1;
            $user->save();

            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    public static function auditRefuse($post)
    {
        $apply = DistributionMemberApply::where('id', $post['id'])->find();
        $apply->status = DistributionMemberApply::STATUS_AUDIT_ERROR;
        $apply->denial_reason = $post['denial_reason'] ?? '';
        $apply->save();
        return true;
    }
}