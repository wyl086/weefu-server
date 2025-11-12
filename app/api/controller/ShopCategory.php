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
use app\api\logic\ShopCategoryLogic;


class ShopCategory extends Api
{
    public $like_not_need_login = ['getList'];

    /**
     * 店铺主营类目
     */
    public function getList()
    {
        if($this->request->isGet()) {
            $list = ShopCategoryLogic::getList();
            return JsonServer::success('获取成功', $list);
        }
        return JsonServer::error('请求方式错误');
    }
}