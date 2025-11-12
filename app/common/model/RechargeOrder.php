<?php
namespace app\common\model;

use app\common\basics\Models;
use app\common\enum\RechargeOrderEnum;

class RechargeOrder extends Models
{
    //支付方式
    public static function getPayWay($status = true)
    {
        $desc = [
            RechargeOrderEnum::WECHAT_PAY => '微信支付',
            RechargeOrderEnum::ALI_PAY => '支付宝支付',
        ];
        if ($status === true) {
            return $desc;
        }
        return $desc[$status] ?? '未知';
    }

    //支付状态
    public static function getPayStatus($status = true)
    {
        $desc = [
            RechargeOrderEnum::PAY_STATUS_NO_PAID => '待支付',
            RechargeOrderEnum::PAY_STATUS_PAID => '已支付',
        ];
        if ($status === true) {
            return $desc;
        }
        return $desc[$status] ?? '未知';
    }

    //支付状态
    public function getPayStatusAttr($value, $data)
    {
        return self::getPayStatus($data['pay_status']);
    }

    //支付方式
    public function getPayWayAttr($value, $data)
    {
        return self::getPayWay($data['pay_way']);
    }
    
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