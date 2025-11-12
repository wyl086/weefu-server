<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\admin\controller\order;

use app\common\basics\AdminBase;
use app\common\model\order\order as OrderModel;
use app\common\server\JsonServer;
use app\admin\logic\order\OrderLogic;
use app\common\model\Client_;
use app\common\enum\PayEnum;
use app\common\model\order\OrderLog;


/**
 * Class order
 * @package app\admin\controller\order
 */
class Order extends AdminBase
{

    /**
     * @notes 订单列表
     * @return \think\response\Json|\think\response\View
     * @author suny
     * @date 2021/7/13 7:07 下午
     */
    public function lists()
    {
        $data = OrderLogic::statistics(input());
        
        // 订单状态
        $order_status = OrderModel::getOrderStatus();
        // 拼装数量统计
        $order_status   = OrderLogic::getStat($order_status);
        $all            = OrderLogic::getAll();
        
        if ($this->request->isAjax()) {
            $data['statistics'] = [
                'all'           => $all,
                'order_status'  => $order_status,
            ];
            return JsonServer::success('', $data);
        }
        
        return view('', [
            'all'           => $all,
            'statistics'    => $data,
            'order_status'  => $order_status,
            'order_type'    => OrderModel::getOrderType(true),
            'order_source'  => Client_::getClient(),
            'pay_way'       => PayEnum::getPayWay(),
            'delivery_type' => OrderModel::getDeliveryType(true),
        ]);
    }

    /**
     * @notes 订单详情
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author suny
     * @date 2021/7/13 7:07 下午
     */
    public function detail()
    {

        $id = $this->request->get('id');
        $detail = OrderLogic::getDetail($id);
        $order_log = OrderLog::getOrderLog($id);
        return view('', [
            'detail' => $detail,
            'logs' => $order_log
        ]);
    }

    /**
     * @notes 物流信息
     * @return \think\response\View
     * @author suny
     * @date 2021/7/13 7:07 下午
     */
    public function express()
    {
        $id = $this->request->get('id');
        $detail = OrderLogic::getDetail($id);
        $detail['shipping'] = OrderLogic::shippingInfo($detail['id']);
        return view('', [
            'detail' => $detail
        ]);
    }


    /**
     * @notes 导出Excel
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/4/24 10:20
     */
    public function export()
    {
        $params = $this->request->get();
        $result = OrderLogic::statistics($params, true);
        if(false === $result) {
            return JsonServer::error(OrderLogic::getError() ?: '导出失败');
        }
        return JsonServer::success('', $result);
    }
}