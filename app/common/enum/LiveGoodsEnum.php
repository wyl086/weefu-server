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

class LiveGoodsEnum
{
    // 产品来源类型
    const SOURCE_TYPE_GOODS = 0; // 商品库
    const SOURCE_TYPE_SELF = 1;// 自定义


    // 系统审核状态
    const SYS_AUDIT_STATUS_WAIT_PLATFORM = 0;
    const SYS_AUDIT_STATUS_WAIT_WECHAT = 1;
    const SYS_AUDIT_STATUS_SUCCESS = 2;
    const SYS_AUDIT_STATUS_FAIL = 3;


    // 微信审核状态
    const WX_AUDIT_STATUS_WAIT = 0;
    const WX_AUDIT_STATUS_ING = 1;
    const WX_AUDIT_STATUS_SUCCESS = 2;
    const WX_AUDIT_STATUS_FAIL = 3;


    // 商品价格类型 1：一口价 2：价格区间 3：显示折扣价
    const PRICE_ONE = 1;
    const PRICE_RANGE = 2;
    const PRICE_DISCOUNT = 3;


    /**
     * @notes 审核状态描述
     * @param bool $from
     * @return string|string[]
     * @author 段誉
     * @date 2023/2/15 18:49
     */
    public static function getAuditStatusDesc($from = true)
    {
        $desc = [
            self::SYS_AUDIT_STATUS_WAIT_PLATFORM => '待平台审核',
            self::SYS_AUDIT_STATUS_WAIT_WECHAT => '待微信审核',
            self::SYS_AUDIT_STATUS_SUCCESS => '审核通过',
            self::SYS_AUDIT_STATUS_FAIL => '审核未通过',
        ];
        if (true === $from) {
            return $desc;
        }
        return $desc[$from] ?? '未知';
    }


    /**
     * @notes 价格类型
     * @param bool $from
     * @return string|string[]
     * @author 段誉
     * @date 2023/2/16 21:54
     */
    public static function getPriceTypeDesc($from = true)
    {
        $desc = [
            self::PRICE_ONE => '一口价',
            self::PRICE_RANGE => '价格区间',
            self::PRICE_DISCOUNT => '显示价格',
        ];
        if (true === $from) {
            return $desc;
        }
        return $desc[$from] ?? '未知';
    }


    /**
     * @notes 商品来源类型描述
     * @param bool $from
     * @return string|string[]
     * @author 段誉
     * @date 2023/2/17 9:36
     */
    public static function getSourceTypeDesc($from = true)
    {
        $desc = [
            self::SOURCE_TYPE_GOODS => '商品库',
            self::SOURCE_TYPE_SELF => '自定义',
        ];
        if (true === $from) {
            return $desc;
        }
        return $desc[$from] ?? '未知';
    }


    /**
     * @notes 微信审核状态
     * @param bool $from
     * @return string|string[]
     * @author 段誉
     * @date 2023/2/20 10:32
     */
    public static function getWxAuditStatusDesc($from = true)
    {
        $desc = [
            self::WX_AUDIT_STATUS_WAIT => '微信未审核',
            self::WX_AUDIT_STATUS_ING => '微信审核中',
            self::WX_AUDIT_STATUS_SUCCESS => '微信审核通过',
            self::WX_AUDIT_STATUS_FAIL => '微信审核失败',
        ];
        if (true === $from) {
            return $desc;
        }
        return $desc[$from] ?? '';
    }

}