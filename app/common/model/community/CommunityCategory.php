<?php


namespace app\common\model\community;


use app\common\basics\Models;

/**
 * 种草社区分类
 * Class CommunityCategory
 * @package app\common\model\content
 */
class CommunityCategory extends Models
{


    /**
     * @notes 关联话题
     * @return \think\model\relation\HasMany
     * @author 段誉
     * @date 2022/4/29 16:33
     */
    public function topic()
    {
        return $this->hasMany(CommunityTopic::class, 'cid', 'id');
    }

}