<?php

namespace app\api\controller;

use app\api\logic\OrderInvoiceLogic;
use app\api\logic\OrderLogic;
use app\api\validate\OrderValidate;
use app\common\basics\Api;
use app\common\server\JsonServer;
use app\common\server\WechatMiniExpressSendSyncServer;


class  Order extends Api
{
    /**
     * @notes 下单
     * @return \think\response\Json
     * @throws \think\Exception
     * @author suny
     * @date 2021/7/13 6:11 下午
     */
    public function submitOrder()
    {
        $post = $this->request->post();
        $post['user_id'] = $this->user_id;
        $post['client'] = $this->client;
        (new OrderValidate())->goCheck('add', $post);
        $order = OrderLogic::add($post);
        if (false === $order) {
            return JsonServer::error(OrderLogic::getError());
        }
        return JsonServer::success('下单成功!', $order);
    }

    /**
     * @notes 结算页数据
     * @return \think\response\Json
     * @author suny
     * @date 2021/7/13 6:11 下午
     */
    public function settlement()
    {
        $post = $this->request->post();
        $post['user_id'] = $this->user_id;
        $post['client'] = $this->client;
        $result = OrderLogic::settlement($post);
        if($result === false) {
            return JsonServer::error(OrderLogic::getError(), [], 301);
        }
        return JsonServer::success('获取成功', $result);
    }


    /**
     * @notes 订单列表
     * @return \think\response\Json
     * @author suny
     * @date 2021/7/13 6:11 下午
     */
    public function lists()
    {
        $type = $this->request->get('type', 'all');
        $order_list = OrderLogic::getOrderList($this->user_id, $type, $this->page_no, $this->page_size);
        return JsonServer::success('获取成功', $order_list);
    }

    /**
     * @notes 获取订单详情
     * @return \think\response\Json
     * @author suny
     * @date 2021/7/13 6:11 下午
     */
    public function getOrderDetail()
    {
        $get = $this->request->get();
        (new OrderValidate())->goCheck('detail', $get);
        $detail = OrderLogic::getOrderDetail($get['id']);
        return JsonServer::success('获取成功', $detail);
    }
    
    /**
     * @notes 微信确认收货 获取详情
     * @return \think\response\Json
     * @author lbzy
     * @datetime 2023-09-05 09:51:41
     */
    function wxReceiveDetail()
    {
        return JsonServer::success('获取成功', OrderLogic::wxReceiveDetail(input('order_id/d'), $this->user_id));
    }

    /**
     * @notes 取消订单
     * @return array|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author suny
     * @date 2021/7/13 6:11 下午
     */
    public function cancel()
    {
        $order_id = $this->request->post('id');
        if (empty($order_id)) {
            return JsonServer::error('参数错误');
        }
        return OrderLogic::cancel($order_id, $this->user_id);
    }

    /**
     * @notes 确认收货
     * @return array|\think\response\Json
     * @author suny
     * @date 2021/7/13 6:11 下午
     */
    public function confirm()
    {
        $order_id = $this->request->post('id');
        if (empty($order_id)) {
            return JsonServer::error('参数错误');
        }
        return OrderLogic::confirm($order_id, $this->user_id);
    }

    /**
     * @notes 删除订单
     * @return array|\think\response\Json
     * @author suny
     * @date 2021/7/13 6:12 下午
     */
    public function del()
    {
        $order_id = $this->request->post('id');
        if (empty($order_id)) {
            return JsonServer::error('参数错误');
        }
        return OrderLogic::del($order_id, $this->user_id);
    }

    /**
     * @notes 订单支付结果页面数据
     * @return \think\response\Json
     * @author suny
     * @date 2021/7/13 6:12 下午
     */
    public function pay_result()
    {
        $id = $this->request->get('id');
        $from = $this->request->get('from');//标识：trade：父订单，order：子订单
        $result = OrderLogic::pay_result($id,$from);
        if ($result !== false) {
            return JsonServer::success('', $result);
        } else {
            return JsonServer::error('参数错误');
        }
    }

    /**
     * @notes 获取支付方式
     * @return \think\response\Json
     * @author suny
     * @date 2021/7/13 6:12 下午
     */
    public function getPayWay()
    {
        $params = $this->request->get();
        if(!isset($params['from']) || !isset($params['order_id'])) {
            return JsonServer::error('参数缺失');
        }
        $pay_way = OrderLogic::getPayWay($this->user_id, $this->client, $params);
        return JsonServer::success('', $pay_way);
    }

    /**
     * @notes 物流查询
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author suny
     * @date 2021/7/13 6:12 下午
     */
    public function orderTraces()
    {
        $order_id = $this->request->get('id');
        $tips = '参数错误';
        if ($order_id) {
            $traces = OrderLogic::orderTraces($order_id, $this->user_id);
            if ($traces) {
                return JsonServer::success('获取成功', $traces);
            }
            $tips = '暂无物流信息';
        }
        return JsonServer::error($tips);
    }

    /**
     * @notes PC获取支付状态
     * @return \think\response\Json
     * @author suny
     * @date 2021/10/29 3:52 下午
     */
    public function getPayStatus()
    {
        $id = $this->request->get('id');
        $from = $this->request->get('from');//标识：trade：父订单，order：子订单
        $result = OrderLogic::getPayStatus($id,$from);
        if ($result !== false) {
            return JsonServer::success('', $result);
        } else {
            return JsonServer::error('参数错误');
        }
    }



    /**
     * @notes 发票详情
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/4/12 9:25
     */
    public function invoice()
    {
        $id = $this->request->get('id');
        $result = OrderInvoiceLogic::getInvoiceDetailByOrderId($id);
        return JsonServer::success('获取成功', $result);
    }
    
    /**
     * @notes 微信同步发货 查询
     * @return \think\response\Json
     * @author lbzy
     * @datetime 2023-09-07 15:27:17
     */
    function wechatSyncCheck()
    {
        $id     = $this->request->get('id');
        
        $order  = \app\common\model\order\Order::where('id', $id)->where('user_id', $this->user_id)->findOrEmpty();
        
        $result = WechatMiniExpressSendSyncServer::wechatSyncCheck($order);
        
        if (! $result) {
            return JsonServer::error('获取失败');
        }
    
        return JsonServer::success('成功', $result);
    }

}
