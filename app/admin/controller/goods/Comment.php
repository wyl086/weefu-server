<?php
namespace app\admin\controller\goods;

use app\admin\logic\goods\CommentLogic;
use app\common\basics\AdminBase;
use app\common\server\JsonServer;

class Comment extends AdminBase
{
    /**
     * 评价列表
     */
    public function lists()
    {
        if($this->request->isAjax()) {
            $get = $this->request->get();
            $data = CommentLogic::lists($get);
            return JsonServer::success('', $data);
        }
        return view();
    }

    /**
     * 显示/隐藏商品评价
     */
    public function changeStatus()
    {
        $post = $this->request->post();
        $result = CommentLogic::changeStatus($post);
        if($result === true) {
            return JsonServer::success('操作成功');
        }
        return JsonServer::error(CommentLogic::getError());
    }

    /**
     * 删除
     */
    public function del()
    {
        $post = $this->request->post();
        $result = CommentLogic::del($post);
        if($result === true) {
            return JsonServer::success('删除成功');
        }
        return JsonServer::error(CommentLogic::getError());
    }
}