<?php

namespace app\api\controller;

use app\api\logic\IntegralOrderLogic;
use app\api\validate\IntegralOrderValidate;
use app\api\validate\IntegralPlaceOrderValidate;
use app\common\basics\Api;
use app\common\server\JsonServer;


/**
 * 积分商城订单
 * Class IntegralOrder
 * @package app\api\controller
 */
class IntegralOrder extends Api
{

    /**
     * @notes 结算订单
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/3/2 9:51
     */
    public function settlement()
    {
        $params = $this->request->get();
        $params['user_id'] = $this->user_id;
        (new IntegralPlaceOrderValidate())->goCheck('settlement', $params);
        $result = IntegralOrderLogic::settlement($params);
        return JsonServer::success('获取成功', $result);
    }


    /**
     * @notes 提交订单
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/3/2 9:40
     */
    public function submitOrder()
    {
        $post = $this->request->post();
        $post['user_id'] = $this->user_id;
        $post['client'] = $this->client;
        (new IntegralPlaceOrderValidate())->goCheck('submit', $post);
        $result = IntegralOrderLogic::submitOrder($post);
        if(false === $result) {
            return JsonServer::error(IntegralOrderLogic::getError());
        }
        return JsonServer::success('提交成功', $result);
    }


    /**
     * @notes 订单列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/3/2 9:39
     */
    public function lists()
    {
        $type = $this->request->get('type', '');
        $result = IntegralOrderLogic::lists($this->user_id, $type, $this->page_no, $this->page_size);
        return JsonServer::success('获取成功', $result);
    }


    /**
     * @notes 订单详情
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/3/2 10:23
     */
    public function detail()
    {
        $id = $this->request->get('id/d');
        $data = ['id' => $id, 'user_id' => $this->user_id];
        (new IntegralOrderValidate())->goCheck('detail', $data);
        $result = IntegralOrderLogic::detail($id);
        return JsonServer::success('获取成功', $result);
    }


    /**
     * @notes 取消订单
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/3/3 14:57
     */
    public function cancel()
    {
        $id = $this->request->post('id/d');
        $data = ['id' => $id, 'user_id' => $this->user_id];
        (new IntegralOrderValidate())->goCheck('cancel', $data);
        $result = IntegralOrderLogic::cancel($id);
        if (false === $result) {
            return JsonServer::error(IntegralOrderLogic::getError());
        }
        return JsonServer::success('取消成功');
    }


    /**
     * @notes 确认收货
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/3/2 10:59
     */
    public function confirm()
    {
        $id = $this->request->post('id/d');
        $data = ['id' => $id, 'user_id' => $this->user_id];
        (new IntegralOrderValidate())->goCheck('confirm', $data);
        IntegralOrderLogic::confirm($id, $this->user_id);
        return JsonServer::success('确认成功');
    }


    /**
     * @notes 删除订单
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/3/2 10:59
     */
    public function del()
    {
        $id = $this->request->post('id/d');
        $data = ['id' => $id, 'user_id' => $this->user_id];
        (new IntegralOrderValidate())->goCheck('del', $data);
        IntegralOrderLogic::del($id, $this->user_id);
        return JsonServer::success('删除成功');
    }



    /**
     * @notes 查看物流
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/3/3 17:31
     */
    public function orderTraces()
    {
        $id = $this->request->get('id/d');
        $data = ['id' => $id, 'user_id' => $this->user_id];
        (new IntegralOrderValidate())->goCheck('traces', $data);
        $result = IntegralOrderLogic::orderTraces($id);
        return JsonServer::success('获取成功', $result);
    }


}
