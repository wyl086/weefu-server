<?php
namespace app\api\controller;

use app\common\basics\Api;
use app\common\server\JsonServer;
use app\api\logic\HelpLogic;

class Help extends Api
{
    public $like_not_need_login = ['category', 'lists', 'detail'];

    /**
     * 帮助中心分类
     */
    public function category()
    {
        $data = HelpLogic::category();
        return JsonServer::success('获取成功', $data);
    }

    /**
     * 列表
     */
    public function lists()
    {
        $get = $this->request->get();
        $get['page_no'] = $this->page_no;
        $get['page_size'] = $this->page_size;
        $data = HelpLogic::lists($get);
        return JsonServer::success('获取成功', $data);
    }

    /**
     * 详情
     */
    public function detail()
    {
        $id = $this->request->get('id', '', 'intval');
        $data = HelpLogic::detail($id);
        return JsonServer::success('获取成功', $data);
    }
}