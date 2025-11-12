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
use app\admin\validate\activity_area\AreaValidate;
use app\admin\logic\activity_area\AreaLogic;
use app\admin\logic\activity_area\GoodsLogic;

/**
 * Class Area
 * @package app\admin\controller\activity_area
 */
class Area extends AdminBase
{
    /**
     * @notes 活动专区列表
     * @return \think\response\Json|\think\response\View
     * @author suny
     * @date 2021/7/13 6:57 下午
     */
    public function lists()
    {

        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $lists = AreaLogic::lists($get);
            return JsonServer::success('获取成功', $lists);
        }
        return View();
    }

    /**
     * @notes 新增活动专区
     * @return \think\response\Json|\think\response\View
     * @author suny
     * @date 2021/7/13 6:57 下午
     */
    public function add()
    {

        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $post['del'] = 0;
            (new AreaValidate())->goCheck('add', $post);
            AreaLogic::add($post);
            return JsonServer::success('添加成功');
        }
        return View();
    }

    /**
     * @notes 编辑活动专区
     * @return \think\response\Json|\think\response\View
     * @author suny
     * @date 2021/7/13 6:57 下午
     */
    public function edit()
    {

        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $post['del'] = 0;
            $post['status'] = isset($post['status']) ? 1 : 0;
            (new AreaValidate())->goCheck('edit', $post);
            AreaLogic::edit($post);
            return JsonServer::success('修改成功');
        }
        $id = $this->request->get('id');
        $detail = AreaLogic::getActivityArea($id);
        View::assign('detail', $detail);
        return View();
    }


    /**
     * @notes 删除活动专区
     * @return \think\response\Json
     * @author suny
     * @date 2021/7/13 6:57 下午
     */
    public function del()
    {

        $id = $this->request->post('id');
        (new AreaValidate())->goCheck('del', ['id' => $id]);

        return JsonServer::success('删除成功', [AreaLogic::del($id)]);
    }

}