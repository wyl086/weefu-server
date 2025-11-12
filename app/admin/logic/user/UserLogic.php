<?php
namespace app\admin\logic\user;

use app\admin\validate\user\UserValidate;
use app\api\cache\TokenCache;
use app\common\basics\Logic;
use app\common\enum\ClientEnum;
use app\common\enum\OrderEnum;
use app\common\logic\AccountLogLogic;
use app\common\model\AccountLog;
use app\common\model\Session;
use app\common\model\user\User;
use app\common\model\user\UserLevel;
use app\common\model\user\UserTag;
use app\common\server\UrlServer;
use app\common\model\order\Order;
use think\facade\Db;

class UserLogic extends Logic
{
    public static function lists($get)
    {
        $where[] = ['del','=', '0'];

        //查询
        if(isset($get['keyword']) && $get['keyword']){
            $where[] = [$get['keyword_type'],'like', '%'. trim($get['keyword']) . '%'];
        }

        //用户状态
        if(isset($get['disable']) && $get['disable'] !== ''){
            $where[] = ['disable', '=', $get['disable']];
        }

        //等级查询
        if(isset($get['level']) && $get['level'] !== ''){
            $where[] = ['level','=',$get['level']];
        }

        // 标签查询
        if(isset($get['tag']) && $get['tag']){
            $where[] = ['tag_ids','find in set',$get['tag']];
        }

        //注册来源
        if(isset($get['client']) && $get['client']){
            $where[] = ['client','=',$get['client']];
        }

        //消费金额
        if(isset($get['total_amount_start']) && $get['total_amount_start']){
            $where[] = ['total_order_amount','>=',$get['total_amount_start']];
        }
        if(isset($get['total_amount_end']) && $get['total_amount_end']){
            $where[] = ['total_order_amount','<=',$get['total_amount_end']];
        }

        //注册时间
        if(isset($get['start_time']) && $get['start_time']!=''){
            $where[] = ['create_time','>=',strtotime($get['start_time'])];
        }
        if(isset($get['end_time']) && $get['end_time']!=''){
            $where[] = ['create_time','<=',strtotime($get['end_time'])];
        }

        $user_count = User::where($where)->count();

        $user_list = User::where($where)
            ->field('id,sn,nickname,avatar,level,total_order_amount,tag_ids,client,login_time,create_time,user_growth,user_money,earnings,first_leader,disable,user_delete')
            ->page($get['page'],$get['limit'])
            ->order('id desc')
            ->select()
            ->toArray();

        //会员等级
        $user_level = UserLevel::where(['del'=>0])->column('name','id');
        // 会员标签
        $user_tag = UserTag::where(['del'=>0])->column('name', 'id');
        // 注册来源
        $client_list = ClientEnum::getClient(true);

        foreach ($user_list as &$item){
            // $item['nickname'] = htmlspecialchars($item['nickname']);
            // 可提现金额
            $item['earnings'] = empty($item['earnings']) ? 0 :  $item['earnings'];
            // 总资产
            $item['total_amount'] = $item['user_money'] + $item['earnings'];
            // 会员等级
            $item['level_name'] = '暂无等级';
            if(isset($user_level[$item['level']])){
                $item['level_name'] = $user_level[$item['level']];
            }
            // 头像
            if ($item['avatar'] != '/static/common/image/default/user.png') {
                $item['abs_avatar'] = $item['avatar'] ? UrlServer::getFileUrl($item['avatar']) : '';
            } else {
                $item['abs_avatar'] = '/static/common/image/default/user.png';
            }
            // 会员标签
            $item['tag_str'] = '';
            if(!empty($item['tag_ids'])) {
                $tempArr = explode(',',$item['tag_ids']);
                foreach($tempArr as $v) {
                    $item['tag_str'] .= $user_tag[$v] . ',';
                }
                $item['tag_str'] = trim( $item['tag_str'], ',');
            }
            // 注册来源
            $item['client_desc'] = $client_list[$item['client']];

            // 上级推荐人
            $item['first_leader_info'] = User::getUserInfo($item['first_leader']);

            //推荐下级人数
            $item['fans'] = User::where([
                ['first_leader|second_leader', '=', $item['id']],
                ['del', '=', 0]
            ])->count();
        }

        return ['count'=>$user_count , 'lists'=>$user_list];
    }

    public static function setTag($post)
    {
        try{
            User::where([
                ['id', 'in', $post['user_ids']]
            ])->update([
                'tag_ids' => $post['tag_ids'],
                'update_time' => time()
            ]);
            return true;
        }catch(\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    public static function getUser($id)
    {
        $field = [
            'id', 'sn','nickname','avatar','mobile','sex','birthday','tag_ids',
            'remark','user_money','user_growth','user_integral','earnings', 'disable'
        ];
        $user = User::field($field)->where(['del' => 0, 'id' => $id])->findOrEmpty();

        if($user->isEmpty()) {
            return [];
        }
        // 头像
        if($user['avatar']) {
            $user['avatar'] = UrlServer::getFileUrl($user['avatar']);
        }
        // 会员标签
        $user['tag_ids'] = json_encode(explode(',', $user['tag_ids']));

        return $user->toArray();
    }

    public static function edit($post)
    {
        try{
            $data = [
                'id' => $post['id'],
                'nickname' => $post['nickname'],
                'avatar' => clearDomain($post['avatar']),
                'mobile' => $post['mobile'],
                'birthday' => strtotime($post['birthday']),
                'tag_ids' => $post['select'],
                'remark' => $post['remark'],
                'disable' => $post['disable'],
                'update_time' => time()
            ];
            User::update($data);

            if ($post['disable']) {
                $tokens = Session::where(['user_id' => $post['id']])->select()->toArray();
                if(count($tokens) > 0) {
                    foreach ($tokens as $item) {
                        (new TokenCache($item['token']))->del();
                    }
                    Session::where(['user_id' => $post['id']])->update([
                        'expire_time' => time(),
                        'update_time' => time(),
                    ]);
                }
            }

            return true;
        }catch(\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    public static function getInfo($id)
    {
        $field = [
            'id', 'sn', 'nickname', 'avatar', 'birthday', 'sex', 'mobile', 'client',
            'create_time','login_time', 'user_money', 'tag_ids', 'user_growth', 'earnings',
            'first_leader', 'disable', 'user_delete'
        ];
        $user =  User::field($field)->findOrEmpty($id);

        if($user->isEmpty()) {
            return [];
        }
        $user =$user->toArray();
        $orderWhere = [
            'user_id' => $id,
            'del' => 0,
            'pay_status' => OrderEnum::PAY_STATUS_PAID
        ];
        // 上级推荐人
        $user['first_leader_info'] = User::getUserInfo($user['first_leader']);
        // 推荐下级人数
        $user['fans'] = User::where([
            ['first_leader|second_leader', '=', $user['id']],
            ['del', '=', 0],
        ])->count();
        // 总资产
        $user['assets'] = $user['user_money'] + $user['earnings'];
        // 总订单数
        $user['order_num'] = Order::where($orderWhere)->count();
        // 总消费金额
        $user['total_amount'] = Order::where($orderWhere)->sum('order_amount');
        $user['total_amount'] = round($user['total_amount'] ,2);
        // 平均消费单价
        $user['avg_amount'] = Order::where($orderWhere)->avg('order_amount');
        $user['avg_amount'] = round($user['avg_amount'], 2);
        // 头像
        $user['avatar'] = UrlServer::getFileUrl($user['avatar']);
        // 客户端
        $user['client_desc'] = ClientEnum::getClient($user['client']);
        // 会员标签
        $user_tag = UserTag::where(['del'=>0])->column('name', 'id');
        $user['tag_str'] = '';
        if(!empty($user['tag_ids'])) {
            $tempArr = explode(',',$user['tag_ids']);
            foreach($tempArr as $v) {
                $user['tag_str'] .= $user_tag[$v] . ',';
            }
            $user['tag_str'] = trim( $user['tag_str'], ',');
        }

        return $user;
    }

    public static function adjustAccount($post)
    {
        Db::startTrans();
        try{
            $user = User::findOrEmpty($post['id']);
            if($user->isEmpty()) {
                throw new \Exception('用户不存在');
            }
            // 余额调整
            if($post['type'] == 'money') {
                if(empty($post['money'])) {
                    throw new \Exception('请输入调整的余额');
                }
                if(empty($post['money_remark'])) {
                    throw new \Exception('请输入余额备注');
                }
                if($post['money_handle'] == 1) { // 增加
                    $user->user_money = $user->user_money + $post['money'];
                    $user->save();
                    AccountLogLogic::AccountRecord($user['id'], $post['money'],1,AccountLog::admin_add_money, $post['money_remark']);
                }else{
                    $user->user_money = $user->user_money - $post['money'];
                    $user->save();
                    AccountLogLogic::AccountRecord($user['id'], $post['money'],0,AccountLog::admin_reduce_money, $post['money_remark']);
                }
            }

            // 成长值调整
            if($post['type'] == 'growth') {
                if(empty($post['growth'])) {
                    throw new \Exception('请输入调整的成长值');
                }
                if(empty($post['growth_remark'])) {
                    throw new \Exception('请输入成长值备注');
                }
                if($post['growth_handle'] == 1) { // 增加
                    $user->user_growth = $user->user_growth + $post['growth'];
                    $user->save();
                    AccountLogLogic::AccountRecord($user['id'], $post['growth'],1,AccountLog::admin_add_growth, $post['growth_remark']);
                }else{
                    $user->user_growth = $user->user_growth - $post['growth'];
                    $user->save();
                    AccountLogLogic::AccountRecord($user['id'], $post['growth'],0,AccountLog::admin_reduce_growth, $post['growth_remark']);
                }
            }

            // 积分调整
            if($post['type'] == 'integral') {
                if(empty($post['integral'])) {
                    throw new \Exception('请输入调整的积分');
                }
                if(empty($post['integral_remark'])) {
                    throw new \Exception('请输入积分调整备注');
                }
                if($post['integral_handle'] == 1) { // 增加
                    $user->user_integral = $user->user_integral + $post['integral'];
                    $user->save();
                    AccountLogLogic::AccountRecord($user['id'], $post['integral'],1,AccountLog::admin_add_integral, $post['integral_remark']);
                }else{
                    $user->user_integral = $user->user_integral - $post['integral'];
                    $user->save();
                    AccountLogLogic::AccountRecord($user['id'], $post['integral'],0,AccountLog::admin_reduce_integral, $post['integral_remark']);
                }
            }


            // 佣金调整
            if($post['type'] == 'earnings') {
                if(empty($post['earnings'])) {
                    throw new \Exception('请输入调整的佣金');
                }
                if(empty($post['earnings_remark'])) {
                    throw new \Exception('请输入佣金调整备注');
                }
                if($post['earnings_handle'] == 1) { // 增加
                    $user->earnings = $user->earnings + $post['earnings'];
                    $user->save();
                    AccountLogLogic::AccountRecord($user['id'], $post['earnings'],1,AccountLog::admin_inc_earnings, $post['earnings_remark']);
                }else{
                    $user->earnings = $user->earnings - $post['earnings'];
                    $user->save();
                    AccountLogLogic::AccountRecord($user['id'], $post['earnings'],0,AccountLog::admin_reduce_earnings, $post['earnings_remark']);
                }
            }


            Db::commit();
            return true;
        }catch(\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }

    public static function  fans($params)
    {
        $where = [];
        // 一级
        if ($params['type'] == 'one') {
            $where[] = ['first_leader', '=', $params['id']];
        }
        // 二级粉丝
        if ($params['type'] == 'two') {
            $where[] = ['second_leader', '=', $params['id']];
        }
        if(isset($params['keyword']) && !empty($params['keyword'])) {
            $where[] = [$params['field'], 'like', '%'. $params['keyword'] . '%'];
        }
        $lists = User::field('id,sn,nickname,avatar,first_leader')
            ->where($where)
            ->page($params['page'], $params['limit'])
            ->select()
            ->toArray();

        $count = User::field('id,sn,nickname,avatar,first_leader')
            ->where($where)
            ->count();

        foreach($lists as &$item) {
            $item['avatar'] = empty($item['avatar']) ? '' : UrlServer::getFileUrl($item['avatar']);
            $item['first_leader_info'] = User::getUserInfo($item['first_leader']);
            $item['fans'] = User::where([
                ['first_leader|second_leader', '=', $item['id']],
                ['del', '=', 0]
            ])->count();
        }
        return [
            'lists' => $lists,
            'count' => $count,
        ];
    }

    public static function adjustLevel($params)
    {
       try {
            $user = User::findOrEmpty($params['id']);
            if ($user->isEmpty()) {
                throw new \Exception('用户不存在');
            }
           if (User::UserIsDelete($params['id'])) {
               throw new \Exception('用户已注销');
           }
            $user->level = $params['level'];
            $user->remark = $params['remark'];
            $user->save();

           return true;
       } catch(\Exception $e) {
            self::$error = $e->getMessage();
            return false;
       }
    }

    public static function adjustFirstLeader($params)
    {
        Db::startTrans();
        try {
            if (User::UserIsDelete($params['id'])) {
                throw new \Exception('用户已注销');
            }
            switch($params['type']) {
                // 指定推荐人
                case 'assign':
                    $formatData = self::assignFirstLeader($params);
                    break;
                // 设置推荐人为系统,即清空上级
                case 'system':
                    $formatData = self::clearFirstLeader($params);
                    break;
            }

            $user = User::findOrEmpty($params['id']);
            // 旧关系链
            if (!empty($user->ancestor_relation)) {
                $old_ancestor_relation = $user->id . ',' .$user->ancestor_relation;
            } else {
                $old_ancestor_relation = $user->id;
            }


            // 更新当前用户的分销关系
            User::where(['id' => $params['id']])->update($formatData);

            //更新当前用户下级的分销关系
            $data = [
                'second_leader' => $formatData['first_leader'],
                'third_leader' => $formatData['second_leader'],
                'update_time'  => time()
            ];
            User::where(['first_leader' => $params['id']])->update($data);

            //更新当前用户下下级的分销关系
            $data = [
                'third_leader' => $formatData['first_leader'],
                'update_time'  => time()
            ];
            User::where(['second_leader' => $params['id']])->update($data);

            //更新当前用户所有后代的关系链
            $posterityArr = User::field('id,ancestor_relation')
                ->whereFindInSet('ancestor_relation', $params['id'])
                ->select()
                ->toArray();
            $updateData = [];
            $replace_ancestor_relation = $params['id'] . ','. $formatData['ancestor_relation'];
            foreach($posterityArr as $item) {
                $updateData[] = [
                    'id' => $item['id'],
                    'ancestor_relation' => trim(str_replace($old_ancestor_relation, $replace_ancestor_relation, $item['ancestor_relation']), ',')
                ];
            }
            // 批量更新
            (new User())->saveAll($updateData);

            Db::commit();
            return true;
        } catch(\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }

    public static function assignFirstLeader($params)
    {
        if (empty($params['first_id'])) {
            throw new \think\Exception('请选择推荐人');
        }
        $firstLeader = User::field(['id', 'first_leader', 'second_leader', 'third_leader', 'ancestor_relation'])
            ->where('id', $params['first_id'])
            ->findOrEmpty()
            ->toArray();
        if(empty($firstLeader)) {
            throw new \think\Exception('推荐人不存在');
        }

        if ($params['first_id'] == $params['id']) {
            throw new \think\Exception('不能指定上级是自己');
        }
        $ancestorArr = explode(',', trim($firstLeader['ancestor_relation']));
        if(!empty($ancestorArr) && in_array($params['id'], $ancestorArr)) {
            throw new \think\Exception('不能指定推荐人为自己的下级');
        }

        // 上级
        $first_leader_id = $firstLeader['id'];
        // 上上级
        $second_leader_id = $firstLeader['first_leader'];
        // 上上上级
        $third_leader_id = $firstLeader['second_leader'];
        // 拼接关系链
        $firstLeader['ancestor_relation'] = $firstLeader['ancestor_relation'] ?? ''; // 清空null值及0
        $my_ancestor_relation = $first_leader_id. ',' . $firstLeader['ancestor_relation'];
        // 去除两端逗号
        $my_ancestor_relation = trim($my_ancestor_relation, ',');
        $data = [
            'first_leader' => $first_leader_id,
            'second_leader' => $second_leader_id,
            'third_leader' => $third_leader_id,
            'ancestor_relation' => $my_ancestor_relation,
            'update_time'  => time()
        ];
        return $data;
    }

    public static function clearFirstLeader($params)
    {
        $data = [
            'first_leader' => 0,
            'second_leader' => 0,
            'third_leader' => 0,
            'ancestor_relation' => '',
            'update_time'  => time()
        ];
        return $data;
    }

    public static function userLists()
    {
        $where[] = ['del', '=', 0];
        $where[] = ['user_delete', '=', 0];
        // 用户信息
        if (isset($params['keyword']) && !empty($params['keyword'])) {
            $where[] = ['sn|nickname', 'like', '%'. $params['keyword'] .'%'];
        }

        $lists = User::field('id,sn,nickname,id as distribution,user_delete')
            ->where($where)
            ->page($params['page'], $params['limit'])
            ->select()
            ->toArray();
        $count = User::where($where)->count();

        return [
            'count' => $count,
            'lists' => $lists,
        ];
    }
}
