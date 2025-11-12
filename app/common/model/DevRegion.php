<?php
namespace app\common\model;

use app\common\basics\Models;

class DevRegion extends Models
{
    static function getAreaName($id)
    {
        return static::where('id', $id)->value('name', '');
    }
}
