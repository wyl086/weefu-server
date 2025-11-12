<?php
namespace app\common\model\distribution;

use app\common\basics\Models;
use think\model\concern\SoftDelete;

class DistributionGoods extends Models
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
}