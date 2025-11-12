<?php

namespace app\api\controller;

use app\api\logic\IntegralGoodsLogic;
use app\api\validate\IntegralGoodsValidate;
use app\common\basics\Api;
use app\common\server\JsonServer;

/**
 * 积分商品
 * Class IntegralGoods
 * @package app\api\controller
 */
class IntegralGoods extends Api
{

    /**
     * @notes 积分商品列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/2/28 12:10
     */
    public function lists()
    {
        $get = $this->request->get();
        $lists = IntegralGoodsLogic::lists($this->user_id, $get, $this->page_no, $this->page_size);
        return JsonServer::success('获取成功', $lists);
    }


    /**
     * @notes 商品详情
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/2/28 15:32
     */
    public function detail()
    {
        (new IntegralGoodsValidate())->goCheck();
        $id = $this->request->get('id', '', 'trim');
        $goodsDetail = IntegralGoodsLogic::detail($id);
        return JsonServer::success('获取成功', $goodsDetail);
    }

}
