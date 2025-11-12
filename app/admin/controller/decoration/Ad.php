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
use app\admin\logic\decoration\AdLogic;
use app\admin\validate\decoration\AdValidate;
use app\common\basics\AdminBase;
use app\common\enum\AdEnum;
use app\common\server\JsonServer;

class Ad extends AdminBase{

    /**
     * Notes:获取广告列表
     * @return \think\response\Json|\think\response\View
     * @author: cjhao 2021/4/20 11:00
     */
    public function lists(){
        if($this->request->isAjax()){
            $get = $this->request->get();
            $list = AdLogic::lists($get);
            return JsonServer::success('',$list);
        }
        return view();
    }

    /**
     * Notes:添加广告
     * @return \think\response\Json|\think\response\View
     * @author: cjhao 2021/4/20 11:00
     */
    public function add(){
        if($this->request->isAjax()){
            $post = $this->request->post();
            $post['del'] = 0;
            (new AdValidate())->goCheck('Add',$post);

            $result = AdLogic::add($post);
            if($result){
                return JsonServer::success('添加成功');
            }
            return JsonServer::error('添加失败');
        }
        $terminal = $this->request->get('terminal');
        $position_list = AdLogic::getPositionList($terminal);
        $category_list = AdLogic::getCategoryList();
        $link_page = AdEnum::getLinkPage($terminal);
        return view('',['position_list'=>$position_list,'category_list'=>$category_list,'link_page'=>$link_page]);
    }

    /**
     * Notes:编辑广告
     * @return \think\response\Json|\think\response\View
     * @author: cjhao 2021/4/20 11:01
     */
    public function edit(){
        if($this->request->isAjax()){
            $post = $this->request->post();
            $post['del'] = 0;
            (new AdValidate())->goCheck('edit',$post);
            AdLogic::edit($post);
            return JsonServer::success('修改成功');


        }
        $id = $this->request->get('id');
        $detail = AdLogic::getAd($id);
        $position_list = AdLogic::getPositionList($detail['terminal']);
        $category_list = AdLogic::getCategoryList();
        $link_page = AdEnum::getLinkPage($detail['terminal']);
        return view('',['detail'=>$detail,'position_list'=>$position_list,'category_list'=>$category_list,'link_page'=>$link_page]);
    }

    /**
     * Notes:删除广告
     * @return \think\response\Json
     * @author: cjhao 2021/4/20 11:01
     */
    public function del(){
        $id = $this->request->post('id');
        (new AdValidate())->goCheck('del');
        ADLogic::del($id);
        return JsonServer::success('删除成功');
    }

    /**
     * Notes:切换广告状态
     * @return \think\response\Json
     * @author: cjhao 2021/4/20 11:01
     */
    public function swtichStatus(){
        (new AdValidate())->goCheck('swtich');
        $post = $this->request->post();
        ADLogic::swtichStatus($post);
        return JsonServer::success('操作成功');
    }

    /**
     * Notes:获取广告位列表
     * @return \think\response\Json
     * @author: cjhao 2021/4/22 11:09
     */
    public function getPositionList(){
        $terminal = $this->request->get('terminal');
        $list = ADLogic::getPositionList($terminal);
        return JsonServer::success('',$list->toArray());
    }


}