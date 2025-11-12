<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\admin\controller\bargain;

use app\admin\logic\bargain\BargainLogic;
use app\common\server\ConfigServer;
use app\common\server\JsonServer;
use app\common\basics\AdminBase;
use app\admin\validate\BargainValidate;
use think\facade\View;

/**
 * Class Bargain
 * @package app\admin\controller\bargain
 */
class Bargain extends AdminBase
{

    /**
     * @notes 砍价活动商品列表
     * @return string|\think\response\Json
     * @author suny
     * @date 2021/7/13 6:59 下午
     */
    public function activity()
    {

        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $lists = BargainLogic::activity($get);
            return JsonServer::success('获取成功', $lists);
        }
        $num = BargainLogic::getNum();
        View::assign('num', $num);
        return View::fetch('bargain/activity');
    }

    /**
     * @notes 砍价活动商品审核
     * @return \think\response\Json|\think\response\View
     * @author suny
     * @date 2021/7/13 6:59 下午
     */
    public function audit()
    {

        if ($this->request->isAjax()) {
            $post = $this->request->post();
            (new BargainValidate())->goCheck('audit', $post);
            $result = BargainLogic::audit($post);
            if ($result) {
                return JsonServer::success('操作成功');
            }
            return JsonServer::error('操作失败');
        }
        $id = $this->request->get('id');
        View::assign('id', $id);
        return View('bargain/audit');
    }


    /**
     * @notes 违规操作
     * @return \think\response\Json|\think\response\View
     * @author suny
     * @date 2021/7/13 6:59 下午
     */
    public function violation()
    {

        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $id = $post['id'];
            (new BargainValidate())->goCheck('violation', $post);
            $result = BargainLogic::violation($post);
            if ($result) {
                return JsonServer::success('操作成功');
            }
            return JsonServer::error('操作失败');
        }
        $id = $this->request->get('id');
        View::assign('id', $id);
        return View('bargain/violation');
    }

    /**
     * @notes 砍价商品详情
     * @return \think\response\View
     * @author suny
     * @date 2021/7/13 6:59 下午
     */
    public function bargain_detail()
    {

        $get = $this->request->get();
        $detail = BargainLogic::detail($get);
        View::assign('detail', $detail);
        return View('bargain/bargain_detail');
    }

    /**
     * @notes 切换状态
     * @return \think\response\Json
     * @author suny
     * @date 2021/7/13 7:00 下午
     */
    public function switchStatus()
    {

        if ($this->request->isAjax()) {
            $post = $this->request->post();
            if (BargainLogic::switchStatus($post)) {
                return JsonServer::success('更新成功');
            } else {
                $error = BargainLogic::getError() ?? '更新失败';
                return JsonServer::error($error);
            }
        }
    }

    /**
     * @notes 砍价记录列表
     * @return string|\think\response\Json
     * @author suny
     * @date 2021/7/13 7:00 下午
     */
    public function launch()
    {

        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $lists = BargainLogic::getLaunch($get);
            if (false === $lists) {
                return JsonServer::error('结束时间必须大于开始时间！');
            } else {
                return JsonServer::success('OK', $lists);
            }
        }

        $bargain_id = $this->request->get('bargain_id', 0);
        View::assign('bargain_id', $bargain_id);
        return View::fetch('bargain/launch');
    }

    /**
     * @notes 砍价记录详情
     * @return string
     * @author suny
     * @date 2021/7/13 7:00 下午
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
     * @date 2021/7/13 7:00 下午
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
     * @date 2021/7/13 7:00 下午
     */
    public function knifeRecord()
    {

        $launch_id = $this->request->get('launch_id');
        $get = $this->request->get();
        $lists = BargainLogic::getKnifeRecord($launch_id, $get);
        return JsonServer::success('获取成功', $lists);
    }

    /**
     * @notes 砍价设置
     * @return string|\think\response\Json
     * @author suny
     * @date 2021/7/13 7:00 下午
     */
    public function set()
    {

        if ($this->request->isAjax()) {
            $payment_limit_time = $this->request->post('payment_limit_time', 0);
            try {
                ConfigServer::set('bargain', 'payment_limit_time', $payment_limit_time);
            } catch (\Exception $e) {
                return JsonServer::error('设置失败');
            }
            return JsonServer::success('设置成功');
        }
        $payment_limit_time = ConfigServer::get('bargain', 'payment_limit_time', 0);
        View::assign('payment_limit_time', $payment_limit_time);
        return View::fetch('bargain/set');
    }
}