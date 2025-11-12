<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\admin\controller\order;

use app\common\basics\AdminBase;
use app\common\model\order\Order;
use app\common\server\JsonServer;
use app\admin\logic\order\InvoiceLogic;


/**
 * 发票管理
 * Class Invoice
 * @package app\shop\controller\order
 */
class Invoice extends AdminBase
{

    /**
     * @notes 发票列表
     * @return \think\response\Json|\think\response\View
     * @author 段誉
     * @date 2022/4/12 17:34
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            return JsonServer::success('', InvoiceLogic::getInvoiceLists($get));
        }
        return view('', [
            'order_status' => order::getOrderStatus(true)
        ]);
    }


    /**
     * @notes 开票
     * @return \think\response\Json|\think\response\View
     * @author 段誉
     * @date 2022/4/12 19:00
     */
    public function detail()
    {
        $id = $this->request->get('id/d');
        return view('detail', [
            'detail' => InvoiceLogic::detail($id)
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
        $result = InvoiceLogic::getInvoiceLists($params, true);
        if(false === $result) {
            return JsonServer::error(InvoiceLogic::getError() ?: '导出失败');
        }
        return JsonServer::success('', $result);
    }

}