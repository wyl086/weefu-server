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
use app\common\logic\SystemNoticeLogic;
use app\common\server\JsonServer;

class Notice extends Api
{

    /**
     * Notes: 消息中心
     * @author 段誉(2021/6/22 0:55)
     * @return \think\response\Json
     */
    public function index()
    {
        return JsonServer::success('获取成功', SystemNoticeLogic::index($this->user_id));
    }


    /**
     * Notes: 消息列表
     * @author 段誉(2021/6/22 0:55)
     */
    public function lists()
    {
        $type = $this->request->get('type');
        $lists = SystemNoticeLogic::lists($this->user_id, $type, $this->page_no, $this->page_size);
        return JsonServer::success('获取成功', $lists);
    }

}