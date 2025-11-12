<?php


namespace app\shop\controller\finance;


use app\common\basics\ShopBase;
use app\common\server\JsonServer;
use app\shop\logic\finance\SettlementLogic;
use think\facade\View;

/**
 * 商家结算
 * Class Settlement
 * @package app\shop\controller\finance
 */
class Settlement extends ShopBase
{
    /**
     * @Notes: 结算列表
     * @Author: 张无忌
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $lists = SettlementLogic::lists($get, $this->shop_id);
            return JsonServer::success('获取成功', $lists);
        }

        $statistics = SettlementLogic::statistics($this->shop_id);
        View::assign('statistics', $statistics);
        return view();
    }

    /**
     * @Notes: 提交结算
     * @Author: 张无忌
     */
    public function add()
    {
        $res = SettlementLogic::add($this->shop_id);
        if ($res === false) {
            $message = SettlementLogic::getError() ?: '结算失败';
            return JsonServer::error($message);
        }

        return JsonServer::success('结算成功');
    }

    /**
     * @Notes: 结算详细
     * @Author: 张无忌
     */
    public function detail()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $lists = SettlementLogic::detail($get);
            return JsonServer::success('获取成功', $lists);
        }

        $settle_id = $this->request->get('settle_id');
        View::assign('settle_id', $settle_id);
        return view();
    }


    /**
     * @notes 导出Excel
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/4/24 11:57
     */
    public function export()
    {
        $params = $this->request->get();
        $result = SettlementLogic::lists($params, $this->shop_id, true);
        if(false === $result) {
            return JsonServer::error(SettlementLogic::getError() ?: '导出失败');
        }
        return JsonServer::success('', $result);
    }
}