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
namespace app\shop\logic\activity_area;

use app\common\model\activity_area\ActivityAreaGoods;
use app\common\basics\Logic;
use think\facade\Db;

/**
 * Class GoodsLogic
 * @package app\shop\logic\activity_area
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
     * @date 2021/7/14 10:17 上午
     */
    public static function lists($get)
    {

        $where[] = ['AG.del', '=', 0];
        $where[] = ['AG.shop_id', '=', $get['shop_id']];

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

        if (isset($get['goods_name']) && $get['goods_name']) {
            $where[] = ['G.name', 'like', '%' . $get['goods_name'] . '%'];
        }
        if (isset($get['activity_area']) && $get['activity_area']) {
            $where[] = ['AA.id', '=', $get['activity_area']];
        }
        $count = ActivityAreaGoods::alias('AG')
            ->join('activity_area AA', 'AG.activity_area_id = AA.id')
            ->join('goods G', 'AG.Goods_id = G.id')
            ->where($where)
            ->count();

        $lists = ActivityAreaGoods::alias('AG')
            ->join('activity_area AA', 'AG.activity_area_id = AA.id')
            ->join('goods G', 'AG.Goods_id = G.id')
            ->where($where)
            ->field('AG.id,AG.goods_id,AG.activity_area_id,AG.audit_status,AA.name as activity_area_name,G.name,G.image,G.min_price,G.max_price')
            ->order('AG.id desc')
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
     * @date 2021/7/14 10:18 上午
     */
    public static function getActivityAreaGoods($id)
    {

        return ActivityAreaGoods::where(['del' => 0, 'id' => $id])
            ->select();
    }

    /**
     * @notes 添加活动商品
     * @param $post
     * @return int|string
     * @author suny
     * @date 2021/7/14 10:18 上午
     */
    public static function add($post)
    {

        $new = time();
        $add_data = [];
        $add_data[] = [
            'activity_area_id' => $post['activity_id'],
            'goods_id' => $post['goods_id'][0],
            'item_id' => $post['item_id'][0],
            'shop_id' => $post['shop_id'],
            'audit_status' => 0, //待审核
            'status' => 1, //显示
            'del' => 0,
            'create_time' => $new,
        ];

        return ActivityAreaGoods::insertAll($add_data);
    }

    /**
     * @notes 删除活动商品
     * @param $id
     * @return ActivityAreaGoods
     * @author suny
     * @date 2021/7/14 10:18 上午
     */
    public static function del($id)
    {

        $update_data = [
            'update_time' => time(),
            'del' => 1,
        ];
        return ActivityAreaGoods::where(['id' => $id])->update($update_data);
    }

    /**
     * @notes 编辑活动商品
     * @param $post
     * @return ActivityAreaGoods
     * @author suny
     * @date 2021/7/14 10:18 上午
     */
    public static function edit($post)
    {

        $new = time();
        $update_data = [
            'activity_id' => $post['activity_id'],
            'update_time' => $new,
        ];

        return ActivityAreaGoods::where(['id' => $post['id'], 'activity_id' => $post['activity_id']])
            ->update($update_data);

    }

    /**
     * @notes 获取全部的活动专区
     * @return array
     * @author suny
     * @date 2021/7/14 10:18 上午
     */
    public static function getActivityList()
    {

        return Db::name('activity_area')
            ->where(['del' => 0])
            ->column('name', 'id');

    }

    /**
     * @notes 获取活动商品详情
     * @param $goods_id
     * @param $activity_id
     * @return array|\PDOStatement|string|\think\Collection|\think\model\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author suny
     * @date 2021/7/14 10:18 上午
     */
    public static function getActivityGoods($goods_id, $activity_id)
    {

        $activity_list = Db::name('activity_area_goods')->alias('AG')
            ->join('goods_item GI', 'AG.item_id = GI.id')
            ->where(['activity_area_id' => $activity_id, 'AG.goods_id' => $goods_id])
            ->field('AG.*,GI.price,GI.spec_value_str,GI.image,GI.price')
            ->select();
        $goods_id = $activity_list[0]['goods_id'];
        $goods = Db::name('goods')->where(['del' => 0, 'id' => $goods_id])->field('image,name')->find();

        foreach ($activity_list as &$item) {
            $item['name'] = $goods['name'];
            if (empty($item['image'])) {
                $item['image'] = $goods['image'];
            }
        }
        return $activity_list;
    }

    /**
     * @notes 获取各列表数量
     * @param $shop_id
     * @return array
     * @author suny
     * @date 2021/7/14 10:18 上午
     */
    public static function getNum($shop_id)
    {

        $unaudit = ActivityAreaGoods::where(['audit_status' => 0, 'del' => 0, 'shop_id' => $shop_id])->count('id');
        $audit_pass = ActivityAreaGoods::where(['audit_status' => 1, 'del' => 0, 'shop_id' => $shop_id])->count('id');
        $audit_refund = ActivityAreaGoods::where(['audit_status' => 2, 'del' => 0, 'shop_id' => $shop_id])->count('id');
        $num = [
            'unaudit' => $unaudit,
            'audit_pass' => $audit_pass,
            'audit_refund' => $audit_refund
        ];
        return $num;
    }
}
