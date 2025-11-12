<?php
namespace app\common\model\distribution;

use app\common\basics\Models;
use think\model\concern\SoftDelete;

class Distribution extends Models
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';

    /**
     * @notes 成为分销会员时间
     * @param $value
     * @return false|string
     * @author Tab
     * @date 2021/9/2 18:59
     */
    public function getDistributionTimeAttr($value)
    {
        return empty($value) ? '' : date('Y-m-d H:i:s', $value);
    }
}