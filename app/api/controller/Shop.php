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
use app\api\logic\ShopLogic;


class Shop extends Api
{
    public $like_not_need_login = ['getShopInfo', 'getShopList','getNearbyShops'];

    /**
     * 店铺信息
     */
    public function getShopInfo()
    {
        if($this->request->isGet()) {
            $shopId = $this->request->get('shop_id', '', 'trim');
            $validate = Validate::rule('shop_id', 'require|integer|gt:0');
            if(!$validate->check(['shop_id'=>$shopId])) {
                return JsonServer::error($validate->getError());
            }
            $data = ShopLogic::getShopInfo($shopId, $this->user_id, input());
            return JsonServer::success('获取店铺信息成功', $data);
        }else{
            return JsonServer::error('请求类型错误');
        }
    }

    /**
     * 店铺列表
     */
    public function getShopList()
    {
        if($this->request->isGet()) {
            $get = $this->request->get();
            $get['page_no'] = $this->page_no;
            $get['page_size'] = $this->page_size;
            $data = ShopLogic::getShopList($get);
            return JsonServer::success('获取店铺列表成功', $data);
        }else{
            return JsonServer::error('请求类型错误');
        }
    }


    /**
     * @notes 附近店铺列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2022/9/20 4:29 下午
     */
    public function getNearbyShops()
    {
        if($this->request->isGet()) {
            $get = $this->request->get();
            $get['page_no'] = $this->page_no;
            $get['page_size'] = $this->page_size;
            $data = ShopLogic::getNearbyShops($get);
            return JsonServer::success('获取店铺列表成功', $data);
        }else{
            return JsonServer::error('请求类型错误');
        }
    }
}