<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\api\controller;

use app\common\basics\Api;
use app\common\server\JsonServer;
use think\facade\Validate;
use app\api\logic\ShopGoodsCategoryLogic;


class ShopGoodsCategory extends Api
{
    public $like_not_need_login = ['getShopGoodsCategory'];
    
    public function getShopGoodsCategory()
    {
        if($this->request->isGet()) {
            $shopId = $this->request->get('shop_id', '', 'trim');
            $validate = Validate::rule('shop_id', 'require|integer|gt:0');
            if(!$validate->check(['shop_id'=>$shopId])) {
                return JsonServer::error($validate->getError());
            }
            $data = ShopGoodsCategoryLogic::getShopGoodsCategory($shopId);
            return JsonServer::success('获取店铺分类成功', $data);
        }else{
            return JsonServer::error('请求类型错误');
        }
    }
}