<?php
namespace  app\admin\controller\wechat;

use app\admin\logic\wechat\PcLogic;
use app\common\basics\AdminBase;
use app\common\server\JsonServer;

class Pc extends AdminBase
{
    /**
     * PC商城设置
     */
    public function set()
    {
        if($this->request->isPost()) {
            $params = $this->request->post();
            PcLogic::set($params);
            return JsonServer::success('设置成功');
        }
        $config = PcLogic::getConfig();
        return view('set', ['config' => $config]);
    }
}