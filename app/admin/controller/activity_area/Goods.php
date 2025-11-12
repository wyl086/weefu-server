<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\admin\controller\activity_area;

use app\common\basics\AdminBase;
use app\common\server\JsonServer;
use think\facade\View;
use app\admin\logic\activity_area\AreaLogic;
use app\admin\logic\activity_area\GoodsLogic;
use app\admin\validate\activity_area\ActivityGoods;

/**
 * Class Goods
 * @package app\admin\controller\activity_area
 */
class Goods extends AdminBase
{

    /**
     * @notes 活动专区商品列表
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/13 6:58 下午
     */
    public function lists()
    {

        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $list = GoodsLogic::lists($get);
            return JsonServer::success('获取成功', $list);
        }
        $activity_area = AreaLogic::getActivityAreaAll();
        $num = GoodsLogic::getNum();
        View::assign('num', $num);
        View::assign('activity_area', $activity_area);
        return View();
    }

    /**
     * @notes 活动专区商品审核
     * @return \think\response\Json|\think\response\View
     * @author suny
     * @date 2021/7/13 6:58 下午
     */
    public function audit()
    {

        if ($this->request->isAjax()) {
            $post = $this->request->post();
            (new ActivityGoods())->goCheck('audit', $post);
            $result = GoodsLogic::audit($post);
            if ($result) {
                return JsonServer::success('操作成功');
            }
            return JsonServer::error('操作失败');
        }
        $id = $this->request->get('id');
        $detail = GoodsLogic::getActivityAreaGoods($id);
        View::assign('detail', $detail);
        View::assign('id', $id);
        return View();
    }

    /**
     * @notes 违规操作
     * @return \think\response\Json|\think\response\View
     * @author suny
     * @date 2021/7/13 6:58 下午
     */
    public function violation()
    {

        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $id = $post['id'];
            (new ActivityGoods())->goCheck('violation', $post);
            $result = GoodsLogic::violation($post);
            if ($result) {
                return JsonServer::success('操作成功');
            }
            return JsonServer::error('操作失败');
        }
        $id = $this->request->get('id');
        $detail = GoodsLogic::getActivityAreaGoods($id);
        View::assign('detail', $detail);
        View::assign('id', $id);
        return View();
    }

    /**
     * @notes 活动商品详情
     * @return \think\response\View
     * @author suny
     * @date 2021/7/13 6:58 下午
     */
    public function detail()
    {

        $get = $this->request->get();
        $detail = GoodsLogic::detail($get);
        View::assign('detail', $detail);
        return View();
    }
}