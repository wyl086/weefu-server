<?php
namespace app\api\controller;

use app\common\basics\Api;
use app\api\logic\AdLogic;
use app\common\server\JsonServer;

class AdContent extends Api
{
    public $like_not_need_login = ['lists'];

    /**
     * 获取广告列表
     */
    public function lists()
    {
        $pid = $this->request->get('pid');
        $terminal = $this->request->get('terminal', '1');
        if ($pid) {
            $list = AdLogic::lists($pid, $terminal);
        } else {
            $list = [];
        }
        return JsonServer::success('', $list);
    }
}