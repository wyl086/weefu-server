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

use app\api\logic\ActivityAreaLogic;
use app\common\basics\Api;
use app\common\server\JsonServer;

/**
 * Class ActivityArea
 * @package app\api\controller
 */
class ActivityArea extends Api
{
    public $like_not_need_login = ['activityGoodsList'];

    /**
     * @notes 活动专区商品列表
     * @return \think\response\Json
     * @author suny
     * @date 2021/7/13 6:03 下午
     */
    public function activityGoodsList()
    {

        $id = $this->request->get('id');
        $list = ActivityAreaLogic::activityGoodsList($id, $this->page_no, $this->page_size);
        return JsonServer::success('获取成功', $list);
    }
}