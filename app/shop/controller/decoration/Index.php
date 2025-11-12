<?php
namespace app\shop\controller\decoration;

use app\common\basics\ShopBase;
use app\shop\logic\decoration\IndexLogic;
use app\common\server\JsonServer;

class Index extends  ShopBase
{
    public function set()
    {
        if($this->request->isAjax()) {
            $post = $this->request->post();
            $post['shop_id'] = $this->shop_id;
            if(!isset($post['logo']) || !isset($post['background'])) {
                return JsonServer::error('商家logo或背景图不能空');
            }
            if(!isset($post['pc_cover'])) {
                return JsonServer::error('PC端店铺封面不能空');
            }
            $result = IndexLogic::set($post);
            if($result === true) {
                return JsonServer::success('设置成功');
            }
            return JsonServer::error(IndexLogic::getError());
        }
        $shopSet = IndexLogic::getShopSet($this->shop_id);
        return view('', [
            'shopSet' => $shopSet
        ]);

    }
}