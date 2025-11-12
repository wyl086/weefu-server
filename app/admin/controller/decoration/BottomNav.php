<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\admin\controller\decoration;
use app\admin\logic\decoration\BottomNavLogic;
use app\common\basics\AdminBase;
use app\common\server\JsonServer;

class BottomNav extends AdminBase{

    /**
     * Notes:获取底部导航
     * @return \think\response\View
     * @author: cjhao 2021/4/22 11:45
     */
    public function lists(){
        if($this->request->isAjax()){
            $list = BottomNavLogic::lists();
            return JsonServer::success('',$list);
        }
        return view();
    }

    /**
     * Notes:编辑底部导航
     * @return \think\response\Json|\think\response\View
     * @author: cjhao 2021/4/27 16:09
     */
    public function edit(){
        if($this->request->isAjax()){
            $post = $this->request->post();
            BottomNavLogic::edit($post);
            return JsonServer::success('修改成功');

        }
        $id = $this->request->get('id');
        $detail = BottomNavLogic::getBootomNav($id);
        return view('',['detail'=>$detail]);


    }
}