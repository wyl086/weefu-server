<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\admin\controller\integral;


use app\admin\logic\integral\IntegralOrderLogic;
use app\admin\validate\integral\IntegralOrderValidate;
use app\common\basics\AdminBase;
use app\common\enum\IntegralGoodsEnum;
use app\common\enum\IntegralOrderEnum;
use app\common\server\JsonServer;

class IntegralOrder extends AdminBase
{
    /**
     * @notes 兑换订单列表
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2022/3/3 10:38 上午
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            return JsonServer::success('', IntegralOrderLogic::lists($get));
        }

        // 订单状态
        $order_status = IntegralOrderEnum::getOrderStatus(true);
        // 兑换类型
        $type = IntegralGoodsEnum::getTypeDesc(true);

        return view('', [
            'order_status' => $order_status,
            'type' => $type,
        ]);
    }

    /**
     * @notes 兑换订单详情
     * @return \think\response\View
     * @author ljj
     * @date 2022/3/3 11:10 上午
     */
    public function detail()
    {
        $id = $this->request->get('id');
        $detail = IntegralOrderLogic::detail($id);
        return view('', [
            'detail' => $detail,
        ]);
    }

    /**
     * @notes 发货详情
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2022/3/3 11:48 上午
     */
    public function delivery()
    {
        $id = $this->request->get('id');
        $detail = IntegralOrderLogic::deliveryDetail($id);
        $express = IntegralOrderLogic::express();
        return view('', [
            'detail' => $detail,
            'express' => $express
        ]);
    }

    /**
     * @notes 发货操作
     * @return \think\response\Json|void
     * @author ljj
     * @date 2022/3/3 2:53 下午
     */
    public function deliveryHandle()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            (new IntegralOrderValidate())->goCheck('deliveryHandle', $post);
            $result = IntegralOrderLogic::deliveryHandle($post,$this->adminId);
            if (true !== $result) {
                return JsonServer::error($result);
            }
            return JsonServer::success('发货成功');
        }
    }

    /**
     * @notes 物流信息
     * @return \think\response\View
     * @author ljj
     * @date 2022/3/3 3:32 下午
     */
    public function express()
    {
        $id = $this->request->get('id');
        $detail = IntegralOrderLogic::detail($id);
        $detail['shipping'] = IntegralOrderLogic::shippingInfo($detail['id']);
        return view('', [
            'detail' => $detail
        ]);
    }

    /**
     * @notes 确认收货
     * @return \think\response\Json|void
     * @author ljj
     * @date 2022/3/3 3:39 下午
     */
    public function confirm()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            (new IntegralOrderValidate())->goCheck('confirm', $post);
            IntegralOrderLogic::confirm($post['id'],$this->adminId);
            return JsonServer::success('确认成功');
        }
    }

    /**
     * @notes 取消订单
     * @return \think\response\Json|void
     * @author 段誉
     * @date 2022/3/3 18:00
     */
    public function cancel()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            (new IntegralOrderValidate())->goCheck('cancel', $post);
            IntegralOrderLogic::cancel($post['id']);
            return JsonServer::success('取消成功');
        }
    }

}