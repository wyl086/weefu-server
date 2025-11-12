<?php
namespace  app\admin\controller\wechat;

use app\common\basics\AdminBase;
use app\admin\logic\wechat\HfiveLogic;
use app\common\server\JsonServer;

class Hfive extends AdminBase
{
    /**
     * H5商城设置
     */
    public function set()
    {
        if($this->request->isPost()) {
            $params = $this->request->post();
            HfiveLogic::set($params);
            return JsonServer::success('设置成功');
        }
        $config = HfiveLogic::getConfig();
        return view('set', ['config' => $config]);
    }
}