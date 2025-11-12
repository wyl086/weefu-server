<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\admin\logic\activity_area;

use app\common\model\activity_area\ActivityAreaGoods;
use app\common\basics\Logic;
use app\common\server\UrlServer;
use think\facade\Db;

/**
 * Class GoodsLogic
 * @package app\admin\logic\activity_area
 */
class GoodsLogic extends Logic
{

    /**
     * @notes 活动专区商品列表
     * @param $get
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/14 9:54 上午
     */
    public static function lists($get)
    {

        $where[] = ['AG.del', '=', 0];
        if (!isset($get['type'])) {
            $get['type'] = 1;
        }
        switch ($get['type']) {
            case 1:
                $audit_status = ActivityAreaGoods::AUDIT_STATUS_PASS;
                break;
            case 0:
                $audit_status = ActivityAreaGoods::AUDIT_STATUS_WAIT;
                break;
            case 2:
                $audit_status = ActivityAreaGoods::AUDIT_STATUS_REFUSE;
                break;
        }
        $where[] = ['AG.audit_status', '=', $audit_status];

        if (isset($get['shop_name']) && $get['shop_name']) {
            $where[] = ['S.name', 'like', '%' . $get['shop_name'] . '%'];
        }

        if (isset($get['goods_name']) && $get['goods_name']) {
            $where[] = ['G.name', 'like', '%' . $get['goods_name'] . '%'];
        }

        if (isset($get['activity_area']) && $get['activity_area']) {
            $where[] = ['AA.id', '=', $get['activity_area']];
        }

        $count = ActivityAreaGoods::alias('AG')
            ->join('activity_area AA', 'AG.activity_area_id = AA.id')
            ->join('shop S', 'S.id = AG.shop_id')
            ->join('goods G', 'AG.Goods_id = G.id')
            ->where($where)
            ->count();

        $lists = ActivityAreaGoods::alias('AG')
            ->join('activity_area AA', 'AG.activity_area_id = AA.id')
            ->join('goods G', 'AG.Goods_id = G.id')
            ->join('shop S', 'S.id = AG.shop_id')
            ->where($where)
            ->field('AG.id,AG.goods_id,AG.activity_area_id,AG.audit_status,AG.audit_remark,AA.name as activity_area_name,G.id as gid,G.name,G.image,G.min_price,G.max_price,S.id as sid,S.name as shop_name,S.type')
            ->order('AG.id desc')
            ->page($get['page'], $get['limit'])
            ->select();

        return ['count' => $count, 'lists' => $lists];
    }

    /**
     * @notes 获取活动专区商品
     * @param $id
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/14 9:55 上午
     */
    public static function getActivityAreaGoods($id)
    {

        return ActivityAreaGoods::where(['del' => 0, 'id' => $id])
            ->select();
    }

    /**
     * @notes 审核
     * @param $post
     * @return ActivityAreaGoods
     * @author suny
     * @date 2021/7/14 9:55 上午
     */
    public static function audit($post)
    {

        $data = [
            'audit_status' => $post['review_status'],
            'audit_remark' => $post['description'],
        ];
        return ActivityAreaGoods::where(['id' => $post['id']])
            ->update($data);
    }

    /**
     * @notes 违规重审
     * @param $post
     * @return ActivityAreaGoods
     * @author suny
     * @date 2021/7/14 9:55 上午
     */
    public static function violation($post)
    {

        $data = [
            'audit_status' => 2,
            'audit_remark' => $post['description'],
        ];
        return ActivityAreaGoods::where(['id' => $post['id']])
            ->update($data);
    }

    /**
     * @notes 活动专区商品详情
     * @param $get
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/14 9:55 上午
     */
    public static function detail($get)
    {

        $where = [];
        $where['AG.id'] = $get['id'];
        $info = ActivityAreaGoods::alias('AG')
            ->join('activity_area AA', 'AG.activity_area_id = AA.id')
            ->join('goods G', 'AG.Goods_id = G.id')
            ->join('shop S', 'S.id = AG.shop_id')
            ->where($where)
            ->field('AG.id,AG.goods_id,AG.activity_area_id,AG.audit_status,AG.audit_remark,AA.name as activity_area_name,AA.image as aimage,G.id as gid,G.name,G.image,G.min_price,G.max_price,S.id as sid,S.name as shop_name,S.type')
            ->find()->toArray();
        $info['aimage'] = UrlServer::getFileUrl($info['aimage']);
        return $info;
    }

    /**
     * @notes 获取各列表数量
     * @return array
     * @author suny
     * @date 2021/7/14 9:55 上午
     */
    public static function getNum()
    {

        $unaudit = ActivityAreaGoods::where(['audit_status' => 0, 'del' => 0])->count('id');
        $audit_pass = ActivityAreaGoods::where(['audit_status' => 1, 'del' => 0])->count('id');
        $audit_refund = ActivityAreaGoods::where(['audit_status' => 2, 'del' => 0])->count('id');
        $num = [
            'unaudit' => $unaudit,
            'audit_pass' => $audit_pass,
            'audit_refund' => $audit_refund
        ];
        return $num;
    }
}