<?php


namespace app\api\validate;


use app\common\basics\Validate;
use app\common\model\goods\Goods;
use app\common\model\shop\Shop;

class TeamValidate extends Validate
{
    protected $rule = [
        'team_id'  => 'require|number',
        'goods_id' => 'require|number|checkShop',
        'item_id'  => 'require|number',
        'count'    => 'require|number',
    ];

    protected $message = [
        'team_id.require'  => '缺少team_id',
        'team_id.number'   => 'team_id需为数字',
        'goods_id.require' => '缺少goods_id',
        'goods_id.number'  => 'goods_id需为数字',
        'item_id.require'  => '缺少item_id',
        'item_id.number'   => 'item_id需为数字',
        'count.require'    => '缺少count',
        'count.number'     => 'count需为数字',
    ];

    protected $scene = [
        'check' => ['goods_id', 'item_id', 'count']
    ];

    /**
     * @notes 检查商品所属店铺的营业状态
     * @param $goods_id
     * @return bool|string
     * @author Tab
     * @date 2021/7/19 14:08
     */
    public function checkShop($goods_id)
    {
        $shop_id = Goods::where('id', $goods_id)->value('shop_id');
        $shop = Shop::field('expire_time,is_run,is_freeze')->where(['del' => 0, 'id' => $shop_id])->findOrEmpty();
        if($shop->isEmpty()) {
            return '该商品所属店铺不存在';
        }
        // 获取原始数据(不经获取器)
        $shop = $shop->getData();
        if(!empty($shop['expire_time']) && ($shop['expire_time'] <= time())) {
            return '该商品所属店铺已到期';
        }
        if($shop['is_freeze']) {
            return '该商品所属店铺已被冻结';
        }
        if(!$shop['is_run']) {
            return '该商品所属店铺暂停营业中';
        }
        return true;
    }
}