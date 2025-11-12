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


use app\api\logic\SubscribeLogic;
use app\common\basics\Api;
use app\common\server\JsonServer;

class Subscribe extends Api
{
    public $like_not_need_login = ['lists'];

    public function lists()
    {
        $scene = $this->request->get('scene');
        if (!$scene) {
            return JsonServer::error('缺少场景scene参数');
        }
        $lists = SubscribeLogic::lists($scene);
        return JsonServer::success('获取成功', $lists);
    }
}