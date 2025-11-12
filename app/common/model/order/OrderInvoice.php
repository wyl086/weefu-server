<?php

namespace app\common\model\order;

use app\common\basics\Models;
use app\common\enum\OrderInvoiceEnum;


/**
 * 发票模型
 * Class OrderInvoice
 * @package app\common\model\order
 */
class OrderInvoice extends Models
{

    /**
     * @notes 关联订单
     * @return \think\model\relation\HasOne
     * @author 段誉
     * @date 2022/4/12 17:58
     */
    public function orderData()
    {
        return $this->hasOne(Order::class, 'id', 'order_id');
    }

    /**
     * @notes 类型描述
     * @param $value
     * @param $data
     * @return bool|mixed|string
     * @author 段誉
     * @date 2022/4/12 9:15
     */
    public function getTypeTextAttr($value, $data)
    {
        return OrderInvoiceEnum::getTypeDesc($data['type']);
    }

    /**
     * @notes 状态属性
     * @param $value
     * @param $data
     * @return bool|mixed|string
     * @author 段誉
     * @date 2022/4/11 19:01
     */
    public function getStatusTextAttr($value, $data)
    {
        return OrderInvoiceEnum::getStatusDesc($data['status']);
    }


    /**
     * @notes 抬头类型描述属性
     * @param $value
     * @param $data
     * @return bool|mixed|string
     * @author 段誉
     * @date 2022/4/11 19:01
     */
    public function getHeaderTypeTextAttr($value, $data)
    {
        return OrderInvoiceEnum::getHeaderTypeTextDesc($data['header_type']);
    }

}
