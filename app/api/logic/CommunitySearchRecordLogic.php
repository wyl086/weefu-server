<?php


namespace app\api\logic;


use app\common\basics\Logic;
use app\common\model\community\CommunitySearchRecord;
use app\common\model\community\CommunityTopic;


/**
 * 种草社区搜索记录
 * Class CommunitySearchRecordLogic
 * @package app\api\logic
 */
class CommunitySearchRecordLogic extends Logic
{

    /**
     * @notes 搜索列表
     * @param $userId
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/5/9 16:57
     */
    public static function lists($userId)
    {
        // 推荐话题
        $topic = CommunityTopic::field(['id', 'name'])
            ->where(['is_show' => 1, 'is_recommend' => 1, 'del' => 0])
            ->order(['sort' => 'desc', 'id' => 'desc'])
            ->limit(10)
            ->select()->toArray();

        // 用户历史搜索记录
        $history = [];
        if ($userId) {
            $where = [
                'del' => 0,
                'user_id' => $userId
            ];
            $sort = [
                'update_time' => 'desc',
                'id' => 'desc'
            ];
            $history = CommunitySearchRecord::where($where)
                ->order($sort)
                ->limit(10)
                ->column('keyword');
        }

        return [
            'history' => $history,
            'topic' => $topic
        ];
    }


    /**
     * @notes 清空搜索记录
     * @param $userId
     * @author 段誉
     * @date 2022/5/9 16:58
     */
    public static function clear($userId)
    {
        CommunitySearchRecord::where('user_id', $userId)->update([
            'del' => 1,
            'update_time' => time()
        ]);
    }



    /**
     * @notes 搜索记录
     * @param $keyword
     * @param $user_id
     * @return CommunitySearchRecord|mixed|\think\Model
     * @author 段誉
     * @date 2022/5/9 16:20
     */
    public static function recordKeyword($keyword, $user_id)
    {
        $record = CommunitySearchRecord::where([
            'user_id' => $user_id,
            'keyword' => $keyword,
            'del' => 0
        ])->findOrEmpty();

        if ($record->isEmpty()) {
            return CommunitySearchRecord::create([
                'user_id' => $user_id,
                'keyword' => $keyword,
            ]);
        }
        return CommunitySearchRecord::incCount($record['id']);
    }
}