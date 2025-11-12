<?php


namespace app\admin\controller\team;

use app\common\basics\AdminBase;
use app\common\server\JsonServer;
use app\admin\logic\team\FoundLogic;
use think\facade\View;

/**
 * 拼团记录管理
 * Class Record
 * @package app\shop\controller\team
 */
class Found extends AdminBase
{
    /**
     * @Notes: 拼团记录
     * @Author: 张无忌
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $lists = FoundLogic::lists($get);
            return JsonServer::success('获取成功', $lists);
        }

        View::assign('statistics', FoundLogic::statistics());
        return view();
    }

    /**
     * @Notes: 数据统计
     * @Author: 张无忌
     */
    public function statistics()
    {
        if ($this->request->isAjax()) {
            $detail = FoundLogic::statistics();
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
}