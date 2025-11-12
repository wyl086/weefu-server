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


namespace app\shop\controller\after_sale;


use app\common\basics\ShopBase;
use app\common\server\JsonServer;
use app\common\model\after_sale\AfterSale as AfterSaleModel;
use app\shop\logic\after_sale\AfterSaleLogic;


/**
 * 售后退款管理
 * Class Goods
 */
class AfterSale extends ShopBase
{
    /**
     * @notes 售后列表
     * @return \think\response\Json|\think\response\View
     * @author suny
     * @date 2021/7/14 10:12 上午
     */
    public function lists()
    {

        if ($this->request->isAjax()) {
            $get = $this->request->get();
            return JsonServer::success('', AfterSaleLogic::list($get, $this->shop_id));
        }
        $data = AfterSaleLogic::list([], $this->shop_id);
        // 售后状态
        $status = AfterSaleModel::getStatusDesc(true);
        $status = AfterSaleLogic::getStatus($status, $this->shop_id);
        $all = AfterSaleLogic::getAll($this->shop_id);
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
     * @date 2021/7/14 10:13 上午
     */
    public function detail()
    {

        $id = $this->request->get('id');
        $detail = AfterSaleLogic::getDetail($id,$this->shop_id);
        return view('', [
            'detail' => $detail
        ]);
    }

    /**
     * @notes 同意
     * @return \think\response\Json
     * @author suny
     * @date 2021/7/14 10:13 上午
     */
    public function agree()
    {

        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $data = AfterSaleLogic::agree($post['id'], $this->shop_id);
            if($data !== false){
                return JsonServer::success('操作成功');
            }else{
                return JsonServer::error('该退款申请已撤销');
            }
        }
    }

    /**
     * @notes 拒绝
     * @return \think\response\Json
     * @author suny
     * @date 2021/7/14 10:13 上午
     */
    public function refuse()
    {

        if ($this->request->isAjax()) {
            $post = $this->request->post();
            AfterSaleLogic::refuse($post, $this->shop_id);
            return JsonServer::success('操作成功');
        }

    }

    /**
     * @notes 收货
     * @return \think\response\Json
     * @author suny
     * @date 2021/7/14 10:13 上午
     */
    public function take()
    {

        if ($this->request->isAjax()) {
            $post = $this->request->post();
            AfterSaleLogic::takeGoods($post, $this->admin_id);
            return JsonServer::success('操作成功');
        }
    }

    /**
     * @notes 拒绝收货
     * @return \think\response\Json
     * @author suny
     * @date 2021/7/14 10:13 上午
     */
    public function refuseGoods()
    {

        if ($this->request->isAjax()) {
            $post = $this->request->post();
            AfterSaleLogic::refuseGoods($post, $this->admin_id);
            return JsonServer::success('操作成功');
        }
    }


    /**
     * @notes 确认退款
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author suny
     * @date 2021/7/14 10:13 上午
     */
    public function confirm()
    {

        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $confirm = AfterSaleLogic::confirm($post, $this->admin_id);
            if($confirm !== true){
                return JsonServer::error($confirm);
            }
            return JsonServer::success('操作成功');
        }
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
        $result = AfterSaleLogic::list($params, $this->shop_id, true);
        if(false === $result) {
            return JsonServer::error(AfterSaleLogic::getError() ?: '导出失败');
        }
        return JsonServer::success('', $result);
    }
}