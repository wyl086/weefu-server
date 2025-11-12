<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\admin\controller\common;
use app\admin\logic\common\GoodsLogic;
use app\common\basics\AdminBase;
use app\common\server\JsonServer;

class goods extends AdminBase{

    public function selectGoods(){
        if($this->request->isAjax()){
            $get = $this->request->get();
            $list = GoodsLogic::selectGoods($get);
            return JsonServer::success('',$list);
        }
        return view();
    }
}