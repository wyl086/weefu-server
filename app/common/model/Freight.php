<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\common\model;

use app\common\basics\Models;
use app\common\enum\FreightEnum;
use think\facade\Db;

/**
 * 运费模板
 */
class Freight extends Models
{
    protected $name = 'freight';

    protected $autoWriteTimestamp = true;

    public static function getChargeWay($type)
    {
        $data = [
            FreightEnum::CHARGE_WAY_WEIGHT => '按重量计费',
            FreightEnum::CHARGE_WAY_VOLUME => '按体积计费',
            FreightEnum::CHARGE_WAY_PIECE => '按件计费',
        ];

        if ($type === true) {
            return $data;
        }

        return $data[$type] ?? '未知';
    }

    public function getChargeWayTextAttr($value, $data)
    {
        return self::getChargeWay($data['charge_way']);
    }

    public static function getNameColumn($shop_id)
    {
        $lists = self::where(['shop_id'=>$shop_id])->column('id,name', 'id');

        return empty($lists) ? [] : $lists;
    }

    public function configs()
    {
        return $this->hasMany('freight_config', 'freight_id', 'id');
    }
    
    /**
     * @notes 根据收货地址-商品信息-运费模板id-商品数量计算运费
     * @param $address
     * @param $freight_id
     * @param $nums mixed 总数量 已计算重量、体积、件数
     * @return float|int|mixed
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\db\exception\DataNotFoundException
     * @author lbzy
     * @datetime 2024-04-03 12:37:48
     */
    public function sumFreight($address, $freight_id, $nums)
    {
        //判断用户收货地址适用模板
        $freight_config         = FreightConfig::where('freight_id', $freight_id)->select()->toArray();
        $freight_config_item    = [];
        foreach ($freight_config as $key => $value) {
            $district_check     = strpos($value['region'], (string)$address['district_id']);
            $city_check         = strpos($value['region'], (string)$address['city_id']);
            $province_check     = strpos($value['region'], (string)$address['province_id']);
            
            if ($district_check !== false) {//区域匹配模板
                $freight_config_item = $value;
            }
            if ($city_check !== false) {//市匹配模板
                $freight_config_item = $value;
            }
            if ($province_check !== false) {//省匹配模板
                $freight_config_item = $value;
            }
            if ($district_check == false && $city_check == false && $province_check == false) {
                if ($value['region'] == 'all') {
                    $freight_config_item = $value;
                }
            }
        }
        
        $price = 0;//运费
        
        $unified_num = $nums;
        
        if ($unified_num <= $freight_config_item['first_unit']) {//小于首件数
            $price = $freight_config_item['first_money'];
        }
        
        if ($unified_num > $freight_config_item['first_unit']) {//大于首件数 计算公式=(((商品数量-首件数)/续件数)*续件费用)+首件费用
            $continue_unit = ceil(($unified_num - $freight_config_item['first_unit']) / $freight_config_item['continue_unit']);//续件数向上取整
            $price = ($continue_unit * $freight_config_item['continue_money']) + $freight_config_item['first_money'];
        }
        
        return $price;
    }
}