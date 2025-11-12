<?php
namespace app\admin\controller\distribution;

use app\admin\logic\distribution\DistributionOrderLogic;
use app\common\basics\AdminBase;
use app\common\server\JsonServer;

class DistributionOrder extends AdminBase
{
    /**
     * @notes 分销订单列表
     * @return \think\response\View
     * @author Tab
     * @date 2021/9/3 16:53
     */
    public function index()
    {
        if($this->request->isPost()) {
            $params = $this->request->post();
            $result = DistributionOrderLogic::lists($params);
            return JsonServer::success('', $result);
        }
        return view();
    }
}