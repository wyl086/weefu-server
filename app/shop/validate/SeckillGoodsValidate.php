<?php
namespace app\shop\validate;

use app\common\model\bargain\Bargain;
use app\common\model\team\TeamActivity;
use think\Validate;
use app\common\model\seckill\SeckillTime;
use app\common\model\seckill\SeckillGoods;
use app\common\model\goods\Goods;
use app\common\model\goods\GoodsItem;

class SeckillGoodsValidate extends Validate
{
    protected $rule = [
        'start_end'        => 'require',
        'seckill_id'        => 'require|checkSeckill',
        'item'              => 'require|checkActivity'
    ];
    protected $message = [
        'start_end.require'    => '请选择日期',
        'seckill_id.require'    => '请选择秒杀时段',
        'item.require'          => '请选择秒杀商品',
    ];

    public function checkSeckill($value,$rule,$data){
        $seckill = SeckillTime::where(['del'=>0,'id'=>$value])->findOrEmpty();
        if($seckill->isEmpty()){
            return '秒杀时间段已被调整，请重新选择时间段';
        }
        return true;
    }

    public function sceneAdd(){
        $this->append('item','checkAddGoods');

    }
    public function sceneEdit(){
        $this->append('item','checkEditGoods');

    }

    public function checkAddGoods($value,$rule,$data){
        $goods_ids = array_unique(array_column($value,'goods_id'));
        $goods = Goods::where(['del'=>0, 'shop_id'=>$data['shop_id']])->column('id');
        $goods_item = GoodsItem::where(['goods_id'=>$goods_ids])->column('price,spec_value_str','id');
        $seckill_goods = SeckillGoods::where(['seckill_id'=>$data['seckill_id'],'del'=>0])->column('item_id');
        // 参与时期
        $start_end_arr = explode('~', $data['start_end']);
        $start_date = strtotime(trim($start_end_arr[0]));
        $end_date = strtotime(trim($start_end_arr[1]));
        // 同一日期同一时间段内不允许重复添加活动商品
        foreach($value as $item) {
            if(!in_array($item['goods_id'],$goods)){
                return '商品ID:'.$item['goods_id'].'已下架';
            }

            $goods_price = $goods_item[$item['item_id']]['price'] ?? 0;
            //验证商品价格
            if($item['price'] > $goods_price){
                return '商品规格:'.$goods_item[$item['item_id']]['spec_value_str'] .'的秒杀价格高于原价';
            }

            // 获取当前商品参与过哪些日期及哪些时段的秒杀
            $joinDateTime = SeckillGoods::where(['del'=>0, 'goods_id'=>$item['goods_id']])
                ->field('seckill_id,goods_id,start_date,end_date')
                ->group('seckill_id,goods_id,start_date,end_date')
                ->select()
                ->toArray();

            foreach($joinDateTime as $subItem) {
                if($data['seckill_id'] == $subItem['seckill_id'] && $start_date < strtotime($subItem['start_date']) && $end_date < strtotime($subItem['start_date'])) {
                    // 时间段相同，新增日期不在已存在的日期范围，允许添加
                    continue;
                }else if($data['seckill_id'] == $subItem['seckill_id'] && $start_date > strtotime($subItem['end_date']) && $end_date > strtotime($subItem['end_date'])) {
                    // 时间段相同，新增日期不在已存在的日期范围，允许添加
                    continue;
                }else if($data['seckill_id'] != $subItem['seckill_id']) {
                    // 时间段不同，允许新增
                    continue;
                }else{
                    return '商品已在活动中，请勿重新添加';
                }
            }
        }

        return true;
    }

    public function checkEditGoods($value,$rule,$data){
        $goods_ids = array_unique(array_column($value,'goods_id'));
        $seckill_ids = array_column($value,'id');

        $seckill_goods = SeckillGoods::where(['goods_id'=>$goods_ids,'seckill_id'=>$data['seckill_id']])
            ->where('id','not in',$seckill_ids)
            ->find();

        $goods = Goods::where(['del'=>0])->column('id');
        $goods_item = GoodsItem::where(['goods_id'=>$goods_ids])->column('price,spec_value_str','id');

        // 参与时期
        $start_end_arr = explode('~', $data['start_end']);
        $start_date = strtotime(trim($start_end_arr[0]));
        $end_date = strtotime(trim($start_end_arr[1]));
        // 同一日期同一时间段内不允许重复添加活动商品
        foreach ($value as $item){
            if(!in_array($item['goods_id'],$goods)){
                return '商品ID:'.$item['goods_id'].'已下架';
            }

            $goods_price = $goods_item[$item['item_id']]['price'] ?? 0;

            //验证商品价格
            if($item['price'] > $goods_price){
                return '商品规格:'.$goods_item[$item['item_id']]['spec_value_str'] .'的秒杀价格高于原价';
            }

            // 获取当前商品参与过哪些日期及哪些时段的秒杀
            $joinDateTime = SeckillGoods::where([
                ['del', '=', 0],
                ['goods_id', '=', $item['goods_id']],
                ['id', 'not in', $seckill_ids]
            ])
                ->field('seckill_id,goods_id,start_date,end_date')
                ->group('seckill_id,goods_id,start_date,end_date')
                ->select()
                ->toArray();

            foreach($joinDateTime as $subItem) {
                if($data['seckill_id'] == $subItem['seckill_id'] && $start_date < strtotime($subItem['start_date']) && $end_date < strtotime($subItem['start_date'])) {
                    // 时间段相同，新增日期不在已存在的日期范围，允许添加
                    continue;
                }else if($data['seckill_id'] == $subItem['seckill_id'] && $start_date > strtotime($subItem['end_date']) && $end_date > strtotime($subItem['end_date'])) {
                    // 时间段相同，新增日期不在已存在的日期范围，允许添加
                    continue;
                }else if($data['seckill_id'] != $subItem['seckill_id']) {
                    // 时间段不同，允许新增
                    continue;
                }else{
                    return '商品已在活动中，请勿重新添加';
                }
            }

        }
        return true;
    }

    public function checkActivity($item, $rule, $data)
    {
        foreach($item as $v) {
            $team = TeamActivity::where(['del' => 0, 'goods_id' => $v['goods_id']])->select()->toArray();
            if($team) {
                return '商品正在参加拼团活动，不能再参与限时秒杀！';
            }
            $bargain = Bargain::where(['del' => 0, 'goods_id' => $v['goods_id']])->select()->toArray();
            if($bargain) {
                return '商品正在参加砍价活动，不能再参与限时秒杀！';
            }
        }
        return true;
    }
}