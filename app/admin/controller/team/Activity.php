<?php


namespace app\admin\controller\team;


use app\common\basics\AdminBase;
use app\common\server\JsonServer;
use app\admin\logic\team\ActivityLogic;
use think\facade\View;

class Activity extends AdminBase
{
    /**
     * @Notes: 拼团商品
     * @Author: 张无忌
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $lists = ActivityLogic::lists($get);
            return JsonServer::success('获取成功', $lists);
        }

        View::assign('statistics', ActivityLogic::statistics());
        return view();
    }

    /**
     * @notes 拼团商品的开团记录
     * @author 张无忌
     * @date 2021/7/19 11:00
     */
    public function record()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $lists = ActivityLogic::record($get);
            return JsonServer::success('获取成功', $lists);
        }


        $get = $this->request->get();
        View::assign('shop_id', $get['shop_id']);
        View::assign('team_activity_id', $get['id']);
        View::assign('recordStatistics', ActivityLogic::recordStatistics($get));
        return view();
    }

    /**
     * @Notes: 统计
     * @Author: 张无忌
     */
    public function statistics()
    {
        if ($this->request->isAjax()) {
            $detail = ActivityLogic::statistics();
            return JsonServer::success('获取成功', $detail);
        }
        return JsonServer::error('异常');
    }

    /**
     * @Notes: 审核
     * @Author: 张无忌
     */
    public function audit()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $res = ActivityLogic::audit($post);
            if ($res === false) {
                $message = ActivityLogic::getError() ?: '审核失败';
                return JsonServer::error($message);
            }
            return JsonServer::success('审核成功');
        }

        return view();
    }

    /**
     * @Notes: 违规重审
     * @Author: 张无忌
     */
    public function violation()
    {
        if ($this->request->isAjax()) {
            $id = $this->request->post('id');
            $res = ActivityLogic::violation($id);
            if ($res === false) {
                $message = ActivityLogic::getError() ?: '操作失败';
                return JsonServer::error($message);
            }
            return JsonServer::success('操作成功');
        }

        return JsonServer::error("异常");
    }

    /**
     * @Notes: 拼团信息
     * @Author: 张无忌
     * @return \think\response\View
     */
    public function details()
    {
        $id = $this->request->get('id');
        View::assign('detail', ActivityLogic::detail($id));
        return view();
    }
}