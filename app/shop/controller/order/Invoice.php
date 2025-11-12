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
use app\common\model\order\Order;
use app\common\server\JsonServer;
use app\shop\logic\order\InvoiceLogic;
use app\shop\validate\order\OrderInvoiceValidate;


/**
 * 发票管理
 * Class Invoice
 * @package app\shop\controller\order
 */
class Invoice extends ShopBase
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
            return JsonServer::success('', InvoiceLogic::getInvoiceLists($get, $this->shop_id));
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
    public function setInvoice()
    {
        if ($this->request->isAjax()) {
            $params = (new OrderInvoiceValidate())->goCheck();
            InvoiceLogic::setInvoice($params);
            return JsonServer::success('操作成功');
        }
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
        $result = InvoiceLogic::export($params, $this->shop_id);
        if(false === $result) {
            return JsonServer::error(InvoiceLogic::getError() ?: '导出失败');
        }
        return JsonServer::success('', $result);
    }

}