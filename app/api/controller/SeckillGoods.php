<?php
namespace app\api\controller;

use app\common\basics\Api;
use app\api\logic\SeckillGoodsLogic;
use app\common\server\JsonServer;
use think\response\Json;

class SeckillGoods extends Api
{
    public $like_not_need_login = ['getSeckillTime', 'getSeckillGoods'];

    /**
     * 获取秒杀时段
     */
    public function getSeckillTime()
    {
        $list = SeckillGoodsLogic::seckillTime();
        return JsonServer::success('', $list);
    }

    /**
     * 获取秒杀商品列表
     */
    public function getSeckillGoods()
    {
        $get = $this->request->get();
        $get['page_no'] = $this->page_no;
        $get['page_size'] = $this->page_size;
        $data = SeckillGoodsLogic::getSeckillGoods($get);
        return JsonServer::success('', $data);
    }
}