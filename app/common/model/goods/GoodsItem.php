<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\common\model\goods;


use app\admin\validate\SeckillTime;
use app\api\logic\SeckillLogic;
use app\common\basics\Models;
use app\common\enum\OrderEnum;
use app\api\logic\OrderLogic;
use app\common\model\seckill\SeckillGoods;
use app\common\model\seckill\SeckillTime as SeckillTimeModel;
use app\common\server\UrlServer;


/**
 * 商品规格
 * Class GoodsItem
 * @package app\common\model\goods
 */
class GoodsItem extends Models
{
    /**
     * @notes notes
     * @param $value
     * @param $data
     * @return string
     * @author lbzy
     * @datetime 2024-04-03 09:53:03
     */
    function getMarketPriceAttr($value, $data)
    {
        return $value <= 0 ? '' : $value;
    }
    
    function getChengbenPriceAttr($value, $data)
    {
        return $value <= 0 ? '' : $value;
    }
    
    function getWeightAttr($value, $data)
    {
        return $value <= 0 ? '' : $value;
    }
    
    function getVolumeAttr($value, $data)
    {
        return $value <= 0 ? '' : $value;
    }
    
    /**
     * @notes 获取订单商品价格
     * @param $item
     * @return int|mixed
     * @author lbzy
     * @datetime 2023-07-27 15:19:11
     */
    static function getGoodsItemPrice($item)
    {
        $seckill_goods_price = self::isSeckill($item['id']);
        
        return $seckill_goods_price != 0 ? $seckill_goods_price : $item['price'];
    }
    
    /**
     * 根据goods_id,num和item_id计算价格
     */
    public function sumGoodsPrice($goods_id, $item_id, $num,$discount)
    {
        $goods_price = $this
            ->where([
                ['goods_id', '=', $goods_id],
                ['id', '=', $item_id],
            ])
            ->value('price');
        $seckill_goods_price = self::isSeckill($item_id);
        if($seckill_goods_price != 0){
            $goods_price = $seckill_goods_price;
            OrderLogic::$order_type = OrderEnum::SECKILL_ORDER;
        }
        $is_member = Goods::where('id',$goods_id)->value('is_member');

        if ($is_member === 0 || empty($is_member)){//不参与会员价
            $price = round($goods_price*$num,2);
        }
        if ($is_member == 1){
            $price = max(round($goods_price*$discount/10,2), 0.01) * $num;
        }
        return $price;
    }

    public function sumMemberPrice($goods_id, $item_id, $num,$discount)
    {
        $goods_price = $this
            ->where([
                ['goods_id', '=', $goods_id],
                ['id', '=', $item_id],
            ])
            ->value('price');
        $seckill_goods_price = self::isSeckill($item_id);
        if($seckill_goods_price != 0){
            $goods_price = $seckill_goods_price;
            OrderLogic::$order_type = OrderEnum::SECKILL_ORDER;
        }
        $is_member = Goods::where('id',$goods_id)->value('is_member');

        if ($is_member === 0 || empty($is_member)){//不参与会员价
            $price = 0;
        }
        if ($is_member == 1){
            $price = ($goods_price - max(round($goods_price*$discount/10,2), 0.01)) * $num;
        }
        return $price;
    }
    /***
     *
     *是否为秒杀商品
     *
    ***/
    public static function isSeckill($item_id){

        //当前时段秒杀商品
        $seckill = SeckillLogic::getSeckillGoods();
        $seckill_goods = $seckill['seckill_goods'];

        //当前商品规格是否为秒杀商品
        if (isset($seckill_goods[$item_id])) {
            return $seckill_goods[$item_id]['price'];
        }else{
            return 0;
        }
    }
}