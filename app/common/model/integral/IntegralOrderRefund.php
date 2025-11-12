<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\common\model\integral;

use app\common\basics\Models;


/**
 * 积分订单退款记录模型
 * Class IntegralOrder
 * @package app\common\model\integral
 */
class IntegralOrderRefund extends Models
{
    /**
     * @notes 汇付斗拱参数
     * @param $fieldValue
     * @param $data
     * @return array
     * @author lbzy
     * @datetime 2023-10-23 17:28:25
     */
    function getHfdgParamsAttr($fieldValue, $data)
    {
        return $fieldValue ? ((array) json_decode($fieldValue, true)) : [];
    }
    
    function setHfdgParamsAttr($fieldValue, $data)
    {
        if (is_string($fieldValue)) {
            return $fieldValue;
        }
        return json_encode((array) $fieldValue, JSON_UNESCAPED_UNICODE);
    }

}