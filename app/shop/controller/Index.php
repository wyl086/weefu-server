<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | gitee下载：https://gitee.com/multshop_gitee
// | github下载：https://github.com/multshop-github
// | 访问官网：https://www.multshop.cn
// | 访问社区：https://home.multshop.cn
// | 访问手册：http://doc.multshop.cn
// | 微信公众号：multshop技术社区
// | multshop系列产品在gitee、github等公开渠道开源版本可免费商用，未经许可不能去除前后端官方版权标识
// |  multshop系列产品收费版本务必购买商业授权，购买去版权授权后，方可去除前后端官方版权标识
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\shop\controller;


use app\common\basics\ShopBase;
use app\common\model\shop\ShopRole;
use app\common\server\ConfigServer;
use app\common\server\JsonServer;
use app\shop\logic\index\StatLogic;
use app\shop\server\MenuServer;
use think\facade\Config;

class Index extends ShopBase
{
    /**
     * 后台前端全局界面
     * @return mixed
     */
    public function index()
    {
        return view('', [
            'config' => [
                'name' => ConfigServer::get('website', 'name'),
                'web_favicon' => ConfigServer::get('website', 'web_favicon'),
                'backstage_logo' => ConfigServer::get('website_shop', 'shop_admin_logo'),//主页左上角logo
            ],
            'menu' => MenuServer::getMenuTree($this->shop['role_id']), // 菜单渲染
            'view_app_trace' => Config::get('app.app_trace'), // 开启右上角前端示例
            'admin_name' => $this->shop['name'],//管理员名称
            'shop_name' => $this->shop_name,//门店名称
            'role_name' => (new ShopRole())->getRoleName($this->shop['role_id']), // 角色名称
        ]);
    }

    /**
     * 工作台
     * @return mixed
     */
    public function stat()
    {
        if($this->request->isAjax()){
            return JsonServer::success('', StatLogic::graphData($this->shop_id));
        }
        return view('', [
            'res' => StatLogic::stat($this->shop_id)
        ]);
    }
    
    /**
     * 工作台商品数据
     * @return mixed
     */
    public function shop()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            return JsonServer::success('', StatLogic::goodsLists($get,$this->shop_id));
        }
    }
}