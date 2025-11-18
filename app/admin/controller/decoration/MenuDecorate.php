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
use app\admin\logic\decoration\MenuDecorateLogic;
use app\admin\logic\goods\CategoryLogic;
use app\admin\validate\decoration\MenuDecorateValidate;
use app\common\basics\AdminBase;
use app\common\enum\MenuEnum;
use app\common\enum\DecorationEnum;
use app\common\server\JsonServer;
use app\common\server\ConfigServer;


class MenuDecorate extends AdminBase{

    /**
     * Notes:获取菜单列表
     * @return \think\response\Json|\think\response\View
     * @author: cjhao 2021/4/28 9:49
     */
    public function lists(){
        if($this->request->isAjax()){
            $get = $this->request->get();
            $list = MenuDecorateLogic::lists($get);
            return JsonServer::success('',$list);
        }
        $type = $this->request->get('type');
        $other_set = MenuDecorateLogic::getOtherSet($type);
        return view('',['type'=>$type,'other_set'=>$other_set]);
    }

    /**
     * Notes:新增导航
     * @return \think\response\View
     * @author: cjhao 2021/5/15 16:29
     */
    public function add(){
        if($this->request->isAjax()){
            $post = $this->request->post();
            $post['del'] = 0;
            $post['decorate_type'] = $post['type'];
            (new MenuDecorateValidate())->goCheck('Add',$post);
            $result = MenuDecorateLogic::add($post);
            if($result){
                return JsonServer::success('添加成功');
            }
            return JsonServer::error('添加失败');

        }
        $type = $this->request->get('type');
        $menu_type = MenuEnum::INDEX;
        if(2 == $type){
            $menu_type = MenuEnum::CENTRE;
        }
        $category_list = CategoryLogic::categoryTwoTree();
        return view('', ['menu_list' => $menu_type, 'category_list' => $category_list]);
    }


    /**
     * Notes:编辑菜单
     * @return \think\response\Json|\think\response\View
     * @author: cjhao 2021/5/18 17:39
     */
    public function edit(){
        if($this->request->isAjax()){
            $post = $this->request->post();
            $post['del'] = 0;
            $post['decorate_type'] = $post['type'];
            (new MenuDecorateValidate())->goCheck('edit',$post);
            $result = MenuDecorateLogic::edit($post);

            if($result){
                return JsonServer::success('修改成功');
            }
            return JsonServer::error('修改失败');
        }
        $id = $this->request->get('id');
        $detail = MenuDecorateLogic::getMenuDecorate($id);
        $type = $this->request->get('type');
        $menu_type = MenuEnum::INDEX;
        if(2 == $type){
            $menu_type = MenuEnum::CENTRE;
        }
        $category_list = CategoryLogic::categoryTwoTree();
        return view('', ['menu_list' => $menu_type, 'detail' => $detail, 'category_list' => $category_list]);
    }

    /**
     * Notes:删除菜单
     * @return \think\response\Json
     * @author: cjhao 2021/5/18 18:37
     */
    public function del(){
        $id = $this->request->post('id');
        (new MenuDecorateValidate())->goCheck('del');
        MenuDecorateLogic::del($id);
        return JsonServer::success('删除成功');
    }


    /**
     * Notes:切换菜单状态
     * @return \think\response\Json
     * @author: cjhao 2021/5/18 18:37
     */
    public function swtichStatus(){
        (new MenuDecorateValidate())->goCheck('swtich');
        $post = $this->request->post();
        MenuDecorateLogic::swtichStatus($post);
        return JsonServer::success('操作成功');
    }


    /**
     * Notes:其他设置
     * @return \think\response\Json
     * @author: cjhao 2021/5/19 14:10
     */
    public function otherSet(){
        $post = $this->request->post();
        MenuDecorateLogic::otherSet($post);
        return JsonServer::success('设置成功');
    }

    /**
     * 底部导航
     */
    public function bottomNavigation()
    {
        if($this->request->isAjax()) {
            $get = $this->request->get();
            $result = MenuDecorateLogic::bottomNavigation($get);
            return JsonServer::success('', $result);
        }

        $unSelectedTextColor = ConfigServer::get('decoration', 'navigation_setting_ust_color', '#000000');
        $selectedTextColor = ConfigServer::get('decoration', 'navigation_setting_st_color', '#000000');

        return view('', [
            'unSelectedTextColor' => $unSelectedTextColor,
            'selectedTextColor' => $selectedTextColor,
        ]);
    }

    /**
     * 编辑底部导航
     */
    public function editNavigation()
    {
        if($this->request->isAjax()) {
            $post = $this->request->post();
            $result = MenuDecorateLogic::editNavigation($post);
            if($result === true) {
                return JsonServer::success('操作成功');
            }
            return JsonServer::error(MenuDecorateLogic::getError());
        }
        $id = $this->request->get('id');
        $navigation = MenuDecorateLogic::getNavigation($id);
        return view('', [
            'navigation' => $navigation
        ]);
    }

    /**
     * 底部导航 - 其他设置
     */
    public function setNavigationSetting()
    {
        $post = $this->request->post();
        ConfigServer::set('decoration', 'navigation_setting_ust_color', $post['unSelectedTextColor']);
        ConfigServer::set('decoration', 'navigation_setting_st_color', $post['selectedTextColor']);
        // ConfigServer::set('decoration', 'navigation_setting_top_bg_image', $post['top_bg_image']);
        return JsonServer::success('设置成功');
    }

    /**
     * 商品分类布局页
     */
    public function categoryLayout() {
        if($this->request->isPost()) {
            $post = $this->request->post();
            // 这里设置值要与显示时取值不同，这里相当于所有的
            ConfigServer::set('decoration', 'layout_no', $post['layout_no']);
            return JsonServer::success('设置成功');
        }

        $category_layouts = DecorationEnum::CATEGORY_LAYOUT;
        $category_layouts_tips = DecorationEnum::CATEGORY_LAYOUT_TIPS;
        $layout_no = ConfigServer::get('decoration', 'layout_no', '1');
        return view('', [
            'category_layouts' => $category_layouts,
            'category_layouts_tips' => $category_layouts_tips,
            'layout_no' => $layout_no,
        ]);
    }

    /**
     * @Notes: 商品详情店铺信息
     * @Author: 张无忌
     */
    public function goods()
    {
        if ($this->request->post()) {
            $post = $this->request->post();
            ConfigServer::set('decoration', 'shop_hide_goods', $post['shop_hide_goods']);
            return JsonServer::success('设置成功');
        }

        return view('', [
            'shop_hide_goods' => ConfigServer::get('decoration', 'shop_hide_goods', 0)
        ]);
    }

    /**
     * @Notes: 店铺街显示隐藏
     * @Author: 张无忌
     */
    function shop()
    {
        if ($this->request->post()) {
            $post = $this->request->post();
            ConfigServer::set('decoration', 'shop_street_hide', $post['shop_street_hide']);
            return JsonServer::success('设置成功');
        }

        return view('', [
            'shop_street_hide' => ConfigServer::get('decoration', 'shop_street_hide', 1)
        ]);
    }
}