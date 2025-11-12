<?php


namespace app\shop\controller\finance;


use app\common\basics\ShopBase;
use app\common\server\JsonServer;
use app\shop\logic\finance\ShopLogic;

/**
 * 账号明细
 * Class Shop
 * @package app\shop\controller\finance
 */
class Shop extends ShopBase
{

    /**
     * @Notes: 账户明细列表
     * @Author: 张无忌
     */
    public function account()
    {
        if($this->request->isAjax()){
            $get= $this->request->get();
            $lists = ShopLogic::account($get, $this->shop_id);
            return JsonServer::success('获取成功', $lists);
        }

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
        $result = ShopLogic::account($params, $this->shop_id, true);
        if(false === $result) {
            return JsonServer::error(ShopLogic::getError() ?: '导出失败');
        }
        return JsonServer::success('', $result);
    }

}