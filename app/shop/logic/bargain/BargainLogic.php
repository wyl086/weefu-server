<?php

namespace app\shop\logic\bargain;

use app\common\basics\Logic;
use app\common\model\bargain\Bargain;
use app\common\model\bargain\BargainItem;
use app\common\model\bargain\BargainKnife;
use app\common\model\bargain\BargainLaunch;
use app\common\model\goods\Goods as GoodsModel;
use app\common\model\user\User;
use app\common\model\order\Order;
use app\common\enum\BargainEnum;
use app\common\model\team\TeamActivity as TeamActivityModel;
use app\common\server\UrlServer;
use app\common\model\seckill\SeckillGoods;
use think\facade\Db;
use think\Model;

/**
 * Class BargainLogic
 * @package app\shop\logic\bargain
 */
class BargainLogic extends Logic
{

    /**
     * @notes 砍价活动列表
     * @param $get
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/14 10:20 上午
     */
    public static function activity($get)
    {

        $where = [
            ['del', '=', 0],
            ['shop_id', '=', $get['shop_id']]
        ];

        // 查询条件
        if (!empty($get['goods_name']) and $get['goods_name'] !== '') {
            $goodsModel = new GoodsModel();
            $ids = $goodsModel->field('id,name')->where([
                ['name', 'like', '%' . $get['goods_name'] . '%']
            ])->column('id');

            $where[] = ['goods_id', 'in', $ids];
        }

        if (isset($get['status']) and is_numeric($get['status'])) {
            $where[] = ['status', '=', (int)$get['status']];
        }

        //审核状态
        if (isset($get['type']) && $get['type'] != "") {
            $where[] = ['audit_status', '=', $get['type']];
        } else if ($get['type'] == "") {
            $where[] = ['audit_status', '=', 1];
        }

        $bargainModel = new Bargain();
        $count = $bargainModel->where($where)->count('id');
        $lists = $bargainModel->field(true)
            ->where($where)
            ->with(['goods'])
            ->append(['status_text'])
            ->withCount(['launchPeopleNumber', 'successKnifePeopleNumber', 'knifePeopleNumber'])
            ->page($get['page'], $get['limit'])
            ->select()->toArray();

        foreach ($lists as &$item) {
            $item['info']['launch_people_number_count'] = $item['launch_people_number_count'];
            $item['info']['success_knife_people_number_count'] = $item['success_knife_people_number_count'];
            $item['info']['knife_people_number_count'] = $item['knife_people_number_count'];
            $item['goods']['image'] = UrlServer::getFileUrl($item['goods']['image']);
            $item['activity_start_time'] = date('Y-m-d H:i:s', $item['activity_start_time']);
            $item['activity_end_time'] = date('Y-m-d H:i:s', $item['activity_end_time']);
        }

        return ['count' => $count, 'lists' => $lists];
    }


    /**
     * @notes 新增砍价活动
     * @param $post
     * @return bool
     * @throws \think\exception\PDOException
     * @author suny
     * @date 2021/7/14 10:21 上午
     */
    public static function add($post)
    {

        Db::startTrans();
        try {
            // 校验拼团活动是否存在
            $teamActivityModel = new TeamActivityModel();
            $team = $teamActivityModel->where([
                'goods_id' => intval($post['goods_id']),
                'del' => 0
            ])->find();

            if ($team) {
                static::$error = '商品正在参与拼团活动, 请先移除活动再添加';
                return false;
            }

            //秒杀验证
            $seckill_goods = SeckillGoods::where(['goods_id' => intval($post['goods_id']), 'del' => 0])
                ->find();
            if ($seckill_goods) {
                static::$error = '商品正在参与秒杀活动，无法修改';
                return false;
            }

            // 每刀金额(随机 / 固定)
            $knife_price = 0;
            if ($post['knife_type'] == 1) {
                $knife_price = [$post['min_knife_price'], $post['max_knife_price']];
                $knife_price = implode(',', $knife_price);
            } else {
                $knife_price = $post['fixed_knife_price'];
            }

            // 查出最大低价和最少价格
            $bargain_price = [];
            foreach ($post['floor_price'] as $key => $value) {
                foreach ($value as $K => $item) {
                    array_push($bargain_price, $item);
                }
            }
            $bargain_max_price = !empty($bargain_price) ? max($bargain_price) : 0;
            $bargain_min_price = !empty($bargain_price) ? min($bargain_price) : 0;

            // 新增砍价活动
            $bargainModel = new Bargain();
            $bargain_id = $bargainModel->insertGetId([
                'goods_id' => $post['goods_id'],
                'shop_id' => $post['shop_id'],
                'audit_status' => 0, //待审核
                'time_limit' => $post['time_limit'],
                'activity_start_time' => strtotime($post['activity_start_time']),
                'activity_end_time' => strtotime($post['activity_end_time']),
                'bargain_min_price' => $bargain_min_price,
                'bargain_max_price' => $bargain_max_price,
                'share_title' => empty($post['share_title']) ? '' : $post['share_title'],
                'share_intro' => empty($post['share_intro']) ? '' : $post['share_intro'],
                'payment_where' => $post['payment_where'],
                'knife_type' => $post['knife_type'],
                'knife_price' => $knife_price,
                'status' => $post['status'],
                'del' => 0,
            ]);

            // 新增砍价商品SKU
            $lists = [];
            foreach ($post['floor_price'] as $key => $value) {
                foreach ($value as $K => $item) {
                    $lists[] = [
                        'bargain_id' => $bargain_id,
                        'goods_id' => $key,
                        'item_id' => $K,
                        'floor_price' => $item,
                        'first_knife_price' => $post['first_knife_price'][$key][$K]
                    ];
                }
            }
            if (!empty($lists)) {
                $bargainItemModel = new BargainItem();
                $bargainItemModel->saveAll($lists);
            }

            Db::commit();
            return true;
        } catch (\Exception $e) {
            static::$error = $e->getMessage();
            Db::rollback();
            return false;
        }
    }

    /**
     * @notes 编辑砍价活动
     * @param $post
     * @return bool
     * @throws \think\exception\PDOException
     * @author suny
     * @date 2021/7/14 10:21 上午
     */
    public static function edit($post)
    {

        Db::startTrans();
        try {
            // 查询商品信息
            $goodsModel = new GoodsModel();
            $goods = $goodsModel->field('id,name,image')
                ->where(['id' => (int)$post['goods_id']])->find();

            if (!$goods) {
                static::$error = '选择的商品已不存在,可能已被删除';
                return false;
            }

            // 每刀金额(随机 / 固定)
            $knife_price = 0;
            if ($post['knife_type'] == 1) {
                $knife_price = [$post['min_knife_price'], $post['max_knife_price']];
                $knife_price = implode(',', $knife_price);
            } else {
                $knife_price = $post['fixed_knife_price'];
            }

            // 查出最大低价和最少价格
            $bargain_price = [];
            foreach ($post['floor_price'] as $key => $value) {
                foreach ($value as $K => $item) {
                    array_push($bargain_price, $item);
                }
            }
            $bargain_max_price = !empty($bargain_price) ? max($bargain_price) : 0;
            $bargain_min_price = !empty($bargain_price) ? min($bargain_price) : 0;

            // 更新砍价活动
            $bargainModel = new Bargain();
            $checkAudit = self::checkAudit((int)$post['id']);
            if (false === $checkAudit) {
                $audit_status = BargainEnum::TO_BE_REVIEWED; //待审核
            } else {
                $audit_status = BargainEnum::AUDIT_PASS; //审核通过
            }
            $bargainModel->where(['id' => (int)$post['id']])->update([
                'goods_id' => $post['goods_id'],
                'time_limit' => $post['time_limit'],
                'activity_start_time' => strtotime($post['activity_start_time']),
                'activity_end_time' => strtotime($post['activity_end_time']),
                'bargain_min_price' => $bargain_min_price,
                'bargain_max_price' => $bargain_max_price,
                'share_title' => empty($post['share_title']) ? '' : $post['share_title'],
                'share_intro' => empty($post['share_intro']) ? '' : $post['share_intro'],
                'payment_where' => $post['payment_where'],
                'knife_type' => $post['knife_type'],
                'knife_price' => $knife_price,
                'status' => $post['status'],
                'audit_status' => $audit_status,
            ]);

            // 删除旧的SKU
            $bargainItemModel = new BargainItem();
            $bargainItemModel->where(['bargain_id' => (int)$post['id']])->delete();

            // 更新砍价商品SKU
            $lists = [];
            foreach ($post['floor_price'] as $key => $value) {
                foreach ($value as $K => $item) {
                    $lists[] = [
                        'bargain_id' => $post['id'],
                        'goods_id' => $key,
                        'item_id' => $K,
                        'floor_price' => $item,
                        'first_knife_price' => $post['first_knife_price'][$key][$K]
                    ];
                }
            }
            if (!empty($lists)) {
                $bargainItemModel->saveAll($lists);
            }

            Db::commit();
            return true;
        } catch (\Exception $e) {
            static::$error = $e->getMessage();
            Db::rollback();
            return false;
        }
    }

    /**
     * @notes 验证审核状态
     * @param $id
     * @return bool
     * @author suny
     * @date 2021/7/14 10:21 上午
     */
    public static function checkAudit($id)
    {

        $audit_status = Bargain::where('id', $id)->value('audit_status');
        if ($audit_status == BargainEnum::TO_BE_REVIEWED || $audit_status == BargainEnum::AUDIT_REFUND) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @notes 获取砍价活动详情
     * @param $id
     * @return array|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/14 10:21 上午
     */
    public static function getDetail($id)
    {

        $bargainModel = new Bargain();
        $bargainItemModel = new BargainItem();

        $detail = $bargainModel->field(true)
            ->where(['id' => (int)$id])
            ->with(['goods'])
            ->find();

        $goodItem = $bargainItemModel->field('t.*,gi.id as spec_item_id,
            gi.spec_value_str, gi.price as spec_item_price, gi.stock')
            ->where(['bargain_id' => (int)$id])
            ->alias('t')
            ->rightJoin('goods_item gi', 'gi.id = t.item_id')
            ->select();

        $detail['min_knife_price'] = 0;
        $detail['max_knife_price'] = 0;
        $detail['fixed_knife_price'] = 0;
        if ($detail['knife_type'] == 1) {
            $knife_price_arr = explode(',', $detail['knife_price']);
            $detail['min_knife_price'] = empty($knife_price_arr[0]) ? 0 : $knife_price_arr[0];
            $detail['max_knife_price'] = empty($knife_price_arr[1]) ? 0 : $knife_price_arr[1];
        } else {
            $detail['fixed_knife_price'] = $detail['knife_price'];
        }

        // 处理判断商品规格是否已发生变化, 没变化是true, 否则false
        $detail['is_goods_item'] = true;
        foreach ($goodItem as $item) {
            if (!$item['spec_value_str'] || $item['spec_value_str'] == ''
                || $item['spec_item_price'] == '') {
                $detail['is_goods_item'] = false;
                break;
            }
        }

        $detail['item'] = $goodItem;
        $detail['goods']['image'] = UrlServer::getFileUrl($detail['goods']['image']);
        $detail['activity_start_time'] = date('Y-m-d H:i:s', $detail['activity_start_time']);
        $detail['activity_end_time'] = date('Y-m-d H:i:s', $detail['activity_end_time']);

        return $detail;
    }

    /**
     * @notes 停止活动
     * @param $post
     * @return bool
     * @throws \think\exception\PDOException
     * @author suny
     * @date 2021/7/14 10:21 上午
     */
    public static function stop($post)
    {

        Db::startTrans();
        try {
            $bargainModel = new Bargain();
            // 切换状态
            $bargainModel->where(['id' => (int)$post['id']])
                ->update(['status' => 0]);//停止
            // 关闭活动未完成的
            $bargainLaunchModel = new BargainLaunch();
            $bargainLaunchModel->where(['bargain_id' => $post['id'], 'status' => 0])
                ->update(['status' => 2]);//砍价失败
            Db::commit();
            return true;
        } catch (\Exception $e) {
            static::$error = $e->getMessage();
            Db::rollback();
            return false;
        }
    }

    /**
     * @notes 开启活动
     * @param $post
     * @return Bargain
     * @author suny
     * @date 2021/7/14 10:21 上午
     */
    public static function start($post)
    {

        $bargainModel = new Bargain();
        // 切换状态
        return $bargainModel->where(['id' => (int)$post['id']])
            ->update(['status' => 1]);//开启
    }

    /**
     * @notes 切换状态
     * @param $post
     * @return bool
     * @throws \think\exception\PDOException
     * @author suny
     * @date 2021/7/14 10:22 上午
     */
    public static function switchStatus($post)
    {

        Db::startTrans();
        try {
            $bargainModel = new Bargain();
            // 切换状态
            $bargainModel->where(['id' => (int)$post['id']])
                ->update([$post['field'] => $post['status']]);
            // 关闭活动未完成的
            if ($post['status']) {
                $bargainLaunchModel = new BargainLaunch();
                $bargainLaunchModel->where(['bargain_id' => $post['id'], 'status' => 0])
                    ->update(['status' => 2]);//砍价失败
            }

            Db::commit();
            return true;
        } catch (\Exception $e) {
            static::$error = $e->getMessage();
            Db::rollback();
            return false;
        }
    }

    /**
     * @notes 砍价列表
     * @param $get
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author suny
     * @date 2021/7/14 10:22 上午
     */
    public static function getLaunch($get)
    {

        // 查询条件
        $where = [];
        $where[] = ['shop_id', '=', $get['shop_id']];

        if (isset($get['bargain_id']) and $get['bargain_id']) {
            $where[] = ['bargain_id', '=', (int)$get['bargain_id']];
        }

        if (isset($get['goods_name']) and $get['goods_name'] !== '') {
            $goodsModel = new GoodsModel();
            $ids = $goodsModel->field('id,name')->where([
                ['name', 'like', '%' . $get['goods_name'] . '%']
            ])->column('id');
            $where[] = ['goods_id', 'in', $ids];
        }
        if (isset($get['status']) and is_numeric($get['status'])) {
            $where[] = ['status', '=', (int)$get['status']];
        }

        if (isset($get['launch_start_time']) and $get['launch_start_time'] !== '') {
            $where[] = ['launch_start_time', '>=', strtotime($get['launch_start_time'])];
        }

        if (isset($get['launch_end_time']) and $get['launch_end_time'] !== '') {
            $where[] = ['launch_end_time', '<=', strtotime($get['launch_end_time'])];
        }

        if (isset($get['keyword_type']) and $get['keyword_type'] !== '') {
            if (isset($get['keyword']) and $get['keyword'] !== '') {
                switch ($get['keyword_type']) {
                    case 'sn':
                        $uid = User::where('sn', '=', $get['keyword'])->column('id');
                        $where[] = ['user_id', 'in', $uid];
                        break;
                    case 'nickname':
                        $uid = User::where('nickname', 'like', '%' . $get['keyword'] . '%')->column('id');
                        $where[] = ['user_id', 'in', $uid];
                        break;
                }
            }
        }
        $model = new BargainLaunch();
        $count = $model->where($where)->count('id');
        $lists = $model->field(true)
            ->where($where)
            ->with(['user.level'])
            ->order('id', 'desc')
            ->page($get['page'], $get['limit'])
            ->select()->toArray();

        foreach ($lists as &$item) {
            if (!empty($item['user']['avatar'])) {
                $item['user']['avatar'] = UrlServer::getFileUrl($item['user']['avatar']);
            }

            $item['launch_start_time'] = date('Y-m-d H:i:s', $item['launch_start_time']);
            $item['launch_end_time'] = date('Y-m-d H:i:s', $item['launch_end_time']);
            $item['domain'] = UrlServer::getFileUrl('/');
            $item['status'] = BargainLaunch::getStatusDesc($item['status']);
            $item['goods_image'] = $item['goods_snap']['image'] == "" ? $item['goods_snap']['goods_iamge'] : $item['goods_snap']['image'];
        }

        return ['count' => $count, 'lists' => $lists];
    }

    /**
     * @notes 砍价订单详情
     * @param $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/14 10:22 上午
     */
    public static function getLaunchDetail($id)
    {

        $model = new BargainLaunch();
        $detail = $model->field(true)
            ->where(['id' => (int)$id])
            ->with(['user.level'])
            ->find()->toArray();

        $detail['domain'] = UrlServer::getFileUrl();
        $detail['launch_start_time'] = date('Y-m-d H:i:s', $detail['launch_start_time']);
        $detail['launch_end_time'] = date('Y-m-d H:i:s', $detail['launch_end_time']);
        $detail['payment_where'] = $detail['bargain_snap']['payment_where'] == 1 ? '砍到底价可购买' : '任意金额可购买';
        $detail['status'] = BargainLaunch::getStatusDesc($detail['status']);
        return $detail;
    }

    /**
     * @notes 砍价订单
     * @param $launch_id
     * @param $get
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/14 10:22 上午
     */
    public static function getKnifeOrderRecord($launch_id, $get)
    {

        $model = new BargainLaunch();
        $count = $model->where(['id' => (int)$launch_id])
            ->where('order_id', '>', 0)->count('id');
        $lists = $model->field(true)
            ->where(['id' => (int)$launch_id])
            ->where('order_id', '>', 0)
            ->with(['user.level', 'order'])
            ->page($get['page'], $get['limit'])
            ->select();

        foreach ($lists as &$item) {
            $item['order_status'] = Order::getOrderStatus($item['order']['order_status']);
        }

        return ['count' => $count, 'lists' => $lists];
    }

    /**
     * @notes 砍价记录
     * @param $launch_id
     * @param $get
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/14 10:22 上午
     */
    public static function getKnifeRecord($launch_id, $get)
    {

        $model = new BargainKnife();

        $count = $model->where(['launch_id' => (int)$launch_id])->count();
        $lists = $model->field(true)
            ->where(['launch_id' => (int)$launch_id])
            ->with(['user.level'])
            ->page($get['page'], $get['limit'])
            ->select();

        foreach ($lists as &$item) {
            $item['help_time'] = date('Y-m-d H:i:s', $item['help_time']);
            $item['help_price'] = '￥' . $item['help_price'];
            $item['surplus_price'] = '￥' . $item['surplus_price'];
        }

        return ['count' => $count, 'lists' => $lists];
    }

    /**
     * @notes 砍价详情
     * @param $get
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/14 10:22 上午
     */
    public static function detail($get)
    {

        $where = [];
        $where['b.id'] = $get['id'];
        $info = Bargain::alias('b')
            ->join('goods g', 'b.goods_id = g.id')
            ->join('shop s', 's.id = b.shop_id')
            ->where($where)
            ->field('b.id,b.goods_id,b.audit_status,b.audit_remark,b.time_limit,b.payment_where,b.share_title,b.share_intro,g.image,g.name,g.min_price,g.max_price,s.id as sid,s.name as shop_name,s.type')
            ->find()->toArray();

        switch ($info['type']) {
            case 1 :
                $info['type'] = '官方自营';
                break;
            case 2 :
                $info['type'] = '入驻商家';
                break;
        }

        switch ($info['audit_status']) {
            case 0 :
                $info['audit_status'] = '待审核';
                break;
            case 1 :
                $info['audit_status'] = '审核通过';
                break;
            case 2 :
                $info['audit_status'] = '审核拒绝';
                break;
        }
        $info['image'] = UrlServer::getFileUrl($info['image']);
        return $info;
    }

    /**
     * @notes 删除
     * @param int $id
     * @return bool
     * @throws \think\exception\PDOException
     * @author suny
     * @date 2021/7/14 10:22 上午
     */
    public static function softDelete(int $id)
    {

        Db::startTrans();
        try {
            $bargainModel = new Bargain();
            $bargainModel->where(['id' => (int)$id])->update(['del' => 1]);

            // 关闭活动未完成的
            $bargainLaunchModel = new BargainLaunch();
            $bargainLaunchModel->where(['bargain_id' => $id, 'status' => 0])
                ->update(['status' => 2]);

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            static::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 关闭砍价
     * @param $id
     * @return BargainLaunch
     * @author suny
     * @date 2021/7/14 10:22 上午
     */
    public static function close($id)
    {

        $data = [
            'launch_end_time' => time(),
            'status' => BargainEnum::STATUS_FAIL,
        ];
        return BargainLaunch::where('id', $id)->update($data);
    }

    /**
     * @notes 获取各列表数量
     * @param $shop_id
     * @return array
     * @author suny
     * @date 2021/7/14 10:23 上午
     */
    public static function getNum($shop_id)
    {

        $all = Bargain::where(['del' => 0, 'shop_id' => $shop_id])->count('id');
        $unaudit = Bargain::where(['audit_status' => 0, 'del' => 0, 'shop_id' => $shop_id])->count('id');
        $audit_pass = Bargain::where(['audit_status' => 1, 'del' => 0, 'shop_id' => $shop_id])->count('id');
        $audit_refund = Bargain::where(['audit_status' => 2, 'del' => 0, 'shop_id' => $shop_id])->count('id');
        $num = [
            'all' => $all,
            'unaudit' => $unaudit,
            'audit_pass' => $audit_pass,
            'audit_refund' => $audit_refund
        ];
        return $num;
    }
}