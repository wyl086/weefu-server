<?php
namespace app\common\model\order;

use app\common\basics\Models;

class OrderTrade extends Models
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
