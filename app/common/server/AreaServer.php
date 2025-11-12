<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\common\server;

use app\common\model\dev\DevRegion;

class AreaServer
{
    /**
     * 通过id获取地址
     * @param $val (为非数组，返回单独地点名，为数组时，按顺序拼接地址返回)
     * @param string $address val为数组时，连接详细地址一起返回
     * @return mixed|string
     */
    public static function getAddress($val, $address = '')
    {
        $region = cache('region');
        if(!$region) { // 无缓存
            $region = DevRegion::column('id,name', 'id');
            cache('region', $region, 3600);
        }
        // 有缓存
        if(is_array($val)) { // 数组
            $temp = '';
            foreach($val as $v) {
                $temp .= $region[$v] ? $region[$v]['name'] : '';
            }
            return $temp.$address;
        }
        // 非数组
        return $region[$val] ? $region[$val]['name'] : '';
    }

    /**
     * 通过id获取地址经纬度上
     * @param $id
     * @return array|\PDOStatement|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getDb09LngAndLat($id)
    {
        return Db::name('dev_region')
            ->where('id', '=', $id)
            ->field(['db09_lng', 'db09_lat'])
            ->find();
    }
}