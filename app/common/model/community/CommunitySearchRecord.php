<?php


namespace app\common\model\community;


use app\common\basics\Models;


/**
 * 种草社区搜索记录
 * Class CommunitySearchRecord
 * @package app\common\model\community
 */
class CommunitySearchRecord extends Models
{

    /**
     * @notes 增加搜索次数
     * @param $id
     * @return mixed
     * @author 段誉
     * @date 2022/5/9 16:18
     */
    public static function incCount($id)
    {
        return self::where(['id' => $id])->inc('count')->update();
    }


}