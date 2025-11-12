<?php


namespace app\api\controller;


use app\api\logic\FootPrintLogic;
use app\common\server\JsonServer;

class Footprint
{
    public $like_not_need_login = ['lists'];

    /**
     * @Notes: 足迹列表
     * @Author: 张无忌
     */
    public function lists()
    {
        $lists = FootPrintLogic::lists();
        return JsonServer::success('获取成功', $lists);
    }
}