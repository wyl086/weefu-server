<?php
namespace app\api\controller;

use app\common\basics\Api;
use app\common\server\JsonServer;
use app\api\logic\GoodsBrandLogic;

class GoodsBrand extends Api
{
    public $like_not_need_login = ['getGoodsBrandList'];

    /**
     * 获取品牌列表
     */
    public function getGoodsBrandList()
    {
        if($this->request->isGet()) {
            $list = GoodsBrandLogic::getGoodsBrandList();
            return JsonServer::success('获取成功', $list);
        }
        return JsonServer::error('请求方式错误');
    }
}