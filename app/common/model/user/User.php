<?php
namespace  app\common\model\user;

use app\common\basics\Models;
use app\common\model\distribution\Distribution;
use app\common\model\user\UserLevel;
use app\common\model\distribution\DistributionOrderGoods;
use app\common\server\UrlServer;
use app\common\enum\ClientEnum;

class User extends Models
{
    
    public function getSexAttr($value)
    {
        switch ($value){
            case 1:
                return '男';
            case 2:
                return '女';
            default:
                return '未知';
        }
    }

    public function getBirthdayAttr($value)
    {
        return $value ? date('Y-m-d', $value) : $value;
    }

    public function getLoginTimeAttr($value)
    {
        return date('Y-m-d H:i:s', $value);
    }

    public function getAbsAvatarAttr($value)
    {
        return empty($value) ? '' : UrlServer::getFileUrl($value);
    }

    public function getClientDescAttr($value, $data)
    {
        return ClientEnum::getClient($data['client']);
    }

    public function getLevelNameAttr($value, $data)
    {
        $levelName = '-';
        if($data['level']) {
            $levelName = UserLevel::where(['id'=>$data['level'], 'del'=>0])->value('name');
        }
        return $levelName;
    }

    public function getGroupNameAttr($value, $data) {
        $groupName = '-';
        if($data['group_id']) {
            $groupName = UserGroup::where(['id'=>$data['group_id'], 'del'=>0])->value('name');
        }
        return $groupName;
    }


    public function getFansAttr($value, $data){
        $fans = User::where('find_in_set('. $data['id']. ',ancestor_relation)')->count();
        return $fans;
    }

    public function getDistributionOrderAttr($value, $data) {
        $info = DistributionOrderGoods::alias('d')
            ->leftJoin('order_goods g', 'g.id=d.order_goods_id')
            ->leftJoin('order o', 'o.id=g.order_id')
            ->field('count(d.id) as num, sum(d.money) as money, sum(o.order_amount) as amount')
            ->where([
                'd.user_id' => $data['id'],
                'd.status' => 2, // 已结算
            ])
            ->find();
        return $info;
    }

    // 获取上级
    public function getLeaderAttr($value, $data) {
        $firstLeader = [];
        if($data['first_leader']) {
            $firstLeader = self::field('id,sn,nickname,mobile,level')->where('id', $data['first_leader'])->find()->toArray();
            if($firstLeader['level']) {
                $firstLeader['levelName'] = UserLevel::where(['id'=>$firstLeader['level'], 'del'=>0])->value('name');
            }else{
                $firstLeader['levelName']  = '-';
            }
        }
        return $firstLeader;
    }

    public function level()
    {
        return $this->hasOne(UserLevel::class,'id', 'level');
    }

    /**
     * 根据user_id确认分销资格
     */
    public function confirmDistribution($user_id)
    {
        if ($this->where(['id'=>$user_id,'is_distribution'=>1,'freeze_distribution'=>0])->find()){
            return true;
        }
        return false;

    }

    public function getDistributionAttr($value)
    {
        $distribution = Distribution::where('user_id', $value)->findOrEmpty()->toArray();
        if (!empty($distribution) && $distribution['is_distribution'] == 1) {
            return '是';
        }
        return '否';
    }

    public function searchDistributionAttr($query, $value, $params)
    {
        // 非分销会员
        if (isset($params['is_distribution']) && $params['is_distribution'] != 'all' && $params['is_distribution'] == 0) {
            $ids = Distribution::where('is_distribution', 1)->column('user_id');
            $query->where('id', 'not in', $ids);
        }
        // 分销会员
        if (isset($params['is_distribution']) && $params['is_distribution'] != 'all' && $params['is_distribution'] == 1) {
            $ids = Distribution::where('is_distribution', 1)->column('user_id');
            $query->where('id', 'in', $ids);
        }
    }

    public static function getUserInfo($userId)
    {
        $user = self::field('id,sn,nickname,avatar')->findOrEmpty($userId)->toArray();
        if (empty($user)) {
            return '系统';
        }
        $user['avatar'] = empty($user['avatar']) ? '' : UrlServer::getFileUrl($user['avatar']);
        return $user;
    }
    
    static function UserIsDelete($id): bool
    {
        return (bool) User::where('id', $id)->value('user_delete');
    }
}