<?php
// +----------------------------------------------------------------------
// | Multshop多商户商城系统
// +----------------------------------------------------------------------
// | author: multshopTeam
// +----------------------------------------------------------------------

namespace app\api\controller;

use app\api\logic\LiveLogic;
use app\common\basics\Api;
use app\common\server\JsonServer;


/**
 * 直播
 * Class Live
 * @package app\api\controller
 */
class Live extends Api
{

    public $like_not_need_login = ['lists', 'shopLive'];


    /**
     * @notes 直播列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2023/2/17 17:28
     */
    public function lists()
    {
        $data = LiveLogic::lists($this->page_no, $this->page_size);
        return JsonServer::success('', $data);
    }


    /**
     * @notes 商家直播间
     * @return \think\response\Json
     * @author 段誉
     * @date 2023/2/17 17:28
     */
    public function shopLive()
    {
        $shopId = $this->request->get('shop_id');
        $data = LiveLogic::shopLive($shopId);
        return JsonServer::success('', $data);
    }

}