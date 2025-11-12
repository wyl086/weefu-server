<?php
namespace app\api\logic;

use app\common\basics\Logic;
use app\common\model\SearchRecord;
use app\common\server\ConfigServer;
use think\facade\Db;

class SearchRecordLogic extends Logic
{
    public static function lists($userId)
    {
        // 热搜关键词
        $hotLists= ConfigServer::get('hot_search', 'hot_keyword', []);

        // 用户历史搜索记录
        if($userId) {
            // 已登录
            $where = [
                'del' => 0,
                'user_id' => $userId
            ];
            $order = [
                'update_time' => 'desc',
                'id' => 'desc'
            ];
            $historyLists = SearchRecord::where($where)
                ->order($order)
                ->limit(10)
                ->column('keyword');
        }else{
            // 未登录
            $historyLists = [];
        }

        return [
            'history_lists' => $historyLists,
            'hot_lists' => $hotLists
        ];
    }

    /**
     * 清空搜索历史
     */
    public static function  clear($userId)
    {
        try {
            $data = [
                'update_time' => time(),
                'del' => 1
            ];
            $result = Db::name('search_record')->where('user_id', $userId)->update($data);
  
            return true;
        } catch(\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }
}