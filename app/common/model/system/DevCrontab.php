<?php
namespace app\common\model\system;

use app\common\basics\Models;

class DevCrontab extends Models
{
    const type = [
        1 => '定时任务',
        2 => '守护进程',
    ];

    public function getTypeDescAttr($value)
    {
        return self::type[$value];
    }

    public function getLastTimeStrAttr($value)
    {
        return date('Y-m-d H:i:s', $value);
    }

    public function getExpressionAttr($value, $data)
    {
        if($data['type'] == 2) {
            return '-';
        }
        return $value;
    }
}