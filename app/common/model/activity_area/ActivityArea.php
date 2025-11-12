<?php

namespace app\common\model\activity_area;

use app\common\basics\Models;
use app\common\enum\ActivityAreaEnum;

/**
 * Class ActivityArea
 * @package app\common\model\activity_area
 */
class ActivityArea extends Models
{
    /**
     * @notes 支付方式
     * @param bool $status
     * @return string|string[]
     * @author suny
     * @date 2021/7/13 6:37 下午
     */
    public static function getStatus($status = true)
    {

        $desc = [
            ActivityAreaEnum::STATUS_OPEN => '开启',
            ActivityAreaEnum::STATUS_CLOSE => '隐藏',
        ];
        if ($status === true) {
            return $desc;
        }
        return $desc[$status] ?? '未知';
    }

    /**
     * @notes 支付状态
     * @param $value
     * @param $data
     * @return string|string[]
     * @author suny
     * @date 2021/7/13 6:37 下午
     */
    public function getStatusAttr($value, $data)
    {

        return self::getStatus($data['status']);
    }
}