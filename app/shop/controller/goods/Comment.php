<?php
namespace app\shop\controller\goods;

use app\shop\logic\goods\CommentLogic;
use app\common\basics\ShopBase;
use app\common\server\JsonServer;

class Comment extends ShopBase
{
    /**
     * 评价列表
     */
    public function lists()
    {
        if($this->request->isAjax()) {
            $get = $this->request->get();
            $get['shop_id'] = $this->shop_id;
            $data = CommentLogic::lists($get);
            return JsonServer::success('', $data);
        }
        return view();
    }

    /**
     * 商家回复
     */
    public function reply()
    {
        $post = $this->request->post();
        $result = CommentLogic::reply($post);
        if($result === true) {
            return JsonServer::success('操作成功');
        }
        return JsonServer::error(CommentLogic::getError());
    }
}