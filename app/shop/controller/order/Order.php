<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | gitee下载：https://gitee.com/multshop_gitee
// | github下载：https://github.com/multshop-github
// | 访问官网：https://www.multshop.cn
// | 访问社区：https://home.multshop.cn
// | 访问手册：http://doc.multshop.cn
// | 微信公众号：multshop技术社区
// | multshop系列产品在gitee、github等公开渠道开源版本可免费商用，未经许可不能去除前后端官方版权标识
// |  multshop系列产品收费版本务必购买商业授权，购买去版权授权后，方可去除前后端官方版权标识
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\shop\controller\order;


use app\common\basics\ShopBase;
use app\common\model\order\OrderLog;
use app\common\model\order\Order as OrderModel;
use app\common\server\JsonServer;
use app\shop\logic\order\OrderLogic;
use app\common\model\Client_;
use app\common\enum\PayEnum;
use app\shop\validate\order\OrderPrintValidate;
use app\shop\validate\order\VerificationValidate;
use app\shop\validate\order\VirtualDeliveryValidate;
use think\response\Json;

/**
 * 订单管理
 * Class Goods
 */
class Order extends ShopBase
{

    /**
     * @notes 订单列表
     * @return Json|\think\response\View
     * @author suny
     * @date 2021/7/14 10:15 上午
     */
    public function lists()
    {
        $data = OrderLogic::statistics(input(), $this->shop_id);
    
        // 订单状态
        $order_status = OrderModel::getOrderStatus();
        // 拼装数量统计
        $order_status   = OrderLogic::getStat($order_status, $this->shop_id);
        $all            = OrderLogic::getAll($this->shop_id);
    
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

    public function totalCount()
    {
        if ($this->request->isAjax()) {
            return JsonServer::success('获取成功', OrderLogic::totalCount($this->shop_id));
        }
    }

    /**
     * @notes 订单详情
     * @return \think\response\View
     * @author suny
     * @date 2021/7/14 10:15 上午
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\db\exception\DataNotFoundException
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
     * @date 2021/7/14 10:15 上午
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
     * @notes 修改物流单号
     * @return Json|\think\response\View
     * @author lbzy
     * @datetime 2024-04-03 11:02:33
     */
    function delivery_change()
    {
        if ($this->request->isAjax()) {
            $result = OrderLogic::change_invoice_no(input());
            
            if ($result !== true) {
                return JsonServer::error((string) $result);
            }
            
            return JsonServer::success('修改成功');
        }
    
        $id = $this->request->get('id');
        $detail = OrderLogic::getDetail($id);
        $express = OrderLogic::express();
        $detail['shipping'] = OrderLogic::shippingInfo($detail['id']);
        return view('', [
            'detail' => $detail,
            'express' => $express
        ]);
    }

    /**
     * @notes 发货
     * @return \think\response\View
     * @author suny
     * @date 2021/7/14 10:15 上午
     */
    public function delivery()
    {
        $id = $this->request->get('id');
        $detail = OrderLogic::getDetail($id);
        $express = OrderLogic::express();
        return view('', [
            'detail' => $detail,
            'express' => $express
        ]);
    }

    /**
     * @notes 发货操作
     * @return Json
     * @author suny
     * @date 2021/7/14 10:15 上午
     * @throws \think\exception\DbException
     */
    public function deliveryHandle()
    {

        if ($this->request->isAjax()) {
            $post = $this->request->post();
            
            $check = (OrderLogic::checkDelivery($post));
            
            if (true !== $check) {
                return JsonServer::error((string) $check);
            }
            
            OrderLogic::deliveryHandle($post, $this->admin_id);
            return JsonServer::success('发货成功');
        }
    }

    /**
     * @notes 确认收货
     * @return Json
     * @author suny
     * @date 2021/7/14 10:16 上午
     */
    public function confirm()
    {

        if ($this->request->isAjax()) {
            $post = $this->request->post('');
            OrderLogic::confirm($post['order_id'], $this->admin_id);
            return JsonServer::success('确认成功');
        }
    }

    /**
     * @notes 取消订单
     * @return Json
     * @author suny
     * @date 2021/7/14 10:16 上午
     */
    public function cancel()
    {

        if ($this->request->isAjax()) {
            $post = $this->request->post('');
            (OrderLogic::checkCancel($post));
            OrderLogic::cancel($post['order_id'], $this->admin_id);
            return JsonServer::success('取消成功');
        }
    }

    /**
     * @notes 删除订单
     * @return Json
     * @author suny
     * @date 2021/7/14 10:16 上午
     */
    public function del()
    {

        if ($this->request->isAjax()) {
            $post = $this->request->post('');
            (OrderLogic::checkDel($post));
            OrderLogic::del($post['order_id'], $this->admin_id);
            return JsonServer::success('删除成功');
        }
    }

    /**
     * @notes 商家备注
     * @return Json
     * @throws \think\Exception
     * @author suny
     * @date 2021/7/14 10:16 上午
     */
    public function remarks()
    {

        // 获取的
        if ($this->request->isAjax() && $this->request->isGet()) {
            $get = $this->request->get();
            $detail = OrderLogic::remarks($get, 'get');
            return JsonServer::success('获取成功', [$detail]);
        }
        // 提交的
        if ($this->request->isAjax() && $this->request->isPost()) {
            $post = $this->request->post();
            $result = OrderLogic::remarks($post, 'post');
            return json(['code' => 1, 'show' => 0, 'msg' => '修改成功', 'data' => $result]);
        }
    }


    /**
     * @notes 小票打印
     * @return Json
     * @author 段誉
     * @date 2022/1/20 11:15
     */
    public function orderPrint()
    {
        $post = $this->request->post();
        $post['shop_id'] = $this->shop_id;
        (new OrderPrintValidate())->goCheck('', $post);
        $result = OrderLogic::orderPrint($post['id'], $this->shop_id);
        if (true === $result) {
            return JsonServer::success('打印成功，如未出小票，请检查打印机是否在线');
        }
        return JsonServer::error($result);
    }

    /**
     * @notes 修改地址
     * @return \think\response\View
     * @author suny
     * @date 2021/7/14 10:16 上午
     */
    public function change_address()
    {

        // 获取的
        $get = $this->request->get();
        $data = OrderLogic::change_address($get);
        $id = json_decode($data['info'], true)['id'];
        return view('', [
            'id' => $id,
            'info' => $data['info'],
            'address_tree' => $data['address_tree']
        ]);
    }

    /**
     * @notes 修改地址提交
     * @return Json
     * @author suny
     * @date 2021/7/14 10:16 上午
     */
    public function change_address_post()
    {

        // 提交的
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            OrderLogic::change_address_post($post);
            return JsonServer::success('修改地址成功');
        }
    }


    /**
     * @notes 虚拟发货
     * @return Json|void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/4/7 17:47
     */
    public function virtualDelivery()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            (new VirtualDeliveryValidate())->goCheck();
            $result = OrderLogic::virtualDelivery($post, $this->admin_id);
            if (false == $result) {
                return JsonServer::error(OrderLogic::getError() ?: '发货失败');
            }
            return JsonServer::success('发货成功');
        }
    }


    /**
     * @notes 导出Excel
     * @return Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/4/24 10:20
     */
    public function export()
    {
        $params = $this->request->get();
        $result = OrderLogic::statistics($params, $this->shop_id, true);
        if(false === $result) {
            return JsonServer::error(OrderLogic::getError() ?: '导出失败');
        }
        return JsonServer::success('', $result);
    }


    /**
     * @notes 提货核销
     * @return Json|void
     * @author 段誉
     * @date 2022/11/2 15:52
     */
    public function verification()
    {
        if ($this->request->isAjax()) {
            $params = $this->request->post();
            (new VerificationValidate())->goCheck();
            $result = OrderLogic::verification($params, $this->shop);
            if(false === $result) {
                return JsonServer::error(OrderLogic::getError() ?: '操作失败');
            }
            return JsonServer::success('操作成功');
        }
    }

}