<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\admin\controller;

use app\common\basics\AdminBase;
use app\common\server\JsonServer;
use app\admin\logic\StatisticsLogic;

/**
 * 数据统计
 * Class Statistics
 * @package app\admin\controller
 */
class Statistics extends AdminBase
{

    //访问分析
    public function visit()
    {
        if($this->request->isAjax()){
            $post = $this->request->post();
            $res = StatisticsLogic::visit($post);
            return JsonServer::success('',$res);
        }
        return view();
    }

    //交易分析
    public function trading()
    {
        if($this->request->isAjax()){
            $post = $this->request->post();
            $res = StatisticsLogic::trading($post);
            return JsonServer::success('',$res);
        }
        return view();
    }
    
    
    //会员分析
    public function member()
    {
        if($this->request->isAjax()){
            $post = $this->request->post();
            $res = StatisticsLogic::member($post);
            return JsonServer::success('',$res);
        }
        return view();
    }


    //商家分析
    public function shop()
    {
        if($this->request->isAjax()){
            $get= $this->request->get();
            $res = StatisticsLogic::shop($get);
            return JsonServer::success('',$res);
        }
        return view();
    }


    //商品分析
    public function goods()
    {
        if($this->request->isAjax()){
            $get= $this->request->get();
            $res = StatisticsLogic::goods($get);
            return JsonServer::success('',$res);
        }
        return view();
    }

}