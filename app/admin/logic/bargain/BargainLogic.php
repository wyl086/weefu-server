<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\admin\logic\bargain;


use app\admin\controller\finance\Shop;
use app\common\basics\Logic;
use app\common\model\shop\Shop as ShopModel;
use app\common\model\bargain\Bargain;
use app\common\model\bargain\BargainItem;
use app\common\model\bargain\BargainKnife;
use app\common\model\bargain\BargainLaunch;
use app\common\model\goods\Goods as GoodsModel;
use app\common\model\order\Order;
use app\common\model\team_activity\TeamActivity as TeamActivityModel;
use app\common\model\user\User;
use app\common\server\UrlServer;
use think\facade\Db;
use think\Exception;

/**
 * Class BargainLogic
 * @package app\admin\logic\bargain
 */
class BargainLogic extends Logic
{
    protected static $error; //错误信息

    /**
     * @notes 砍价活动列表
     * @param $get
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/14 9:57 上午
     */
    public static function activity($get)
    {

        $where = [
            ['del', '=', 0],
        ];

        // 查询条件

        if (!empty($get['shop_name']) and $get['shop_name'] !== '') {
            $shopModel = new ShopModel();
            $ids = $shopModel->field('id,name')->where([
                ['name', 'like', '%' . $get['shop_name'] . '%']
            ])->column('id');

            $where[] = ['shop_id', 'in', $ids];
        }

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
        }

        $bargainModel = new Bargain();
        $count = $bargainModel->where($where)->count('id');
        $lists = $bargainModel->field(true)
            ->where($where)
            ->with(['goods', 'shop'])
            ->withCount(['launchPeopleNumber', 'successKnifePeopleNumber'])
            ->page($get['page'], $get['limit'])
            ->select();

        foreach ($lists as &$item) {
            $item['goods']['image'] = UrlServer::getFileUrl($item['goods']['image']);
            $item['activity_start_time'] = date('Y-m-d H:i:s', $item['activity_start_time']);
            $item['activity_end_time'] = date('Y-m-d H:i:s', $item['activity_end_time']);
        }

        return ['count' => $count, 'lists' => $lists];
    }

    /**
     * @notes 砍价活动审核
     * @param $post
     * @return Bargain
     * @author suny
     * @date 2021/7/14 9:57 上午
     */
    public static function audit($post)
    {

        $data = [
            'audit_status' => $post['review_status'],
            'audit_remark' => $post['description'],
        ];
        return Bargain::where(['id' => $post['id']])
            ->update($data);
    }

    /**
     * @notes 违规重审
     * @param $post
     * @return bool
     * @author suny
     * @date 2021/7/14 9:58 上午
     */
    public static function violation($post)
    {

        try {
            $data = [
                'audit_status' => 2,
                'audit_remark' => $post['description'],
            ];
            Bargain::where(['id' => $post['id']])
                ->update($data);
            BargainLaunch::where(['bargain_id' => $post['id']])
                ->update(['status' => 2]);//砍失败
            return true;
        } catch (Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 切换状态
     * @param $post
     * @return bool
     * @throws \think\exception\PDOException
     * @author suny
     * @date 2021/7/14 9:58 上午
     */
    public static function switchStatus($post)
    {

        Db::startTrans();
        try {
            $bargainModel = new Bargain();
            // 切换状态
            $bargainModel->where(['id' => (int)$post['id']])
                ->update([$post['field'] => $post['status']]);

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
     * @date 2021/7/14 9:58 上午
     */
    public static function getLaunch($get)
    {

        // 查询条件
        $where = [];

        //砍价订单编号bargain_sn
        if (isset($get['bargain_sn']) and $get['bargain_sn']) {
            $where[] = ['bargain_sn', '=', (int)$get['bargain_sn']];
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
            // 解决用户被删除及Indirect modification of overloaded element报错
            if (empty($item['user'])) {
                $user = [
                    'avatar' => '',
                    'sn' => '-',
                    'nickname' => '-',
                    'level' => [
                        'name' => '-'
                    ]
                ];
            } else {
                $user = $item['user'];
            }
            $user['avatar'] = UrlServer::getFileUrl($user['avatar']);
            $item['user'] = $user;
            $item['launch_start_time'] = date('Y-m-d H:i:s', $item['launch_start_time']);
            $item['launch_end_time'] = date('Y-m-d H:i:s', $item['launch_end_time']);
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
     * @date 2021/7/14 9:58 上午
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
        $detail['payment_where'] = $detail['bargain_snap']['payment_where'] == 1 ? '任意金额购买' : '固定金额购买';
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
     * @date 2021/7/14 9:59 上午
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
            $item['user']['avatar'] = UrlServer::getFileUrl($item['user']['avatar']);
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
     * @date 2021/7/14 9:59 上午
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
            $item['user']['avatar'] = UrlServer::getFileUrl($item['user']['avatar']);
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
     * @date 2021/7/14 9:59 上午
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
     * @notes 获取各列表数量
     * @return array
     * @author suny
     * @date 2021/7/14 9:59 上午
     */
    public static function getNum()
    {

        $all = Bargain::where('del', 0)->count('id');
        $unaudit = Bargain::where(['audit_status' => 0, 'del' => 0])->count('id');
        $audit_pass = Bargain::where(['audit_status' => 1, 'del' => 0])->count('id');
        $audit_refund = Bargain::where(['audit_status' => 2, 'del' => 0])->count('id');
        $num = [
            'all' => $all,
            'unaudit' => $unaudit,
            'audit_pass' => $audit_pass,
            'audit_refund' => $audit_refund
        ];
        return $num;
    }
}