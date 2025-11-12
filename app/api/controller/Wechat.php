<?php
namespace app\api\controller;

use app\common\basics\Api;
use app\api\logic\WechatLogic;
use app\common\server\JsonServer;

class Wechat extends Api
{
    public $like_not_need_login = ['jsconfig', 'index'];

    /**
     * 微信公众号接口回调
     */
    public function index()
    {
        $params = $this->request->get('');
        WechatLogic::index($params);
    }

    public function jsConfig()
    {
        $url = $this->request->get('url');
        $result = WeChatLogic::jsConfig($url);
        if ($result['code'] != 1) {
            return JsonServer::error('',[$result]);
        }
        return JsonServer::success('', ['config' => $result['data']]);
    }
}