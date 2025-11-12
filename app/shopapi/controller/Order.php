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

namespace app\shopapi\controller;


use app\common\basics\ShopApi;
use app\common\server\JsonServer;
use app\shopapi\validate\VirtualDeliveryValidate;
use app\shopapi\logic\OrderLogic;
use app\shopapi\validate\OrderValidate;

/**
 * 商家移动端订单管理控制器
 * Class Order
 * @package app\shopapi\controller
 */
class Order extends ShopApi
{
    /**
     * @notes 订单列表
     * @return \think\response\Json
     * @author ljj
     * @date 2021/11/10 3:14 下午
     */
    public function lists()
    {
        $get = $this->request->get();
        $result = OrderLogic::lists($get, $this->page_no, $this->page_size, $this->shop_id);
        return JsonServer::success('获取成功', $result);
    }

    /**
     * @notes 订单详情
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/11/10 4:16 下午
     */
    public function detail()
    {
        $get = $this->request->get();
        $get['shop_id'] = $this->shop_id;
        (new OrderValidate())->goCheck('detail', $get);
        $result = OrderLogic::detail($get['id']);
        return JsonServer::success('获取成功', $result);
    }

    /**
     * @notes 取消订单
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/11/10 5:06 下午
     */
    public function cancel()
    {
        $post = $this->request->post();
        $post['shop_id'] = $this->shop_id;
        (new OrderValidate())->goCheck('cancel', $post);
        $result = OrderLogic::cancel($post['id'],$this->admin_id);
        if (true !== $result) {
            return JsonServer::error($result);
        }
        return JsonServer::success('操作成功');
    }

    /**
     * @notes 删除订单
     * @return \think\response\Json
     * @author ljj
     * @date 2021/11/10 5:37 下午
     */
    public function del()
    {
        $post = $this->request->post();
        $post['shop_id'] = $this->shop_id;
        (new OrderValidate())->goCheck('del', $post);
        OrderLogic::del($post['id'],$this->admin_id);
        return JsonServer::success('操作成功');
    }

    /**
     * @notes 修改地址
     * @return \think\response\Json
     * @author ljj
     * @date 2021/11/10 6:36 下午
     */
    public function editAddress()
    {
        $post = $this->request->post();
        $post['shop_id'] = $this->shop_id;
        (new OrderValidate())->goCheck('editAddress', $post);
        OrderLogic::editAddress($post);
        return JsonServer::success('操作成功');
    }

    /**
     * @notes 获取地址详情
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/11/13 11:41 上午
     */
    public function getAddress()
    {
        $get = $this->request->get();
        $get['shop_id'] = $this->shop_id;
        (new OrderValidate())->goCheck('getAddress', $get);
        $result = OrderLogic::getAddress($get['id']);
        return JsonServer::success('获取成功', $result);
    }

    /**
     * @notes 发货
     * @return \think\response\Json
     * @author ljj
     * @date 2021/11/11 10:27 上午
     */
    public function delivery()
    {
        $post = $this->request->post();
        $post['shop_id'] = $this->shop_id;
        (new OrderValidate())->goCheck('delivery', $post);
        $result = OrderLogic::delivery($post,$this->admin_id);
        if (true !== $result) {
            return JsonServer::error($result);
        }
        return JsonServer::success('操作成功');
    }

    /**
     * @notes 获取物流公司列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/11/13 11:46 上午
     */
    public function getExpress()
    {
        return JsonServer::success('获取成功', OrderLogic::getExpress());
    }

    /**
     * @notes 确认收货
     * @return \think\response\Json
     * @author ljj
     * @date 2021/11/11 10:56 上午
     */
    public function confirm()
    {
        $post = $this->request->post();
        $post['shop_id'] = $this->shop_id;
        (new OrderValidate())->goCheck('confirm', $post);
        OrderLogic::confirm($post['id'],$this->admin_id);
        return JsonServer::success('操作成功');
    }

    /**
     * @notes 查看物流
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/11/11 11:35 上午
     */
    public function logistics()
    {
        $get = $this->request->get();
        $get['shop_id'] = $this->shop_id;
        (new OrderValidate())->goCheck('logistics', $get);
        $result = OrderLogic::logistics($get['id']);
        return JsonServer::success('获取成功', $result);
    }


    /**
     * @notes 虚拟发货
     * @return \think\response\Json|void
     * @author 段誉
     * @date 2022/4/20 17:53
     */
    public function virtualDelivery()
    {
        $post = $this->request->post();
        (new VirtualDeliveryValidate())->goCheck();
        $result = OrderLogic::virtualDelivery($post, $this->admin_id);
        if (false == $result) {
            return JsonServer::error(OrderLogic::getError() ?: '发货失败');
        }
        return JsonServer::success('发货成功');
    }

}