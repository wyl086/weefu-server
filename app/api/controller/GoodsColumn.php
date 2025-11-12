<?php
namespace app\api\controller;

use app\common\basics\Api;
use app\common\server\JsonServer;
use app\api\logic\GoodsColumnLogic;

class GoodsColumn extends Api
{
    public $like_not_need_login = ['getGoodsColumnList'];

    /**
     * 获取商品栏目列表
     */
    public function getGoodsColumnList()
    {
        if($this->request->isGet()) {
            $list = GoodsColumnLogic::getGoodsColumnList();
            return JsonServer::success('获取栏目列表成功', $list);
        }
        return JsonServer::error('请求方式错误');
    }
}