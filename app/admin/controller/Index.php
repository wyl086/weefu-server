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


use app\admin\logic\index\StatLogic;
use app\admin\server\MenuServer;
use app\common\basics\AdminBase;
use app\common\model\Role;
use app\common\server\ConfigServer;
use app\common\server\JsonServer;
use think\facade\Config;

class Index extends AdminBase
{
    /**
     * 后台前端全局界面
     * @return mixed
     */
    public function index()
    {
        return view('',[
            'config' => [
                'name' => ConfigServer::get('website', 'name'),
                'web_favicon' => ConfigServer::get('website', 'web_favicon'),
                'backstage_logo' => ConfigServer::get('website_platform', 'platform_admin_logo'),//主页左上角logo
            ],
            'menu' => MenuServer::getMenuTree($this->adminUser['role_id']), // 菜单渲染
            'view_app_trace' => Config::get('app.app_trace'), // 开启右上角前端示例
            'admin_name' => $this->adminUser['name'],//管理员名称
            'role_name' => (new Role())->getRoleName($this->adminUser['role_id']), // 角色名称
        ]);
    }

    /**
     * 工作台
     * @return mixed
     */
    public function stat()
    {
        if($this->request->isAjax()){
            return JsonServer::success('', StatLogic::graphData());
        }
        return view('', [
            'res' => StatLogic::stat()
        ]);
    }
    

    /**
     * 工作台商家数据
     * @return mixed
     */
    public function shop()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            return JsonServer::success('', StatLogic::shopLists($get));
        }
    }
}