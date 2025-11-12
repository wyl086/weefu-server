<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\admin\controller\after_sale;

use app\common\basics\AdminBase;
use app\common\server\JsonServer;
use app\common\model\Freight;
use app\common\model\after_sale\AfterSale as AfterSaleModel;
use app\admin\logic\after_sale\AfterSaleLogic;
use think\exception\ValidateException;

/**
 * Class AfterSale
 * @package app\admin\controller\after_sale
 */
class AfterSale extends AdminBase
{
    /**
     * @notes 售后列表
     * @return \think\response\Json|\think\response\View
     * @author suny
     * @date 2021/7/13 6:59 下午
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            return JsonServer::success('', AfterSaleLogic::list($get));
        }
        $data = AfterSaleLogic::list();
        // 售后状态
        $status = AfterSaleModel::getStatusDesc(true);
        $status = AfterSaleLogic::getStatus($status);
        $all = AfterSaleLogic::getAll();
        return view('', [
            'data' => $data,
            'all' => $all,
            'status' => $status
        ]);
    }

    /**
     * @notes 售后详情
     * @return \think\response\View
     * @author suny
     * @date 2021/7/13 6:59 下午
     */
    public function detail()
    {
        $id = $this->request->get('id');
        $detail = AfterSaleLogic::getDetail($id);
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
        $result = AfterSaleLogic::list($params, true);
        if(false === $result) {
            return JsonServer::error(AfterSaleLogic::getError() ?: '导出失败');
        }
        return JsonServer::success('', $result);
    }
}