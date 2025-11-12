<?php
namespace app\api\logic;

use app\api\controller\Account;
use app\common\basics\Logic;
use app\common\enum\ClientEnum;
use app\common\logic\ChatLogic;
use app\common\model\goods\GoodsCollect;
use app\common\model\kefu\ChatRecord;
use app\common\model\kefu\ChatRelation;
use app\common\model\kefu\Kefu;
use app\common\model\user\User;
use app\common\model\order\Order;
use app\common\model\AfterSale;
use app\common\model\CouponList;
use app\common\model\user\UserAuth;
use app\common\model\user\UserLevel;
use app\common\server\ConfigServer;
use app\common\model\AccountLog;
use app\common\server\UrlServer;
use app\common\server\WeChatServer;
use app\common\server\storage\Driver as StorageDriver;
use think\facade\Db;
use EasyWeChat\Factory;
use EasyWeChat\Kernel\Exceptions\Exception;

class UserLogic extends Logic
{
    public static function center($user_id)
    {
        $user = User::findOrEmpty($user_id);
        if($user->isEmpty()) {
            return [];
        }
        // 头像
        $user->avatar = UrlServer::getFileUrl($user->avatar);
        //待支付
        $user->wait_pay = Order::where(['del'=>0,'user_id'=>$user_id,'order_status'=>Order::STATUS_WAIT_PAY,'pay_status'=>0])->count();
        //待发货
        $user->wait_delivery = Order::where(['del'=>0,'user_id'=>$user_id,'order_status'=>Order::STATUS_WAIT_DELIVERY,'pay_status'=>1])->count();
        //待收货
        $user->wait_take = Order::where(['del'=>0,'user_id'=>$user_id,'order_status'=>Order::STATUS_WAIT_RECEIVE,'pay_status'=>1])->count();
        //待评论
        $user->wait_comment = Order::alias('o')
            ->join('order_goods og','o.id = og.order_id')
            ->where(['del'=>0,'user_id'=>$user_id,'order_status'=>Order::STATUS_FINISH,'og.is_comment'=>0])
            ->count('og.id');
        //售后中
        $user->after_sale = AfterSale::where(['del'=>0,'user_id'=>$user_id])
            ->where('status','<>',AfterSale::STATUS_SUCCESS_REFUND)
            ->count();
        // 优惠券
        $user->coupon = self::ableUsedCouponCount($user_id);
        //分销开关
        $user->distribution_setting = ConfigServer::get('distribution', 'is_open',1);
        //消息数量
        $user->notice_num = 0;
        //收藏商品数量
        $user->collect = GoodsCollect::where(['user_id' => $user_id, 'status' => 1])->count();
        // 会员等级
        $level_list = UserLevel::where(['del'=>0])->order('growth_value', 'asc')->column('id,name,growth_value,image,background_image', 'id');
        $user['level_name'] = isset($level_list[$user['level']]) ? $level_list[$user['level']]['name'] : '-';
        $user['vip'] = 0;
        $level_list = array_values($level_list);
        foreach($level_list as $k=>$v){
            if($v['id'] == $user['level']) {
                $user['vip'] = $k + 1;
            }
        }
        //下个会员等级提示
        $user_level = UserLevel::where([
                ['id','>',$user->getData('level')],
                ['del','=',0]
            ])->order('growth_value asc')
            ->findOrEmpty();
        $user['next_level_tips'] = '';
        if(!$user_level->isEmpty()){
            $user['next_level_tips'] = '距离升级还差'.intval($user_level['growth_value'] - $user['user_growth']);
        }
        // 是否设置支付密码
        $user['hasPayPassword'] = $user['pay_password'] ? 1: 0;
        $user->visible(['id','nickname','sn','avatar', 'mobile', 'hasPayPassword','next_level_tips','user_money','total_order_amount','total_recharge_amount',
            'coupon','user_integral','level','wait_pay','wait_take','wait_delivery',
            'wait_comment','after_sale', 'distribution_setting', 'distribution_code', 'notice_num', 'collect','level_name','vip','is_new_user']);
        $user = $user->toArray();
        return $user;
    }

    public static function accountLog($user_id,$source,$type,$page,$size){
        $source_type = '';
        $where[] = ['user_id','=',$user_id];
        switch ($source){
            case 1:
                $source_type = AccountLog::money_change;
                break;
            case 2:
                $source_type = AccountLog::integral_change;
                break;
            case 3:
                $source_type = AccountLog::growth_change;

        }
        $where[] = ['source_type','in',$source_type];
        if($type && $type != 0){
            $where[] = ['change_type','=',$type];
        }

        $count = AccountLog::where($where)->count();
        $list = AccountLog::where($where)
            ->page($page,$size)
            ->order('id desc')
            ->field('id,change_amount,change_amount as change_amount_format,source_type,change_type,create_time,create_time as create_time_format')
            ->select()
            ->toArray();

        $more = is_more($count,$page,$size);  //是否有下一页

        $data = [
            'list'          => $list,
            'page_no'       => $page,
            'page_size'     => $size,
            'count'         => $count,
            'more'          => $more
        ];
        return $data;
    }

    public static function getUserLevelInfo($id)
    {
        $user = User::field('id,avatar,nickname,level,user_growth')->findOrEmpty($id);
        if($user->isEmpty()) {
            return ['error_msg' => '会员不存在'];
        }
        $user = $user->toArray();
        $level_list = UserLevel::where(['del'=>0])->order('growth_value', 'asc')->column('id,name,growth_value,image,background_image', 'id');

        // 用户会员等级
        $user['level_name'] = isset($level_list[$user['level']]) ? $level_list[$user['level']]['name'] : '-';

        // 用户头像
        $user['avatar'] = UrlServer::getFileUrl($user['avatar']);

        // 重置会员等级列表索引
        $level_list = array_values($level_list);
        // 获取当前用户对应等级的索引
        $index = null;
        foreach($level_list as $key => $item) {
            if($item['id'] == $user['level']) {
                $index = $key;
            }
        }

        // 遍历标识每个会员等级信息
        foreach($level_list as $key => &$item) {
            $diff_growth_percent = 0; //距离下个等级成长百分比
            if($index === false || $index < $key) {
                $item['lock_desc'] = '未解锁';
                $item['tips1'] = '当前成长值 '. $user['user_growth'];
                $item['tips2'] = '还需'.($item['growth_value'] - $user['user_growth']).'成长值';
                $item['current_level_status'] = -1;
            }else if($index > $key) {
                $item['lock_desc'] = '已解锁';
                $item['tips1'] = '当前高于该等级 ';
                $item['tips2'] = '';
                $item['current_level_status'] = 0;
            }else if($index == $key) {
                $item['current_level_status'] = 1;
                $item['lock_desc'] = '当前等级';
                $item['tips1'] = '当前成长值 '. $user['user_growth'];
                //下个等级
                $next_level = $level_list[$key+1] ?? [];
                if($next_level) {
                    $diff_growth_percent = round($user['user_growth'] / $next_level['growth_value'],2);
                    $item['tips2'] = '满'.$next_level['growth_value'].'可升级';
                } else {
                    $item['tips2'] = '';
                }
            }
            $item['diff_growth_percent'] = $diff_growth_percent;
            $item['image'] = empty($item['image']) ? '' : UrlServer::getFileUrl($item['image']);
            $item['background_image'] = empty($item['background_image']) ? '' : UrlServer::getFileUrl($item['background_image']);
        }

        $level_intro = ConfigServer::get('user_level', 'intro', '');
        $data = [
            'user' => $user,
            'level' => $level_list,
            'level_intro' => $level_intro
        ];

        return $data;
    }

    public static function getGrowthList($get)
    {
        $user_growth = User::where(['id'=>$get['user_id']])->value('user_growth');
        $where = [
            ['user_id', '=', $get['user_id']],
            ['source_type', 'in', AccountLog::growth_change]
        ];
        $lists = AccountLog::field('id,source_type,change_amount as change_amount_format,change_type,create_time as create_time_format')
            ->where($where)
            ->order('create_time', 'desc')
            ->page($get['page_no'], $get['page_size'])
            ->select()
            ->toArray();
        $count = AccountLog::field('id,source_type,change_amount as change_amount_format,change_type,create_time as create_time_format')
            ->where($where)
            ->count();

        $data = [
            'count' => $count,
            'lists' => $lists,
            'page_no' => $get['page_no'],
            'page_size' => $get['page_size'],
            'more' => is_more($count, $get['page_no'], $get['page_size']),
            'user_growth' => $user_growth
        ];
        return $data;
    }

    public static function myWallet($user_id)
    {
        $info = User::where(['id'=>$user_id])
            ->field('user_money,total_order_amount,total_recharge_amount')
            ->findOrEmpty();
        if($info->isEmpty()) {
            self::$error = '用户不存在';
            return false;
        }
        $info = $info->toArray();
        $info['open_racharge'] = ConfigServer::get('recharge','open_racharge',0);
        return $info;
    }

    //获取用户信息
    public static function getUserInfo($user_id)
    {
        $info = User::where(['id' => $user_id])
            ->field('id,sn,nickname,avatar,mobile,sex,create_time')
            ->find()
            ->toArray();
        $info['avatar'] = $info['avatar'] ? UrlServer::getFileUrl($info['avatar']) : '';
        
        $info['oa_auth'] = UserAuth::where('user_id', $info['id'])->where('client', ClientEnum::oa)->value('id') ? 1: 0;
        $info['mnp_auth'] = UserAuth::where('user_id', $info['id'])->where('client', ClientEnum::mnp)->value('id') ? 1: 0;
        
        return $info;
    }

    //设置个人信息
    public static function setUserInfo($post)
    {
        try{
            $field = $post['field'];
            $value = $post['value'];

            if ($field == 'avatar') {
                $value = UrlServer::setFileUrl($value);
            }

            User::where(['id'=> $post['user_id']])
                ->update([$field => $value]);
            return true;
        }catch(\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    //更新微信信息
    public static function updateWechatInfo($user_id, $post)
    {
        Db::startTrans();
        try{
            $time = time();
            $avatar_url = $post['avatar'];
            $nickanme  = $post['nickname'];
            $sex = $post['sex'];

            $config = [
                'default' => ConfigServer::get('storage', 'default', 'local'),
                'engine'  => ConfigServer::get('storage_engine')
            ];

            $avatar = ''; //头像路径
            if ($config['default'] == 'local') {
                $file_name = md5($user_id . $time. rand(10000, 99999)) . '.jpeg';
                $avatar = download_file($avatar_url, 'uploads/user/avatar/', $file_name);
            } else {
                $avatar = 'uploads/user/avatar/' . md5($user_id . $time. rand(10000, 99999)) . '.jpeg';
                $StorageDriver = new StorageDriver($config);
                if (!$StorageDriver->fetch($avatar_url, $avatar)) {
                    throw new Exception( '头像保存失败:'. $StorageDriver->getError());
                }
            }

            User::where(['id' => $user_id])->update([
                'nickname'  => $nickanme,
                'avatar' => $avatar,
                'sex' => $sex
            ]);

            Db::commit();
            return true;

        } catch(\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }

    //获取微信手机号
    public static function getMobileByMnp($post)
    {
        Db::startTrans();
        try {
            $config = WeChatServer::getMnpConfig();
            $app = Factory::miniProgram($config);
            $response = $app->auth->session($post['code']);
            if (!isset($response['session_key'])) {
                throw new Exception('获取用户信息失败');
            }
            $response = $app->encryptor->decryptData($response['session_key'], $post['iv'], $post['encrypted_data']);

            $isExist = User::where([
                ['mobile', '=', $response['phoneNumber']],
                ['id', '<>', $post['user_id']]
            ])->findOrEmpty();

            if (!$isExist->isEmpty()) {
                throw new Exception('手机号已被其他账号绑定');
            }

            User::update(['id' => $post['user_id'], 'mobile' => $response['phoneNumber']]);
            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }

    //修改手机号
    public static function changeMobile($user_id, $data)
    {
        $user = User::find($user_id);
        $user->mobile = $data['new_mobile'];
        $user->save();
        return $user;
    }

    //我的粉丝列表
    public static function fans($user_id, $get, $page, $size)
    {
        $where = [];
        if (isset($get['keyword']) && $get['keyword'] != ''){
            $where[] = ['nickname|mobile','like','%'.$get['keyword'].'%'];
        }

        //查询条件
        $type = $get['type'] ?? 'all';
        switch ($type){
            case 'first':
                $where[] = ['first_leader', '=', $user_id];
                break;
            case 'second':
                $where[] = ['second_leader', '=', $user_id];
                break;
            default:
                $where[] = ['first_leader|second_leader', '=', $user_id];
        }

        $field = 'u.id, avatar, nickname, mobile, u.create_time, order_num as fans_order,
                  order_amount as fans_money, fans as fans_team';

        $count = User::alias('u')
            ->field($field)
            ->leftJoin('user_distribution d', 'd.user_id = u.id')
            ->where($where)
            ->count();

        $lists = User::alias('u')
            ->field($field)
            ->leftJoin('user_distribution d', 'd.user_id = u.id')
            ->where($where)
            ->page($page, $size)
            ->order(self::fansListsSort($get))
            ->select();

        foreach ($lists as &$item) {
            $item['avatar'] = UrlServer::getFileUrl($item['avatar']);
            $item['fans_team'] = $item['fans_team'] ?? 0;
            $item['fans_order'] = $item['fans_order'] ?? 0;
            $item['fans_money'] = $item['fans_money'] ?? 0;
            unset($item['fans'], $item['distribution_order_num'], $item['distribution_money']);
        }

        $data = [
            'list' => $lists,
            'page' => $page,
            'size' => $size,
            'count' => $count,
            'more' => is_more($count, $page, $size)
        ];
        return $data;
    }

    //粉丝列表排序
    public static function fansListsSort($get)
    {
        if (isset($get['fans']) && $get['fans'] != ''){
            return ['fans_team' =>  $get['fans'], 'u.id' => 'desc'];
        }

        if (isset($get['money']) && $get['money'] != ''){
            return ['fans_money' =>  $get['money'], 'u.id' => 'desc'];
        }

        if (isset($get['order']) && $get['order'] != ''){
            return ['fans_order' =>  $get['order'], 'u.id' => 'desc'];
        }

        return ['u.id' =>  'desc'];
    }


    /**
     * @notes 获取聊天记录
     * @param $user_id
     * @param $shop_id
     * @param $page
     * @param $size
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2021/12/29 18:20
     */
    public static function getChatRecord($user_id, $shop_id, $page, $size)
    {
        $map1 = [
            ['shop_id', '=', $shop_id],
            ['from_id', '=', $user_id],
            ['from_type', '=', 'user'],
        ];
        $map2 = [
            ['shop_id', '=', $shop_id],
            ['to_id', '=', $user_id],
            ['to_type', '=', 'user'],
        ];
        // 通信记录
        $records = ChatRecord::whereOr([$map1, $map2])
            ->order('id desc')
            ->page($page, $size)
            ->select()->toArray();

        $count = ChatRecord::whereOr([$map1, $map2])->count();

        // 上一个客服关系
        $kefu = self::getLastRelation((int)$user_id, (int)$shop_id);

        // 当前在线的所有客服
        $online = ChatLogic::getOnlineKefu($shop_id);

        // 后台配置=>[1=>人工客服; 2=>在线客服]
        if ($shop_id > 0) {
            $config = ConfigServer::get('shop_customer_service', 'type', 1, $shop_id);
        } else {
            $config = ConfigServer::get('customer_service', 'type', 1);
        }

        $kefu_id = $kefu['kefu_id'] ?? 0;

        $records = ChatLogic::formatChatRecords($records, $count, $page, $size);

        // 没有在线客服或者后台配置为 人工客服
        if (empty($online) || $config == 1) {
            return ['config' => $config, 'kefu' => [], 'record' => $records];
        }

        // 没有聊天记录(未与客服聊天) 或者 曾经的聊天客服不在线
        if (empty($kefu) || !in_array($kefu_id, $online)) {
            // 随机分配客服
            $rand = rand(0, count($online) - 1);
            $kefu_id = $online[$rand];
        }

        $kefu = Kefu::where(['id' => $kefu_id])
            ->field(['id', 'nickname', 'avatar'])
            ->findOrEmpty();

        return ['config' => $config, 'kefu' => $kefu, 'record' => $records];
    }



    /**
     * @notes 获取用户最后通信的客服id
     * @param int $user_id
     * @param int $shop_id
     * @return array|\think\Model
     * @author 段誉
     * @date 2021/12/29 18:20
     */
    public static function getLastRelation(int $user_id, int $shop_id)
    {
        $relation = ChatRelation::where([
            'user_id' => $user_id,
            'shop_id' => $shop_id
        ])->order('update_time desc')->findOrEmpty();

        return $relation;
    }


    /**
     * @notes 可用优惠券数量
     * @param $get
     * @return int
     * @author 段誉
     * @date 2023/2/20 15:10
     */
    public static function ableUsedCouponCount($user_id)
    {
        // 提取用户未删除的优惠券
        $where = [
            ['cl.del', '=', 0],
            ['cl.user_id', '=', $user_id]
        ];
        $field = 'cl.*, c.name,c.use_time_type,c.use_time_start,c.use_time_end,c.use_time as coupon_use_time,
        c.condition_type,c.condition_money,c.money,c.use_goods_type';
        $count_list = CouponList::alias('cl')
            ->leftJoin('coupon c', 'c.id=cl.coupon_id')
            ->field($field)
            ->where($where)
            ->order('id', 'desc')
            ->select()
            ->toArray();

        // 循环标识每条记录的券是否过期
        foreach($count_list as &$item) {
            $item['is_expired'] = 0; // 默认先标识为未过期
            switch($item['use_time_type']) {
                case 1: // 固定时间
                    if($item['use_time_end'] <= time()) {
                        $item['is_expired'] = 1;
                    }
                    break;
                case 2:  // 领券当天起
                    $days = '+'.$item['coupon_use_time'].' day';
                    $expired_time = strtotime($days, strtotime($item['create_time']));
                    if($expired_time <= time()) {
                        $item['is_expired'] = 1;
                    }
                    break;
                case 3: // 领券次日起
                    $days = '+'.($item['coupon_use_time']+1).' day';
                    $expired_time = strtotime($days, strtotime($item['create_time']));
                    if($expired_time <= time()) {
                        $item['is_expired'] = 1;
                    }
                    break;
            }
        }

        $valid_array = array_filter($count_list, function($item) {
            return $item['status'] == 0 && $item['is_expired'] == 0; // 未使用，未过期
        });

        return count($valid_array);
    }
}
