<?php

namespace app\shop\controller\bargain;

use app\common\basics\ShopBase;
use app\common\server\ConfigServer;
use app\common\server\JsonServer;
use app\shop\logic\bargain\BargainLogic;
use think\facade\View;
use app\shop\validate\BargainValidate;

/**
 * Class Bargain
 * @package app\shop\controller\bargain
 */
class Bargain extends ShopBase
{

    /**
     * @notes 砍价活动 商品列表
     * @return string|\think\response\Json
     * @author suny
     * @date 2021/7/14 10:13 上午
     */
    public function activity()
    {

        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $get['shop_id'] = $this->shop_id;
            $lists = BargainLogic::activity($get);
            return JsonServer::success('获取成功', $lists);
        }
        $num = BargainLogic::getNum($this->shop_id);
        View::assign('num', $num);
        return View::fetch('bargain/activity');
    }

    /**
     * @notes 新增砍价活动 商品
     * @return string|\think\response\Json
     * @author suny
     * @date 2021/7/14 10:13 上午
     */
    public function add()
    {

        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $post['shop_id'] = $this->shop_id;
            (new BargainValidate())->goCheck('add', $post);
            $result = BargainLogic::add($post);
            if ($result) {
                return JsonServer::success('新增成功');
            } else {
                $error = BargainLogic::getError() ?? '新增失败';
                return JsonServer::error($error);
            }
        }
        return View::fetch('bargain/add');
    }

    /**
     * @notes 编辑活动 商品
     * @return string|\think\response\Json
     * @author suny
     * @date 2021/7/14 10:14 上午
     */
    public function edit()
    {

        if ($this->request->isAjax()) {
            $post = $this->request->post();
            (new BargainValidate())->goCheck('edit', $post);
            if (BargainLogic::edit($post)) {
                return JsonServer::success('编辑成功');
            } else {
                $error = BargainLogic::getError() ?? '编辑失败';
                return JsonServer::error($error);
            }
        }

        $id = $this->request->get('id');
        $detail = BargainLogic::getDetail($id);
        View::assign('detail', $detail);
        return View::fetch('bargain/edit');
    }

    /**
     * @notes 停止活动
     * @return \think\response\Json
     * @author suny
     * @date 2021/7/14 10:14 上午
     */
    public function stop()
    {

        if ($this->request->isAjax()) {
            $post = $this->request->post();
            if (BargainLogic::stop($post)) {
                return JsonServer::success('更新成功');
            } else {
                $error = BargainLogic::getError() ?? '更新失败';
                return JsonServer::error($error);
            }
        }
    }

    /**
     * @notes 开启活动
     * @return \think\response\Json
     * @author suny
     * @date 2021/7/14 10:14 上午
     */
    public function start()
    {

        if ($this->request->isAjax()) {
            $post = $this->request->post();
            if (BargainLogic::start($post)) {
                return JsonServer::success('更新成功');
            } else {
                $error = BargainLogic::getError() ?? '更新失败';
                return JsonServer::error($error);
            }
        }
    }

    /**
     * @notes 砍价订单列表
     * @return string|\think\response\Json
     * @author suny
     * @date 2021/7/14 10:14 上午
     */
    public function launch()
    {

        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $get['shop_id'] = $this->shop_id;
            $lists = BargainLogic::getLaunch($get);
            return JsonServer::success('OK', $lists);
        }

        $bargain_id = $this->request->get('bargain_id', 0);
        View::assign('bargain_id', $bargain_id);
        return View::fetch('bargain/launch');
    }

    /**
     * @notes 砍价订单详情
     * @return string
     * @author suny
     * @date 2021/7/14 10:14 上午
     */
    public function detail()
    {

        $id = $this->request->get('id');
        $detail = BargainLogic::getLaunchDetail($id);
        View::assign('detail', $detail);
        return View::fetch('bargain/detail');
    }

    /**
     * @notes 砍价订单记录
     * @return \think\response\Json
     * @author suny
     * @date 2021/7/14 10:14 上午
     */
    public function knifeOrderRecord()
    {

        $launch_id = $this->request->get('launch_id');
        $get = $this->request->get();
        $lists = BargainLogic::getKnifeOrderRecord($launch_id, $get);
        return JsonServer::success('获取成功', $lists);
    }

    /**
     * @notes 砍价助力记录
     * @return \think\response\Json
     * @author suny
     * @date 2021/7/14 10:14 上午
     */
    public function knifeRecord()
    {

        $launch_id = $this->request->get('launch_id');
        $get = $this->request->get();
        $lists = BargainLogic::getKnifeRecord($launch_id, $get);
        return JsonServer::success('获取成功', $lists);
    }

    /**
     * @notes 砍价活动详情
     * @return \think\response\View
     * @author suny
     * @date 2021/7/14 10:14 上午
     */
    public function bargain_detail()
    {

        $get = $this->request->get();
        $detail = BargainLogic::detail($get);
        View::assign('detail', $detail);
        return View('bargain/bargain_detail');
    }

    /**
     * @notes 删除
     * @return \think\response\Json
     * @author suny
     * @date 2021/7/14 10:14 上午
     */
    public function del()
    {

        if ($this->request->isAjax()) {
            $id = $this->request->post('id');
            if (BargainLogic::softDelete($id)) {
                return JsonServer::success('删除成功');
            } else {
                $error = BargainLogic::getError() ?? '删除失败';
                return JsonServer::error($error);
            }
        }
    }

    /**
     * @notes 结束砍价
     * @return \think\response\Json
     * @author suny
     * @date 2021/7/14 10:14 上午
     */
    public function close()
    {

        if ($this->request->isAjax()) {
            $id = $this->request->post('id');
            if (BargainLogic::close($id)) {
                return JsonServer::success('结束砍价成功');
            } else {
                $error = BargainLogic::getError() ?? '结束砍价失败';
                return JsonServer::error($error);
            }
        }
    }
}