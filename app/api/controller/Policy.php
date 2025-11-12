<?php
namespace app\api\controller;

use app\common\basics\Api;
use app\api\logic\PolicyLogic;
use app\common\server\JsonServer;

class Policy extends Api
{
    
    public $like_not_need_login = ['service', 'privacy', 'aftersale', 'userDelete'];

    /**
     * 服务协议
     */
    public function service()
    {
        $data = PolicyLogic::service();
        return JsonServer::success('获取成功', ['content' => $data]);
    }

    /**
     * 隐私政策
     */
    public function privacy()
    {
        $data = PolicyLogic::privacy();
        return JsonServer::success('获取成功', ['content' => $data]);
    }

    /**
     * 售后保障
     */
    public function afterSale()
    {
        $data = PolicyLogic::afterSale();
        return JsonServer::success('获取成功', ['content' => $data]);
    }
    
    /**
     * 用户注销
     */
    public function userDelete()
    {
        $data = PolicyLogic::userDelete();
        return JsonServer::success('获取成功', ['content' => $data]);
    }
}