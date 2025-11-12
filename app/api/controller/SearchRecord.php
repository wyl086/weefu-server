<?php
namespace app\api\controller;

use app\common\basics\Api;
use app\api\logic\SearchRecordLogic;
use app\common\server\JsonServer;

class SearchRecord extends Api
{
    public $like_not_need_login = ['lists'];
    /**
     * 用户历史搜索记录
     */
    public function lists()
    {
        $lists = SearchRecordLogic::lists($this->user_id);
        return JsonServer::success('获取成功', $lists);
    }

    /**
     * 清空用户搜索历史
     */
    public function clear()
    {
        if($this->request->isPost()) {
            $result = SearchRecordLogic::clear($this->user_id);
            if($result) {
                return JsonServer::success('清空成功');
            }
            return JsonServer::error(SearchRecordLogic::getError());
        }else{
            return JsonServer::error('请求类型错误');
        }
    }
}