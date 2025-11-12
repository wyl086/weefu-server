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
 * 种草社区文章枚举
 * Class CommunityArticleEnum
 * @package app\common\enum
 */
class CommunityArticleEnum
{
    const STATUS_WAIT = 0;  //待审核
    const STATUS_SUCCESS = 1;  //审核通过
    const STATUS_REFUSE = 2;  //审核拒绝


    /**
     * @notes 获取审核状态描述
     * @param bool $status
     * @return string|string[]
     * @author 段誉
     * @date 2022/5/6 10:41
     */
    public static function getStatusDesc($status = true)
    {
        $desc = [
            self::STATUS_WAIT => '审核中',
            self::STATUS_SUCCESS => '审核通过',
            self::STATUS_REFUSE => '审核拒绝',
        ];
        if (true === $status) {
            return $desc;
        }
        return $desc[$status];
    }


    /**
     * @notes
     * @param $article
     * @param bool $page_detail 是否为详情页描述 true=详情页描述 false=作品列表页描述
     * @return mixed|string
     * @author 段誉
     * @date 2022/5/12 16:11
     */
    public static function getStatusRemarkDesc($article, $page_detail = true)
    {
        $desc = '';
        if ($article['status'] == self::STATUS_WAIT) {
            $desc = $page_detail ? '审核通过后,将展示在首页!' : '通过后将展示在社区';
        }

        if ($article['status'] == self::STATUS_REFUSE) {
            $desc = $page_detail ? $article['audit_remark'] : '查看未通过原因';
        }

        return $desc;
    }

}