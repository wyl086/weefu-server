<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\common\cache;


class CommunityArticleCache extends CacheBase
{

    public function setTag()
    {
        return 'community_article';
    }


    public function setData()
    {
        if (!empty($this->extend['has_new'])) {
            return $this->extend['has_new'];
        }

        return [];
    }

}