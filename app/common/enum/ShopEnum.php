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


class ShopEnum
{
    /**
     * 审核状态
     */
    const AUDIT_STATUS_STAY    = 1; //待审核
    const AUDIT_STATUS_OK      = 2; //审核通过
    const AUDIT_STATUS_REFUSE  = 3; //审核拒绝

    /**
     * 商家类型
     */
    const SHOP_TYPE_SELF       = 1; //官方自营
    const SHOP_TYPE_IN         = 2; //入驻商家

    /**
     * 营业状态
     */
    const SHOP_RUN_CLOSE      = 0; //暂停营业
    const SHOP_RUN_OPEN       = 1; //营业中

    /**
     * 冻结状态
     */
    const SHOP_FREEZE_NORMAL   = 0; //正常
    const SHOP_FREEZE_BAN      = 1; //冻结

    /**
     * 产品审核
     */
    const PRODUCT_AUDIT_FALSE  = 0; //关闭
    const PRODUCT_AUDIT_TRUE   = 1; //开启

    /**
     * 店铺推荐
     */
    const SHOP_RECOMMEND_FALSE = 0; //不推荐
    const SHOP_RECOMMEND_TRUE  = 1; //推荐

    /**
     * 商家默认logo、背景图、PC端店铺封面、PC端店铺头图
     */
    const DEFAULT_LOGO       = '/static/common/image/default/shop_default_logo.png';
    const DEFAULT_BG         = '/static/common/image/default/shop_default_bg.jpg';
    const DEFAULT_COVER      = '/static/common/image/default/shop_default_cover.png';
    const DEFAULT_BANNER     = '/static/common/image/default/shop_default_banner.jpg';

    /**
     * 背景图、PC端店铺封面、PC端店铺头图的示例图片
     */
    const DOME_BG         = '/static/common/image/default/shop_demo_background.png';
    const DOME_COVER      = '/static/common/image/default/shop_demo_cover.png';
    const DOME_BANNER     = '/static/common/image/default/shop_demo_banner.png';


    /**
     * 发票开关
     * INVOICE_CLOSE-关闭
     * INVOICE_OPEN- 开启
     */
    const INVOICE_CLOSE = 0;
    const INVOICE_OPEN = 1;

    /**
     * 发票是否支持专票
     * SPEC_INVOICE_NOT - 不支持
     * SPEC_INVOICE_SUPPORT - 不支持
     */
    const SPEC_INVOICE_UNABLE = 0;
    const SPEC_INVOICE_ABLE = 1;


    /**
     * 商家支持的配送方式
     * DELIVERY_EXPRESS = 快递发货
     * DELIVERY_VIRTUAL = 线下自提
     */
    const DELIVERY_EXPRESS = 1;
    const DELIVERY_SELF = 2;


    /**
     * NOTE: 审核状态
     * @author: 张无忌
     * @param bool $form
     * @return array|mixed|string
     */
    public static function getAuditStatusDesc($form = true){
        $desc = [
            self::AUDIT_STATUS_STAY    => '待审核',
            self::AUDIT_STATUS_OK      => '审核通过',
            self::AUDIT_STATUS_REFUSE  => '审核拒绝'
        ];
        if(true === $form){
            return $desc;
        }
        return $desc[$form] ?? '';
    }

    /**
     * NOTE: 商家类型
     * @author: 张无忌
     * @param bool $form
     * @return array|mixed|string
     *
     */
    public static function getShopTypeDesc($form = true){
        $desc = [
            self::SHOP_TYPE_SELF    => '官方自营',
            self::SHOP_TYPE_IN      => '入驻商家',
        ];
        if(true === $form){
            return $desc;
        }
        return $desc[$form] ?? '';
    }

    /**
     * NOTE: 营业状态
     * @author: 张无忌
     * @param bool $form
     * @return array|mixed|string
     */
    public static function getShopIsRunDesc($form = true)
    {
        $desc = [
            self::SHOP_RUN_CLOSE    => '暂停营业',
            self::SHOP_RUN_OPEN     => '营业中',
        ];
        if(true === $form){
            return $desc;
        }
        return $desc[$form] ?? '';
    }

    /**
     * NOTE: 商家冻结状态
     * @author: 张无忌
     * @param bool $form
     * @return array|mixed|string
     */
    public static function getShopFreezeDesc($form = true) {
        $desc = [
            self::SHOP_FREEZE_NORMAL  => '正常',
            self::SHOP_FREEZE_BAN     => '冻结',
        ];
        if(true === $form){
            return $desc;
        }
        return $desc[$form] ?? '';
    }

    /**
     * NOTE: 商家推荐状态
     * @author: 张无忌
     * @param bool $form
     * @return array|mixed|string
     */
    public static function getShopIsRecommendDesc($form = true) {
        $desc = [
            self::SHOP_RECOMMEND_TRUE  => '推荐',
            self::SHOP_RECOMMEND_FALSE => '不推荐',
        ];
        if(true === $form){
            return $desc;
        }
        return $desc[$form] ?? '';
    }


    /**
     * @notes 支持的配送方式
     * @param bool $form
     * @return string|string[]
     * @author 段誉
     * @date 2022/11/1 11:19
     */
    public static function getDeliveryTypeDesc($form = true)
    {
        $desc = [
            self::DELIVERY_EXPRESS  => '快递发货',
            self::DELIVERY_SELF => '线下自提',
        ];
        if(true === $form){
            return $desc;
        }
        return $desc[$form] ?? '';
    }


}