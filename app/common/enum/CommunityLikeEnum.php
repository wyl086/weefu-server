<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\common\enum;


/**
 * 种草社区点赞枚举
 * Class CommunityLikeEnum
 * @package app\common\enum
 */
class CommunityLikeEnum
{

    // 点赞类型
    const TYPE_ARTICLE = 1;  // 文章类型
    const TYPE_COMMENT = 2;  // 评论类型


    // 点赞类型
    const LIKE_TYPE = [
        self::TYPE_ARTICLE,
        self::TYPE_COMMENT,
    ];


}