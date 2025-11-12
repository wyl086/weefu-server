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
use app\api\logic\ShopFollowLogic;


class ShopFollow extends Api
{
    /**
     * 店铺： 关注/取消关注
     */
    public function changeStatus()
    {
        if($this->request->isPost()) {
            $shopId = $this->request->post('shop_id', '', 'trim');
            $validate = Validate::rule('shop_id', 'require|integer|gt:0');
            if(!$validate->check(['shop_id'=>$shopId])) {
                return JsonServer::error($validate->getError());
            }
            $data= ShopFollowLogic::changeStatus($shopId, $this->user_id);
            if($data['result']) {
                return JsonServer::success($data['msg']);
            }
            return JsonServer::error('操作失败');
        }else{
            return JsonServer::error('请求方式错误');
        }
    }

    /**
     * 店铺关注列表
     */
    public function lists()
    {
        $get = $this->request->get();
        $get['user_id'] = $this->user_id;
        $get['page_no'] = $this->page_no;
        $get['page_size'] = $this->page_size;

        $data = ShopFollowLogic::lists($get);
        return JsonServer::success('', $data);
    }
}