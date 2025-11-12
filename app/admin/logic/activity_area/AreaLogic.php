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

use app\common\basics\Logic;
use app\common\server\JsonServer;
use think\facade\Db;
use app\common\model\activity_area\ActivityArea;
use app\common\model\activity_area\ActivityAreaGoods;
use app\common\server\UrlServer;

/**
 * Class AreaLogic
 * @package app\admin\logic\activity_area
 */
class  AreaLogic extends Logic
{

    /**
     * @notes 活动专区列表
     * @param $get
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/14 9:53 上午
     */
    public static function lists($get)
    {

        $where[] = ['del', '=', 0];
        $lists = ActivityArea::where($where)
            ->page($get['page'], $get['limit'])
            ->select();

        $count = ActivityArea::where($where)
            ->count();

        return ['count' => $count, 'lists' => $lists];
    }

    /**
     * @notes 添加活动专区
     * @param $post
     * @return int|string
     * @author suny
     * @date 2021/7/14 9:53 上午
     */
    public static function add($post)
    {

        $post['create_time'] = time();
        if (isset($post['status']) && $post['status'] == 'on') {
            $post['status'] = 1; //专区显示
        } else {
            $post['status'] = 0; //专区隐藏
        }
        $post['image'] = UrlServer::setFileUrl($post['image']);
        return ActivityArea::insert($post);
    }

    /**
     * @notes 获取活动专区
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/14 9:53 上午
     */
    public static function getActivityArea($id)
    {

        $data = ActivityArea::where(['id' => $id, 'del' => 0])->find();
        $data = $data->getData();
        $data['image'] = UrlServer::getFileUrl($data['image']);
        return $data;
    }

    /**
     * @notes 获取所有活动专区
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/14 9:53 上午
     */
    public static function getActivityAreaAll()
    {

        return ActivityArea::where(['del' => 0])
            ->select();
    }

    /**
     * @notes 编辑活动专区
     * @param $post
     * @return ActivityArea
     * @author suny
     * @date 2021/7/14 9:53 上午
     */
    public static function edit($post)
    {

        $post['image'] = UrlServer::setFileUrl($post['image']);
        return ActivityArea::update($post);
    }

    /**
     * @notes 删除活动专区
     * @param $id
     * @return bool
     * @author suny
     * @date 2021/7/14 9:54 上午
     */
    public static function del($id)
    {

        $AreaResult = ActivityArea::update(['del' => 1, 'id' => $id]);
        $AreaGoodsResult = ActivityAreaGoods::where('activity_area_id', $id)->update(['del' => 1]);
        return $AreaResult;
    }
}
