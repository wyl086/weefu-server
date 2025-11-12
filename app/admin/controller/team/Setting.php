<?php


namespace app\admin\controller\team;


use app\common\basics\AdminBase;
use app\common\server\ConfigServer;
use app\common\server\JsonServer;
use think\facade\View;

class Setting extends AdminBase
{
    /**
     * @Notes: 拼团设置页
     * @Author: 张无忌
     * @return \think\response\View
     */
    public function index()
    {
        $automatic = ConfigServer::get('team', 'automatic', 0);
        View::assign('automatic', $automatic);
        return view();
    }

    /**
     * @Notes: 设置拼团
     * @Author: 张无忌
     */
    public function set()
    {
        if ($this->request->isAjax()) {
            $automatic = $this->request->post('automatic', 0, 'intval');
            ConfigServer::set('team', 'automatic', $automatic);
            return JsonServer::success('设置成功');
        }
        return JsonServer::error('异常');
    }
}