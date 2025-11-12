<?php


namespace app\api\controller;


use app\api\logic\CommunitySearchRecordLogic;
use app\common\basics\Api;
use app\common\server\JsonServer;


/**
 * 种草社区搜索记录
 * Class CommunitySearchRecord
 * @package app\api\controller
 */
class CommunitySearch extends Api
{

    public $like_not_need_login = ['lists'];


    /**
     * @notes 历史记录
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/5/9 16:59
     */
    public function lists()
    {
        $lists = CommunitySearchRecordLogic::lists($this->user_id);
        return JsonServer::success('获取成功', $lists);
    }


    /**
     * @notes 清空历史搜索
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/5/9 16:59
     */
    public function clear()
    {
        CommunitySearchRecordLogic::clear($this->user_id);
        return JsonServer::success('清空成功');
    }


}