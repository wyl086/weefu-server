<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | gitee下载：https://gitee.com/multshop_gitee
// | github下载：https://github.com/multshop-github
// | 访问官网：https://www.multshop.cn
// | 访问社区：https://home.multshop.cn
// | 访问手册：http://doc.multshop.cn
// | 微信公众号：multshop技术社区
// | multshop系列产品在gitee、github等公开渠道开源版本可免费商用，未经许可不能去除前后端官方版权标识
// |  multshop系列产品收费版本务必购买商业授权，购买去版权授权后，方可去除前后端官方版权标识
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\shop\logic\coupon;

use app\common\model\user\User;
use app\common\server\UrlServer;
use app\common\basics\Logic;
use think\facade\Db;
use app\common\model\coupon\Coupon;
use app\common\model\coupon\CouponGoods;
use app\common\model\coupon\CouponList;
use app\common\model\user\UserLevel;


class CouponLogic extends Logic{
    public static function lists($get){
        // 下架本店过了发放时间的优惠券
        $now = time();
        $updateWhere = [
            ['send_time_start','<',$now],
            ['send_time_end','<',$now],
            ['status','=',1],
            ['shop_id', '=', $get['shop_id']]
        ];
        Coupon::where($updateWhere)
            ->update(['status'=>0,'update_time'=>$now]);

        $where = [
            ['del','=',0],
            ['shop_id','=', $get['shop_id']]
        ];

        if(empty($get['type'])) {
            // 已下架
            $where[] = ['status', '=', '0'];
        }else{
            $where[] = ['status', '=', '1'];
        }

        // 名称
        if(isset($get['name']) && !empty($get['name'])) {
            $where[] = ['name', 'like', '%'.trim($get['name']).'%' ];
        }

        // 领取方式
        if(isset($get['get_type']) && !empty($get['get_type'])) {
            $where[] = ['get_type', '=', $get['get_type'] ];
        }

        // 创建时间
        if(isset($get['start_time']) && !empty($get['start_time'])) {
            $where[] = ['create_time', '>=', strtotime($get['start_time']) ];
        }

        if(isset($get['end_time']) && !empty($get['end_time'])) {
            $where[] = ['create_time', '<=', strtotime($get['end_time']) ];
        }

        $coupon_count = Coupon::where($where)->count();

        $coupon_list = Coupon::field('id,name,money,use_goods_type,use_goods_type as use_goods_type_desc,condition_type,condition_money,condition_type as condition_type_desc,send_total_type,send_total_type as send_total_type_desc,send_total,get_type,get_type as get_type_desc,status,status as statusDesc,send_time_start,send_time_end,send_time_start as send_time,use_time_type,use_time_start,use_time_end,use_time,use_time_type as use_time_desc,create_time')
            ->where($where)
            ->page($get['page'], $get['limit'])
            ->order('id desc')
            ->select()
            ->toArray();

        return ['count' => $coupon_count, 'list' => $coupon_list];

    }

    //新增优惠券
    public static function add($post){
        $time = time();
        //拼接数据
        $add_data = [
            'shop_id'           => $post['shop_id'],
            'name'              => trim($post['name']),
            'money'             => $post['money'],
            'send_time_start'   => strtotime($post['send_time_start']),
            'send_time_end'     => strtotime($post['send_time_end']),
            'send_total_type'   => $post['send_total_type'],
            'send_total'        => $post['send_total_type'] == 2 ? $post['send_total'] : '',
            'condition_type'    => $post['condition_type'],
            'condition_money'   => $post['condition_type'] == 2 ? $post['condition_money'] : '',
            'use_time_type'     => $post['use_time_type'],
            'use_time_start'    => $post['use_time_type'] == 1 ? strtotime($post['use_time_start']) : '',
            'use_time_end'      => $post['use_time_type'] == 1 ? strtotime($post['use_time_end']) : '',
            'use_time'          => $post['use_time_type'] == 2 ? $post['use_time'] : '',
            'get_type'          => $post['get_type'],
            'get_num_type'      => $post['get_num_type'],
            'get_num'           => $post['get_num'],
            'use_goods_type'    => $post['use_goods_type'],
            'status'            => $post['status'],
            'create_time'       => $time,
            'update_time'       => $time,
        ];
        //用券时间
        if($post['use_time_type'] == 3){
            $update_data['use_time'] = $post['tomorrow_use_time'];
        }
        //领取次数
        if($post['get_num_type'] == 3){
            $update_data['get_num'] = $post['day_get_num'];
        }

        //提交订单
        Db::startTrans();
        try {
            $coupon = Coupon::create($add_data);
            if($coupon && $coupon['use_goods_type'] != 1){
                $goods_coupon = [];
                $now = time();
                foreach ($post['goods_ids'] as $item){
                    $goods_coupon[] = [
                        'coupon_id'     => $coupon->id,
                        'goods_id'      => $item,
                        'create_time'   => $now,
                    ];
                }
                $couponGoods = new CouponGoods();
                $couponGoods->saveAll($goods_coupon);
            }
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }

    }
    public static function edit($post){
        //拼接数据
        $update_data = [
            'shop_id'           => $post['shop_id'],
            'name'              => $post['name'],
            'money'             => $post['money'],
            'send_time_start'   => strtotime($post['send_time_start']),
            'send_time_end'     => strtotime($post['send_time_end']),
            'send_total_type'   => $post['send_total_type'],
            'send_total'        => $post['send_total_type'] == 2 ? $post['send_total'] : '',
            'condition_type'    => $post['condition_type'],
            'condition_money'   => $post['condition_type'] == 2 ? $post['condition_money'] : '',
            'use_time_type'     => $post['use_time_type'],
            'use_time_start'    => $post['use_time_type'] == 1 ? strtotime($post['use_time_start']) : '',
            'use_time_end'      => $post['use_time_type'] == 1 ? strtotime($post['use_time_end']) : '',
            'use_time'          => $post['use_time_type'] == 2 ? $post['use_time'] : '',
            'get_type'          => $post['get_type'],
            'get_num_type'      => $post['get_num_type'],
            'get_num'           => $post['get_num_type'] == 2 ? $post['get_num'] : '',
            'use_goods_type'    => $post['use_goods_type'],
            'status'            => $post['status'],
            'update_time'       => time()
        ];
        //用券时间
        if($post['use_time_type'] == 3){
            $update_data['use_time'] = $post['tomorrow_use_time'];
        }
        //领取次数
        if($post['get_num_type'] == 3){
            $update_data['get_num'] = $post['day_get_num'];
        }

        Db::startTrans();
        try {
            $coupon = Coupon::update($update_data,['id'=>$post['id']]);
            CouponGoods::where(['coupon_id'=>$post['id']])->delete();

            if($coupon && $coupon['use_goods_type'] != 1){
                $goods_coupon = [];
                $now = time();
                foreach ($post['goods_ids'] as $item){
                    $goods_coupon[] = [
                        'coupon_id'     => $post['id'],
                        'goods_id'      => $item,
                        'create_time'   => $now,
                    ];
                }
                $couponGoods = new CouponGoods();
                $couponGoods->saveAll($goods_coupon);
            }
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }

    public static function getCoupon($id,$get_data = false){
        $coupon = Coupon::findOrEmpty($id);
        $coupon['goods_coupon'] = [];
        if($get_data) {
            $coupon = $coupon->getData();
            $coupon['send_time_start'] = date('Y-m-d H:i:s',$coupon['send_time_start']);
            $coupon['send_time_end'] = date('Y-m-d H:i:s',$coupon['send_time_end']);
            if($coupon['use_goods_type'] != 1){ // 非全部商品
                $goods_coupon= CouponGoods::alias('cg')
                    ->join('goods g','cg.goods_id = g.id')
                    ->where(['coupon_id'=>$id])
                    ->field('g.id,name,max_price,min_price,stock')
                    ->select();
                foreach ($goods_coupon as &$item){
                    $item['price'] = '￥'.$item['min_price'].'~'.'￥'.$item['max_price'];
                    if($item['max_price'] == $item['min_price']){
                        $item['price'] = '￥'.$item['min_price'];
                    }
                }
                $coupon['goods_coupon'] = $goods_coupon;
            }
            if($coupon['use_time_start']){
                $coupon['use_time_start'] = date('Y-m-d H:i:s',$coupon['use_time_start']);
                $coupon['use_time_end'] = date('Y-m-d H:i:s',$coupon['use_time_end']);
            }
        }

        return $coupon;
    }

    /*
 * 发放记录
 */
    public static function log($get){
        $where[] = ['cl.del','=',0];
        $where[] = ['cl.coupon_id','=',$get['id']];

        if(isset($get['keyword']) && $get['keyword']){
            switch($get['search_type']) {
                case 'sn';
                    $where[] = ['u.sn', '=', $get['keyword']];
                    break;
                case 'nickname';
                    $where[] = ['u.nickname', '=', $get['keyword']];
                    break;
                case 'mobile';
                    $where[] = ['u.mobile', '=', $get['keyword']];
                    break;
            }
        }

        if(isset($get['status']) && $get['status'] != '') {
            $where[] = ['cl.status', '=', $get['status']];
        }

        $log_count = CouponList::alias('cl')
            ->join('user u','cl.user_id = u.id')
            ->where($where)
            ->count();

        $log_list = CouponList::alias('cl')
            ->join('user u','cl.user_id = u.id')
            ->where($where)
            ->field('cl.coupon_id,cl.status as cl_status,coupon_code,cl.create_time as cl_create_time,cl.use_time,u.nickname,u.avatar,u.mobile,u.sn,u.level')
            ->page($get['page'], $get['limit'])
            ->select();
        $coupon_list = Coupon::where(['del'=>0])->column('name','id');
        $level_name =  UserLevel::where(['del'=>0])->column('name','id');
        foreach ($log_list as &$item)
        {
            $item['coupon_name'] = $coupon_list[$item['coupon_id']] ?? '';
            $item['avatar'] = UrlServer::getFileUrl($item['avatar']);
            $item['level_name'] = $level_name[$item['level']] ?? '';
            $item['status_desc'] = $item['cl_status'] ? '已使用' : '未使用';
            $item['cl_create_time'] = date('Y-m-d H:i:s',$item['cl_create_time']);
            $item['use_time_desc'] = $item['use_time'] ? date('Y-m-d H:i:s',$item['use_time']) : '';
        }
        return ['count'=>$log_count , 'lists'=>$log_list];
    }

    public static function changeStatus($id)
    {
        try{
            $coupon = Coupon::findOrEmpty($id);
            if ($coupon['send_time_end'] <= time() && $coupon['status'] == 0) {
                throw new \Exception('已过发放时间，无法上架');
            }
            $coupon->status = $coupon->status ? 0 : 1;
            $coupon->update_time = time();
            $coupon->save();
            return true;
        }catch(\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }

    }

    /*
     * 删除优惠券  删除已领取用户的优惠券
     */
    public static function del($id){
        Db::startTrans();
        try{
            $time = time();
            // 优惠券主表
            Coupon::update([
                'id' => $id,
                'del' => 1,
                'update_time' => $time
            ]);
            // 已发放的优惠券
            CouponList::where(['coupon_id'=>$id])->update([
                'del' => 1,
                'update_time' => $time
            ]);
            Db::commit();
            return true;
        }catch(\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * 发放优惠券
     */
    public static function sendCoupon($post){
        $user_ids = $post['user_ids'];
        $coupon_ids = $post['coupon_ids'];
        $coupon_list = Coupon::where('id', 'in', $coupon_ids)->column('*', 'id');
        $user_list = User::where('id', 'in', $user_ids)->column('id,nickname', 'id');

        // 判断是否会超发
        foreach($coupon_ids as $coupon_id) {
            $coupon = $coupon_list[$coupon_id];
            if($coupon['send_total_type'] == 2) { // 限制数量的券
                $already_issue = CouponList::where([
                    ['coupon_id','=',$coupon_id],
                    ['del','=',0]
                ])->count(); // 已发放数量
                $target_issue = count($user_ids) + $already_issue; // 目标发放总数(若能正常发放的话)
                if($target_issue > $coupon['send_total']) {
                    self::$error = $coupon['name'].'的发放数量已达到限制';
                    return false;
                }
            }
        }

        // 判断用户是否超出领取数量
        foreach($coupon_ids as $coupon_id) {
            $coupon = $coupon_list[$coupon_id];
            if($coupon['get_num_type'] == 2) { // 限制次数
                foreach($user_ids as $user_id) {
                    $count = CouponList::where([
                        'user_id' => $user_id,
                        'coupon_id' => $coupon_id,
                        'del' => 0,
                    ])->count();
                    if($count >= $coupon['get_num']) {
                        self::$error = $user_list[$user_id]['nickname'].'已超出领取的限制次数';
                        return false;
                    }
                }
            }
            if($coupon['get_num_type'] == 3) { // 每天领取次数限制
                $timeStr = date('Y-m-d', time());
                $today = strtotime($timeStr.' 00:00:00');
                foreach($user_ids as $user_id) {
                    $count = CouponList::where([
                        ['user_id', '=', $user_id],
                        ['coupon_id', '=', $coupon_id],
                        ['del', '=', 0],
                        ['create_time', '>', $today],
                    ])->count();
                    if($count >= $coupon['get_num']) {
                        self::$error = $user_list[$user_id]['nickname'].'已超出每日领取的限制次数';
                        return false;
                    }
                }
            }
        }


        try{
            $addData = [];
            $time = time();
            foreach ($coupon_ids as $coupon_id){
                foreach ($user_ids  as $user_id){
                    $addData[] = [
                        'user_id'       => $user_id,
                        'coupon_id'     => $coupon_id,
                        'status'        => 0,
                        'coupon_code'   => create_coupon_code(),
                        'create_time'   => $time,
                        'update_time'   => $time
                    ];
                }
            }
            // 批量添加
            $couponList = new CouponList();
            $couponList->saveAll($addData);

            return true;
        }catch(\Exception $e){
            self::$error = $e->getMessage();
            return false;
        }
    }

    public static function getShopCouponList($get)
    {
        $where = [
            ['del', '=', 0],  // 未删除
            ['status', '=', 1], // 上架中
            ['get_type', '=', 2], // 商家赠送
            ['send_time_start', '<=', time()], // 开始发放时间早于或等于当前时间
            ['send_time_end', '>', time()], // 结束发放时间大于当前时间
            ['shop_id', '=', $get['shop_id']]
        ];

        if(isset($get['name']) && !empty($get['name'])) {
            $where[] = ['name', 'like', '%'.trim($get['name']).'%'];
        }

        if(isset($get['use_goods_type']) && !empty($get['use_goods_type'])) {
            $where[] = ['use_goods_type', '=', $get['use_goods_type']];
        }

        $lists = Coupon::field('id,name,send_time_start,send_time_end,send_time_start as send_time,money,get_type as get_type_desc,condition_type as condition_type_desc_two,condition_money,send_total_type as send_info,send_total,use_goods_type as use_goods_type_desc,use_time_type,use_time_start,use_time_end,use_time,use_time as use_time_desc,status as status_desc,create_time')
            ->where($where)
            ->page($get['page'], $get['limit'])
            ->order('id', 'desc')
            ->select()
            ->toArray();

        $count = Coupon::field('id,name,send_time_start,send_time_end,money,get_type,condition_type,send_total_type')
            ->where($where)
            ->count();

        return [
            'count' => $count,
            'lists' => $lists
        ];
    }
}