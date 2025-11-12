<?php


namespace app\shop\controller\team;

use app\common\basics\ShopBase;
use app\common\server\JsonServer;
use app\shop\logic\team\FoundLogic;
use think\facade\View;

/**
 * 拼团记录管理
 * Class Record
 * @package app\shop\controller\team
 */
class Found extends ShopBase
{
    /**
     * @Notes: 拼团记录
     * @Author: 张无忌
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $lists = FoundLogic::lists($get, $this->shop_id);
            return JsonServer::success('获取成功', $lists);
        }

        $team_activity_id = $this->request->get('team_activity_id', 0);
        View::assign('team_activity_id', $team_activity_id);
        View::assign('statistics', FoundLogic::statistics($this->shop_id, $team_activity_id));
        return view();
    }

    /**
     * @Notes: 数据统计
     * @Author: 张无忌
     */
    public function statistics()
    {
        if ($this->request->isAjax()) {
            $team_activity_id = $this->request->get('team_activity_id', 0);
            $detail = FoundLogic::statistics($this->shop_id, $team_activity_id);
            return JsonServer::success('获取成功', $detail);
        }
        return JsonServer::error('异常');
    }


    /**
     * @Notes: 拼团记录详细
     * @Author: 张无忌
     */
    public function detail()
    {
        $id = $this->request->get('id');
        View::assign('detail', FoundLogic::detail($id));
        return view();
    }

    /**
     * @Notes: 参团列表
     * @Author: 张无忌
     */
    public function join()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $lists = FoundLogic::join($get);
            return JsonServer::success('获取成功', $lists);
        }
        return JsonServer::error('请求异常');
    }

    /**
     * @Notes: 结束拼团
     * @Author: 张无忌
     */
    public function end()
    {
        if ($this->request->isAjax()) {
            $team_id = $this->request->post('team_id');
            $res = FoundLogic::end($team_id);
            if ($res === false) {
                $message = FoundLogic::getError() ?: '结束失败';
                return JsonServer::error($message);
            }
            return JsonServer::success('结束成功');
        }
        return JsonServer::error('请求异常');
    }
}