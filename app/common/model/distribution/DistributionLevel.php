<?php
namespace app\common\model\distribution;

use app\common\basics\Models;
use think\model\concern\SoftDelete;

class DistributionLevel extends Models
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';

    /**
     * 升级条件允许的字段
     * singleConsumptionAmount 单笔消费金额
     * cumulativeConsumptionAmount 累计消费金额
     * cumulativeConsumptionTimes 累计消费次数
     * returnedCommission 已结算佣金收入
     */
    const UPDATE_CONDITION_FIELDS = ['singleConsumptionAmount', 'cumulativeConsumptionAmount', 'cumulativeConsumptionTimes', 'returnedCommission'];

    /**
     * @notes 获取键对应值的字段名
     * @param $key
     * @return string
     * @author Tab
     * @date 2021/9/1 14:58
     */
    public static function getValueFiled($key)
    {
        switch($key) {
            case 'singleConsumptionAmount':
            case 'cumulativeConsumptionAmount':
            case 'returnedCommission':
                return 'value_decimal';
            case 'cumulativeConsumptionTimes':
                return 'value_int';
            default:
                return 'value_text';
        }
    }

    /**
     * @notes 权重描述获取器
     * @param $value
     * @param $data
     * @return string
     * @author Tab
     * @date 2021/9/1 11:40
     */
    public function getWeightsDescAttr($value, $data)
    {
        return $data['is_default'] ? $value . '级(默认等级)' : $value . '级';
    }

    /**
     * @notes 等级下分销会员数量
     * @param $value
     * @param $data
     * @return int
     * @author Tab
     * @date 2021/9/1 11:41
     */
    public function getMembersNumAttr($value, $data)
    {
        $num = Distribution::where('level_id', $data['id'])->count();
        return $num;
    }

    public static function getLevelName($levelId)
    {
        $level = self::field('name,weights')->findOrEmpty($levelId)->toArray();
        if (empty($level)) {
            return '';
        }
        return $level['name']. '(' . $level['weights'] . ')级';
    }

    public static function getLevelNameTwo($levelId)
    {
        $level = self::field('name,weights')->findOrEmpty($levelId)->toArray();
        if (empty($level)) {
            return '';
        }
        return $level['name'];
    }
}