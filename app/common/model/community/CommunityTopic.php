<?php


namespace app\common\model\community;


use app\common\basics\Models;

/**
 * 种草社区话题
 * Class CommunityCategory
 * @package app\common\model\content
 */
class CommunityTopic extends Models
{


    /**
     * @notes 关联分类
     * @return \think\model\relation\HasOne
     * @author 段誉
     * @date 2022/4/28 14:18
     */
    public function cate()
    {
        return $this->hasOne(CommunityCategory::class, 'id', 'cid');
    }


    /**
     * @notes 扣减文章数量
     * @param $id
     * @return mixed
     * @author 段誉
     * @date 2022/5/10 16:43
     */
    public static function decArticleNum($id)
    {
        return self::where([
            ['id', '=', $id],
            ['article_num', '>=', 1]
        ])->dec('article_num')->update();
    }

}