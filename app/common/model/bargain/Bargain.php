<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\common\model\bargain;


use app\common\basics\Models;
use app\common\model\goods\Goods;
use app\common\model\shop\Shop;

/**
 * 砍价活动模型
 * Class bargain
 * @package app\common\model
 */
class Bargain extends Models
{
    /**
     * @notes 关联商品模型
     * @return \think\model\relation\HasOne
     * @author suny
     * @date 2021/7/13 6:39 下午
     */
    public function goods()
    {

        return $this->hasOne(Goods::class, 'id', 'goods_id')
            ->field(['id', 'name', 'image', 'max_price', 'min_price']);
    }

    /**
     * @notes 关联商品模型
     * @return \think\model\relation\HasOne
     * @author suny
     * @date 2021/7/13 6:39 下午
     */
    public function shop()
    {

        return $this->hasOne(Shop::class, 'id', 'shop_id')
            ->field(['id', 'name', 'logo']);
    }

    /**
     * @notes 关联砍价参与人数
     * @return \think\model\relation\HasMany
     * @author suny
     * @date 2021/7/13 6:40 下午
     */
    public function launchPeopleNumber()
    {

        return $this->hasMany('BargainLaunch', 'bargain_id', 'id');
    }

    /**
     * @notes 帮砍人数
     * @return \think\model\relation\HasMany
     * @author suny
     * @date 2021/7/13 6:40 下午
     */
    public function KnifePeopleNumber()
    {

        return $this->hasMany('BargainKnife', 'bargain_id', 'id');
    }

    /**
     * @notes 关联砍价成功人数
     * @return \think\model\relation\HasMany
     * @author suny
     * @date 2021/7/13 6:40 下午
     */
    public function successKnifePeopleNumber()
    {

        return $this->hasMany('BargainLaunch', 'bargain_id', 'id')
            ->where(['status' => 1]);
    }

    /**
     * @notes 活动状态
     * @param $value
     * @param $data
     * @return string|string[]
     * @author suny
     * @date 2021/7/13 6:40 下午
     */
    public static function getStatusTextAttr($value, $data)
    {

        $array = [
            1 => '已开启',
            0 => '已停止',
        ];
        if ($data['status'] === true) {
            return $array;
        }
        return $array[$data['status']] ?? '';
    }
}