<?php
namespace app\admin\controller\setting;

use app\common\basics\AdminBase;
use app\common\server\ConfigServer;
use app\common\server\JsonServer;

class Register extends AdminBase
{
    public function index()
    {
        $config = [
            // 'wechat_h5' => ConfigServer::get('login', 'wechat_h5', 0),
            'captcha' => ConfigServer::get('register', 'captcha', 0),
            'growth' => ConfigServer::get('register', 'growth', 0)
        ];
        return view('', [
            'config' => $config
        ]);
    }

    public function set()
    {
        $post = $this->request->post();
        // ConfigServer::set('login','wechat_h5', $post['wechat_h5']);
        ConfigServer::set('register','captcha', $post['captcha']);
        ConfigServer::set('register','growth', $post['growth']);
        return JsonServer::success('设置成功');
    }
}