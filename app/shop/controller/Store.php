<?php


namespace app\shop\controller;


use app\common\basics\ShopBase;
use app\common\server\ConfigServer;
use app\common\server\JsonServer;
use app\shop\logic\StoreLogic;

class Store extends ShopBase
{
    /**
     * @Notes: 商家设置
     * @Author: 张无忌
     */
    public function index()
    {
        return view('', [
            'detail' => StoreLogic::detail($this->shop_id),
            'tx_map_key' => ConfigServer::get('map', 'tx_map_key')
        ]);
    }

    /**
     * @Notes: 编辑商家
     * @Author: 张无忌
     */
    public function edit()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $res = StoreLogic::edit($post);
            if ($res === false) {
                $error = StoreLogic::getError() ?: '更新失败';
                return JsonServer::error($error);
            }
            return JsonServer::success('更新成功');
        }
        return JsonServer::error('异常');
    }
}