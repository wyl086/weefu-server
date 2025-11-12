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
use app\admin\logic\decoration\AdPositionLogic;
use app\admin\validate\decoration\AdPositionValidate;
use app\common\basics\AdminBase;
use app\common\server\JsonServer;

class AdPosition extends AdminBase{

    /**
     * Notes:获取广告位列表
     * @return \think\response\Json|\think\response\View
     * @author: cjhao 2021/4/17 14:26
     */
    public function lists(){
        if($this->request->isAjax()){
            $get = $this->request->get();
            $list = AdPositionLogic::lists($get);
            return JsonServer::success('',$list);
        }
        return view();
    }

    /**
     * Notes:添加广告位
     * @return \think\response\Json
     * @author: cjhao 2021/4/17 14:49
     */
    public function add(){
        if($this->request->isAjax()){
            $post = $this->request->post();
            $post['del'] = 0;
            //验证数据
            (new AdPositionValidate())->goCheck('Add',$post);
            //添加操作
            $result = AdPositionLogic::add($post);
            if($result){
                return JsonServer::success('添加成功');
            }
            return JsonServer::error('添加失败');
        }
        return view();
    }

    /**
     * Notes:编辑广告位
     * @return \think\response\Json
     * @author: cjhao 2021/4/17 14:52
     */
    public function edit(){
        if($this->request->isAjax()){
            $post = $this->request->post();
            $post['del'] = 0;
            //验证数据
            (new AdPositionValidate())->goCheck('Add',$post);
            AdPositionLogic::edit($post);
            return JsonServer::success('修改成功');

        }
        $id = $this->request->get('id');
        $detail = AdPositionLogic::getPosition($id);
        return view('',['detail'=>$detail]);
    }

    /**
     * Notes:删除广告位
     * @return \think\response\Json
     * @author: cjhao 2021/4/19 11:21
     */
    public function del(){

        (new AdPositionValidate())->goCheck('del');

        $id = $this->request->post('id');
        AdPositionLogic::del($id);
        return JsonServer::success('删除成功');


    }


    public function swtichStatus(){
        (new AdPositionValidate())->goCheck('swtich');
        $post = $this->request->post();
        AdPositionLogic::swtichStatus($post);
        return JsonServer::success('操作成功');


    }



}