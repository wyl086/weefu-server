<?php
namespace app\shop\controller\distribution;

use app\common\basics\ShopBase;
use app\common\server\JsonServer;
use app\shop\logic\distribution\OrderLogic;


class Order extends ShopBase
{
    /**
     * @notes 分销订单列表
     * @return \think\response\View
     * @author Tab
     * @date 2021/9/3 14:38
     */
    public function index()
    {
        if($this->request->isPost()) {
            $params = $this->request->post();
            $params['shop_id'] = $this->shop_id;
            $result = OrderLogic::lists($params);
            return JsonServer::success('', $result);
        }
        return view();
    }
}