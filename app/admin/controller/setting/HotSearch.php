<?php
namespace app\admin\controller\setting;

use app\common\basics\AdminBase;
use app\admin\logic\setting\HotSearchLogic;
use app\common\server\JsonServer;

class HotSearch extends AdminBase
{
    public function index()
    {
        $info = HotSearchLogic::info();
        return view('index', ['info' => $info]);
    }

    public function set()
    {
        if($this->request->isPost()) {
            $post = $this->request->post();
            $result = HotSearchLogic::set($post);
            if($result){
                return JsonServer::success('设置成功');
            }
            return JsonServer::error('设置失败');
        }else{
            return JsonServer::error('请求方式错误');
        }
    }
}