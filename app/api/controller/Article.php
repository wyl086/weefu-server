<?php


namespace app\api\controller;



use app\api\logic\ArticleLogic;
use app\common\basics\Api;
use app\common\server\JsonServer;

class Article extends Api
{
    public $like_not_need_login = ['category', 'lists', 'detail'];
    /**
     * @Notes: 文章分类
     * @Author: 张无忌
     */
    public function category()
    {
        $get = $this->request->get();
        $lists = ArticleLogic::category($get);
        return JsonServer::success('获取成功', $lists);
    }

    /**
     * @Notes: 文章列表
     * @Author: 张无忌
     */
    public function lists()
    {
        $get = $this->request->get();
        $get['page_no'] = $this->page_no;
        $get['page_size'] = $this->page_size;
        $lists = ArticleLogic::lists($get);
        return JsonServer::success('获取成功', $lists);
    }

    /**
     * @Notes: 文章详细
     * @Author: 张无忌
     */
    public function detail()
    {
        $id = $this->request->get('id');
        $detail = ArticleLogic::detail($id);
        return JsonServer::success('获取成功', $detail);
    }
}