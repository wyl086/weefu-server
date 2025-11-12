<?php
namespace app\shop\logic\seckill;

use app\common\basics\Logic;
use app\common\model\seckill\SeckillGoods;
use app\common\model\seckill\SeckillTime;
use app\common\model\goods\Goods;
use app\common\server\UrlServer;
use think\facade\Db;

class SeckillGoodsLogic extends Logic
{
    /**
     * 统计
     */
    public static function statistics($shop_id)
    {
        // 秒杀中商品
        $where = [
            ['sg.del', '=', 0],
            ['sg.review_status', '=', 1],
            ['shop_id', '=', $shop_id],
        ];
        $lists = SeckillGoods::alias('sg')
            ->leftJoin('seckill_time st', 'st.id=sg.seckill_id')
            ->field('sg.goods_id,sg.start_date,sg.end_date,st.start_time,st.end_time')
            ->where($where)
            ->group('sg.goods_id,sg.start_date,sg.end_date,st.start_time,st.end_time')
            ->order(['sg.id' => 'desc'])
            ->select()
            ->toArray();
        $now = time();
        $now_date = date('Y-m-d', $now);
        $unSeckillCount = 0;
        foreach($lists as $key => $item) { // 检验是否在秒杀中
            $start_date_time = strtotime($item['start_date']. ' 00:00:00');
            $end_date_time = strtotime($item['end_date']. ' 23:59:59');
            // 日期校验
            if($now >= $start_date_time && $now <= $end_date_time) {
                $start_time = strtotime($now_date. ' '. $item['start_time']);
                $end_time = strtotime($now_date. ' '. $item['end_time']);
                if($now >= $start_time && $now <= $end_time) {
                    // 秒杀中的时段，无需处理
                }else{
                    unset($lists[$key]); // 未在秒杀时段
                    ++$unSeckillCount ;
                }
            }else{
                unset($lists[$key]); // 未在秒杀时间日期
                ++$unSeckillCount;
            }
        }
        $seckillCount = count($lists);

        // 待审核
        $waitReview = SeckillGoods::where(['del'=>0, 'review_status'=>0, 'shop_id'=>$shop_id])->group('seckill_id,goods_id,start_date,end_date')->count();
        // 审核拒绝
        $refuseReview = SeckillGoods::where(['del'=>0, 'review_status'=>2, 'shop_id'=>$shop_id])->group('seckill_id,goods_id,start_date,end_date')->count();
//
        return [
            'unSeckillCount'  => $unSeckillCount,
            'seckillCount'  => $seckillCount,
            'waitReview' => $waitReview,
            'refuseReview' => $refuseReview,
        ];
    }

    public static function getTimeAll(){
        $time_list =  SeckillTime::where(['del'=>0])->order('start_time asc')->select()->toArray();
        foreach ($time_list as &$item){
            $item['time'] = $item['start_time'].' ~ '.$item['end_time'];
        }
        return $time_list;
    }

    public static function addGoods($post){
        try{
            $now = time();
            $add_data = [];

            // 开始日期结束日期处理
            $start_end = explode('~', $post['start_end']);

            foreach ($post['item'] as  $item){
                $add_data[] =[
                    'seckill_id'        => $post['seckill_id'],
                    'goods_id'          => $item['goods_id'],
                    'item_id'           => $item['item_id'],
                    'price'             => $item['price'],
                    'create_time'       => $now,
                    'sales_sum'         => 0,
                    'update_time'       => $now,
                    'del'               => 0,
                    'shop_id'           => $post['shop_id'],
                    'start_date'        => trim($start_end[0]),
                    'end_date'          => trim($start_end[1]),
                ];
            }
            $seckillGoods = new SeckillGoods();
            $seckillGoods->saveAll($add_data);
            return true;
        }catch(\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    public static function goodsList($get)
    {
        $where = [
            ['sg.del', '=', 0],
            ['sg.shop_id', '=', $get['shop_id']]
        ];

        // 商品名称
        if(isset($get['name']) && !($get['name'] == '')) {
            $where[] = ['g.name', 'like', '%'.trim($get['name']).'%'];
        }
        // 参与日期
        if(isset($get['start_end']) && !empty($get['start_end'])) {
            $start_end = explode('~', $get['start_end']);
            $where[] = ['sg.start_date', '>=', trim($start_end[0])];
            $where[] = ['sg.end_date', '<=', trim($start_end[1])];
        }
        // 参与时段
        if(isset($get['seckill_id']) && !empty($get['seckill_id'])) {
            $where[] = ['sg.seckill_id', '=', $get['seckill_id']];
        }

        $lists = SeckillGoods::alias('sg')
            ->leftJoin('seckill_time st', 'st.id=sg.seckill_id')
            ->leftJoin('goods g', 'sg.goods_id=g.id')
            ->field('sg.seckill_id,sg.goods_id,review_status,review_desc,start_date,end_date,start_time,end_time,g.name,g.image,g.min_price,g.max_price')
            ->where($where)
            ->group('sg.seckill_id,sg.goods_id,review_status,review_desc,start_date,end_date,start_time,end_time,g.name,g.image,g.min_price,g.max_price')
            ->select()
            ->toArray();
        // 按类型提取数据
        $unSeckill = [];
        $seckill = [];
        $waitReview = [];
        $refuseReview = [];
        $now = time();
        $now_date = date('Y-m-d', $now);
        foreach($lists as $key => $item) {
            if($item['review_status'] == 0) { // 待审核
                $waitReview[] = $item;
                continue;
            }else if($item['review_status'] == 2){ // 审核拒绝
                $refuseReview[] = $item;
                continue;
            }else if($item['review_status'] == 1) { // 审核通过
                $start_date_time = strtotime($item['start_date']. ' 00:00:00');
                $end_date_time = strtotime($item['end_date']. ' 23:59:59');
                // 日期校验
                if($now >= $start_date_time && $now <= $end_date_time) {
                    $start_time = strtotime($now_date. ' '. $item['start_time']);
                    $end_time = strtotime($now_date. ' '. $item['end_time']);
                    if($now >= $start_time && $now <= $end_time) {
                        $seckill[] = $item;
                        continue;
                    }else{
                        // 未在秒杀时段
                        $unSeckill[] = $item;
                        continue;
                    }
                }else{
                    // 未在秒杀时间日期
                    $unSeckill[] = $item;
                    continue;
                }
            }
        }

        switch($get['type']) {
            case 'seckill':
                $lists = $seckill;
                break;
            case 'un_seckill':
                $lists = $unSeckill;
                break;
            case 'wait_review':
                $lists = $waitReview;
                break;
            case 'refuse_review':
                $lists = $refuseReview;
                break;
        }
        // 组装信息
        $review_status_desc = ['待审核','审核通过','审核拒绝'];
        foreach($lists as &$item) {
            // 秒杀价格
            $price = SeckillGoods::where([
                'del' => 0,
                'shop_id' => $get['shop_id'],
                'seckill_id' => $item['seckill_id'],
                'goods_id' => $item['goods_id'],
                'start_date' => $item['start_date'],
                'end_date' => $item['end_date'],
            ])->column('price', 'id');
            $seckill_min_price = min($price);
            $seckill_max_price = max($price);
            $item['seckill_price'] = $seckill_min_price == $seckill_max_price ? '¥ ' .$seckill_min_price : '¥ '. $seckill_min_price . ' ~ ¥ ' . $seckill_max_price;
            // 商品价格
            $item['goods_price'] = $item['min_price'] == $item['max_price'] ? '¥ ' .$item['min_price'] : '¥ '. $item['min_price'] .' ~ ¥ '. $item['max_price'];
            // 参与日期
            $item['date'] = $item['start_date'] . ' ~ ' . $item['end_date'];
            // 参与时段
            $item['time'] = $item['start_time'] . ' ~ ' . $item['end_time'];
            // 审核状态
            $item['review_status_desc'] = $review_status_desc[$item['review_status']];
        }

        // 分页
        $count = count($lists);
        $index = ($get['page'] -1) * $get['limit'];
        $lists = array_slice($lists, $index, $get['limit']);

        // 返回
        return [
            'count' => $count,
            'lists' => $lists,
        ];
    }

    public static function getSeckillGoods($id,$seckill_id, $start_date, $end_date){
        $skill_goods = SeckillGoods::alias('sg')
            ->join('goods_item gi','sg.item_id = gi.id')
            ->where(['del'=>0,'sg.goods_id'=>$id,'sg.seckill_id'=>$seckill_id, 'sg.start_date'=>$start_date,'sg.end_date'=>$end_date])
            ->field('sg.*,gi.image,gi.spec_value_str,gi.price as goods_price')
            ->select()
            ->toArray();

        $goods_id = $skill_goods[0]['goods_id'];
        $goods = Goods::where(['del'=>0,'id'=>$goods_id])->field('image,name')->find();

        foreach ($skill_goods as &$item){
            $item['name'] = $goods['name'];
            if(!$item['image']){
                $item['image'] = $goods['image'];
            }
            $item['date'] = $item['start_date'] . ' ~ ' . $item['end_date'];
        }

        return $skill_goods;
    }

    public static function editGoods($post){
        Db::startTrans();
        try{
            // 开始日期结束日期处理
            $start_end = explode('~', $post['start_end']);

            $now = time();
            foreach ($post['item'] as  $goods){
                $review_status = SeckillGoods::where('id', $goods['id'])->value('review_status');
                $review_status = $review_status == 2 ? 0 : $review_status;
                $update_data = [
                    'start_date'    => trim($start_end[0]),
                    'end_date'      => trim($start_end[1]),
                    'seckill_id'    => $post['seckill_id'],
                    'price'         => $goods['price'],
                    'update_time'   => $now,
                    'review_status' => $review_status,
                ];
                SeckillGoods::where(['id'=>$goods['id']])->update($update_data);
            }
            Db::commit();
            return true;
        }catch(\Exception $e) {
            self::$error = $e->getMessage();
            Db::rollback();
            return false;
        }
    }

    public static function delGoods($id,$seckill_id,$start_date,$end_date,$shop_id){
        try{
            $update_data = [
                'update_time'   => time(),
                'del'           => 1,
            ];
            SeckillGoods::where([
                'del'=>0,
                'goods_id'=>$id,
                'seckill_id'=>$seckill_id,
                'shop_id' => $shop_id,
                'start_date' => $start_date,
                'end_date' => $end_date,
            ])->update($update_data);
            return true;
        }catch(\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }
}
