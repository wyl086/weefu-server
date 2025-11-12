<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\admin\controller\finance;

use app\admin\logic\finance\ShopSettlementLogic;
use app\admin\logic\finance\ShopWithdrawalLogic;
use app\common\basics\AdminBase;
use app\common\server\JsonServer;

use think\facade\View;

/**
 * 财务-商家相关
 * Class Shop
 * @package app\admin\controller\finance
 */
class Shop extends AdminBase
{
    /**
     * @Notes: 商家提现列表
     * @Author: 张无忌
     */
    public function withdrawal()
    {
        if($this->request->isAjax()){
            $get= $this->request->get();
            $lists = ShopWithdrawalLogic::lists($get);
            return JsonServer::success('获取成功', $lists);
        }

        View::assign('summary', ShopWithdrawalLogic::summary());
        View::assign('statistics', ShopWithdrawalLogic::statistics());
        return view();
    }

    /**
     * @Notes: 商家提现详细
     * @Author: 张无忌
     * @return \think\response\View
     */
    public function withdrawalDetail()
    {
        $id = $this->request->get('id');
        View::assign('detail', ShopWithdrawalLogic::detail($id));
        return view();
    }

    /**
     * @Notes: 商家提现统计
     * @Author: 张无忌
     */
    public function withdrawalStatistics()
    {
        if ($this->request->isAjax()) {
            $statistics = ShopWithdrawalLogic::statistics();
            return JsonServer::success('获取成功', $statistics);
        }

        return JsonServer::error('请求异常');
    }

    /**
     * @Notes: 审核商家提现
     * @Author: 张无忌
     */
    public function withdrawalExamine()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $res = ShopWithdrawalLogic::examine($post);
            if ($res === false) {
                $error = ShopWithdrawalLogic::getError() ?: '审核失败';
                return JsonServer::error($error);
            }

            return JsonServer::success('审核成功');
        }

        return view();
    }

    /**
     * @Notes: 商家提现转账
     * @Author: 张无忌
     */
    public function withdrawalTransfer()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $res = ShopWithdrawalLogic::transfer($post);
            if ($res === false) {
                $error = ShopWithdrawalLogic::getError() ?: '审核失败';
                return JsonServer::error($error);
            }

            return JsonServer::success('审核成功');
        }

        $id = $this->request->get('id');
        View::assign('detail', ShopWithdrawalLogic::detail($id));
        return view();
    }
    
    /**
     * @notes 在线转账
     * @return \think\response\Json|void
     * @author lbzy
     * @datetime 2023-06-07 09:48:22
     */
    function WithdrawalTransferOnline()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $res = ShopWithdrawalLogic::transfer_online($post);
            if ($res === false) {
                $error = ShopWithdrawalLogic::getError() ? : '在线转账失败';
                return JsonServer::error($error);
            }
            
            return JsonServer::success(ShopWithdrawalLogic::getError() ? : '在线转账成功');
        }
    }


    /**
     * @Notes: 商家结算列表
     * @Author: 张无忌
     */
    public function settlement()
    {
        if($this->request->isAjax()){
            $get= $this->request->get();
            $lists = ShopSettlementLogic::lists($get);
            return JsonServer::success('获取成功', $lists);
        }

        $statistics = ShopSettlementLogic::statistics();
        View::assign('statistics', $statistics);
        return view();
    }

    /**
     * @Notes: 商家结算记录
     * @Author: 张无忌
     */
    public function settlementRecord()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $lists = ShopSettlementLogic::record($get);
            return JsonServer::success('获取成功', $lists);
        }

        $shop_id = $this->request->get('shop_id');
        $statistics = ShopSettlementLogic::statistics($shop_id);
        View::assign('shop_id', $shop_id);
        View::assign('statistics', $statistics);
        return view();
    }

    /**
     * @Notes: 商家结算详细记录
     * @Author: 张无忌
     */
    public function settlementDetail()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $lists = ShopSettlementLogic::detail($get);
            return JsonServer::success('获取成功', $lists);
        }

        $settle_id = $this->request->get('settle_id');
        View::assign('settle_id', $settle_id);
        return view();
    }


    /**
     * @Notes: 账户明细列表
     * @Author: 张无忌
     */
    public function account()
    {
        if($this->request->isAjax()){
            $get= $this->request->get();
            $lists = ShopWithdrawalLogic::account($get);
            return JsonServer::success('获取成功', $lists);
        }

        return view();
    }


    /**
     * @notes 导出商家提现Excel
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/4/24 10:20
     */
    public function withdrawalExport()
    {
        $params = $this->request->get();
        $result = ShopWithdrawalLogic::lists($params, true);
        if(false === $result) {
            return JsonServer::error(ShopWithdrawalLogic::getError() ?: '导出失败');
        }
        return JsonServer::success('', $result);
    }

    /**
     * @notes 导出商家结算Excel
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/4/24 10:20
     */
    public function settlementExport()
    {
        $params = $this->request->get();
        $result = ShopSettlementLogic::lists($params, true);
        if(false === $result) {
            return JsonServer::error(ShopSettlementLogic::getError() ?: '导出失败');
        }
        return JsonServer::success('', $result);
    }


    /**
     * @notes 导出商家账户明细Excel
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/4/24 10:20
     */
    public function accountExport()
    {
        $params = $this->request->get();
        $result = ShopWithdrawalLogic::account($params, true);
        if(false === $result) {
            return JsonServer::error(ShopWithdrawalLogic::getError() ?: '导出失败');
        }
        return JsonServer::success('', $result);
    }

}