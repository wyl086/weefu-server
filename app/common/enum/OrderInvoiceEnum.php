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


class OrderInvoiceEnum
{
    /**
     * 发票类型
     * TYPE_NORMAL 普通
     * TYPE_SPEC 专用
     */
    const TYPE_NORMAL = 0;
    const TYPE_SPEC = 1;

    /**
     * 抬头类型
     * HEADER_TYPE_PERSONAL 个人
     * HEADER_TYPE_COMPANY 企业
     */
    const HEADER_TYPE_PERSONAL = 0;
    const HEADER_TYPE_COMPANY = 1;

    /**
     * 开票状态
     * STATUS_NO 未开票
     * STATUS_NO 已开票
     */
    const STATUS_NO = 0;
    const STATUS_YES = 1;

    /**
     * @notes 获取状态描述
     * @param bool $status
     * @return bool|mixed|string
     * @author 段誉
     * @date 2022/4/11 18:55
     */
    public static function getStatusDesc($status = true)
    {
        $desc = [
            self::STATUS_NO => '未开票',
            self::STATUS_YES => '已开票',
        ];

        if ($status === true) {
            return $status;
        }

        return isset($desc[$status]) ? $desc[$status] : $status;
    }


    /**
     * @notes 获取类型描述
     * @param bool $type
     * @return bool|mixed|string
     * @author 段誉
     * @date 2022/4/12 9:14
     */
    public static function getTypeDesc($type = true)
    {
        $desc = [
            self::TYPE_NORMAL => '电子普通发票',
            self::TYPE_SPEC => '专用发票',
        ];

        if ($type === true) {
            return $type;
        }

        return isset($desc[$type]) ? $desc[$type] : $type;
    }



    /**
     * @notes 获取抬头类型描述
     * @param bool $type
     * @return bool|mixed|string
     * @author 段誉
     * @date 2022/4/12 9:14
     */
    public static function getHeaderTypeTextDesc($type = true)
    {
        $desc = [
            self::HEADER_TYPE_PERSONAL => '个人',
            self::HEADER_TYPE_COMPANY => '企业',
        ];

        if ($type === true) {
            return $type;
        }

        return isset($desc[$type]) ? $desc[$type] : $type;
    }

}