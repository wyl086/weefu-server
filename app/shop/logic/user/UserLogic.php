<?php
namespace app\shop\logic\user;

use app\common\basics\Logic;
use app\common\model\shop\ShopFollow;
use app\common\model\order\Order;
use app\common\model\user\User;
use app\common\server\UrlServer;
use app\common\enum\ClientEnum;

class UserLogic extends Logic
{
    public static function lists($get)
    {
        $shopUserWhere = [
            ['shop_id', '=', $get['shop_id']],
            ['status', '=', 1]
        ];
        // 根据关注时间筛选
        if(!empty($get['start_time'])) {
            $shopUserWhere[] = ['update_time', '>=', strtotime($get['start_time'])];
        }
        if(!empty($get['end_time'])) {
            $shopUserWhere[] = ['update_time', '<=', strtotime($get['end_time'])];
        }
        // 获取关注的用户
        $shopUser = ShopFollow::where($shopUserWhere)->column('user_id', 'user_id');

        if(!empty($get['start_time']) || !empty($get['end_time'])) { // 通过关注时间筛选，将不显示下单的用户
            $orderUser = [];
        }else{
            // 获取下过单的用户
            $orderUser = Order::where([
                'shop_id' => $get['shop_id']
            ])->column('user_id', 'user_id');
        }


        $mergeUser = array_merge($shopUser, $orderUser);
        $uniqueUser = array_unique($mergeUser);

        $where = [
            ['id', 'in', $uniqueUser],
            [ 'del', '=', 0 ],
            [ 'user_delete', '=', 0 ],
        ];

        if(isset($get['keyword']) && !empty($get['keyword'])) {
            $where[] = [$get['keyword_type'], 'like', '%'. $get['keyword'] . '%'];
        }

        if(isset($get['client']) && $get['client'] != '') {
            $where[] = ['client', '=', $get['client']];
        }

        $count = User::where($where)->count();
        $lists = User::field('id,sn,nickname,avatar as abs_avatar,level,level as levelName,client,login_time,create_time')
            ->append(['client_desc'])
            ->where($where)
            ->order('id desc')
            ->page($get['page'],$get['limit'])
            ->select()
            ->toArray();

        return [
            'count' => $count,
            'lists' => $lists
        ];
    }

    public static function getInfo($id)
    {
        $user =  User::field('id,sn,nickname,avatar,birthday,sex,mobile,client,create_time,login_time,user_money')
            ->where('user_delete', 0)
            ->findOrEmpty($id);
        if($user->isEmpty()) {
            return [];
        }
        $user =$user->toArray();

        // 头像
        $user['avatar'] = UrlServer::getFileUrl($user['avatar']);
        // 客户端
        $user['client_desc'] = ClientEnum::getClient($user['client']);

        return $user;
    }
}