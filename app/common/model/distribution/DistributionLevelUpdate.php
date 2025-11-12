<?php
namespace app\common\model\distribution;

use app\common\basics\Models;
use think\model\concern\SoftDelete;

class DistributionLevelUpdate extends Models
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
}