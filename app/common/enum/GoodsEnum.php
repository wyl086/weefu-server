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


class GoodsEnum
{
//    商品类型
    const TYPE_ACTUAL = 0; // 实物商品
    const TYPE_VIRTUAL = 1; // 虚拟商品

    /**
     * 运费类型
     */
    const EXPRESS_TYPE_FREE         = 1; //包邮
    const EXPRESS_TYPE_UNIFIED      = 2; //统一运费
    const EXPRESS_TYPE_TEMPLATE     = 3; //运费模板


    /**
     * 销售状态
     */
    const STATUS_SOLD_OUT   = 0;  //仓库中
    const STATUS_SHELVES    = 1;  //上架中

    /**
     * 删除状态
     */
    const DEL_NORMAL = 0; // 正常
    const DEL_TRUE = 1; // 已删除
    const DEL_RECYCLE = 2; // 回收站


    /**
     * 审核状态
     */
    const AUDIT_STATUS_STAY    = 0; //待审核
    const AUDIT_STATUS_OK      = 1; //审核通过
    const AUDIT_STATUS_REFUSE  = 2; //审核失败

    /**
     * 商品类型
     */
    const SALES = 1; // 销售中
    const WAREHOUSE = 2; // 仓库中
    const WARNING = 3; // 库存预警
    const RECYCLE_BIN = 4; // 回收站
    const WAIT_AUDIT = 5; // 待审核
    const UNPASS_AUDIT = 6; // 未通过审核

    /**
     * 配送方式
     */
    const DELIVERY_EXPRESS = 1;// 快递发货
    const DELIVERY_VIRTUAL = 2;// 虚拟发货
    const DELIVERY_SELF = 3;// 线下自提


    // 买家付款后
    const AFTER_PAY_AUTO_DELIVERY = 1;// 自动发货
    const AFTER_PAY_SELF_DELIVERY = 2;// 手动发货

    // 卖家发货后
    const AFTER_DELIVERY_AUTO_COMFIRM = 1;// 自动完成订单
    const AFTER_DELIVERY_SELF_COMFIRM = 2;// 买家确认订单


    /**
     * @notes 商品类型
     * @param bool $type
     * @return string|string[]
     * @author 段誉
     * @date 2022/4/20 18:38
     */
    public static function getTypeDesc($type = true)
    {
        $desc = [
            self::TYPE_ACTUAL => '实物商品',
            self::TYPE_VIRTUAL => '虚拟商品',
        ];
        if ($type === true) {
            return $desc;
        }
        return $desc[$type] ?? '';
    }


    /**
     * @notes 配送方式
     * @param bool $type
     * @return string|string[]
     * @author ljj
     * @date 2023/6/28 5:07 下午
     */
    public static function getDeliveryTypeDesc($type = true)
    {
        $desc = [
            self::DELIVERY_EXPRESS => '快递发货',
            self::DELIVERY_VIRTUAL => '虚拟发货',
            self::DELIVERY_SELF => '线下自提',
        ];
        if ($type === true) {
            return $desc;
        }
        return $desc[$type] ?? '';
    }
    
    
    
    static function getDeliveryLists($delivery_types) : array
    {
        $result = [];
        
        $delivery_types = array_unique($delivery_types);
        $lists = self::getDeliveryTypeDesc();
        
        foreach ($delivery_types as $delivery_type) {
            if (isset($lists[$delivery_type])) {
                $result[] = [
                    'delivery_type'         => $delivery_type,
                    'delivery_type_text'    => $lists[$delivery_type],
                ];
            }
        }
        
        return $result;
    }
}