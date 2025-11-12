<?php
namespace app\api\controller;

use app\common\basics\Api;
use app\common\server\JsonServer;
use app\api\logic\MenuLogic;

class Menu extends Api
{
    public $like_not_need_login = ['lists'];

    /**
     * 获取菜单
     * type 1-首页菜单 2-个人中心菜单
     */
    public function lists()
    {
        $type = $this->request->get('type', 1);
        $list = MenuLogic::getMenu($type, $this->user_id);
        return JsonServer::success('获取成功', $list);
    }
}