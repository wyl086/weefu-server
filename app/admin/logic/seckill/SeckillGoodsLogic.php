<?php
namespace app\admin\logic\seckill;

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
    public static function statistics()
    {
        // 秒杀中商品
        $where = [
            ['sg.del', '=', 0],
            ['sg.review_status', '=', 1],
        ];
        $lists = SeckillGoods::alias('sg')
            ->leftJoin('seckill_time st', 'st.id=sg.seckill_id')
            ->field('sg.goods_id,sg.start_date,sg.end_date,st.start_time,st.end_time')
            ->where($where)
            ->group('sg.goods_id,sg.start_date,sg.end_date,st.start_time,st.end_time')
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
        $waitReview = SeckillGoods::where(['del'=>0, 'review_status'=>0])->group('seckill_id,goods_id,start_date,end_date')->count();
        // 审核拒绝
        $refuseReview = SeckillGoods::where(['del'=>0, 'review_status'=>2])->group('seckill_id,goods_id,start_date,end_date')->count();
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

    public static function goodsList($get)
    {
        $where = [
            ['sg.del', '=', 0],
        ];

        // 商品名称
        if(isset($get['name']) && !($get['name'] == '')) {
            $where[] = ['g.name', 'like', '%'.trim($get['name']).'%'];
        }

        // 参与日期
        if(isset($get['start_end']) && !empty($get['start_end'])) {
            $start_end = explode('~', $get['start_end']);
            $where[] = ['sg.start_date', '=', trim($start_end[0])];
            $where[] = ['sg.end_date', '=', trim($start_end[1])];
        }
        // 参与时段
        if(isset($get['seckill_id']) && !empty($get['seckill_id'])) {
            $where[] = ['sg.seckill_id', '=', $get['seckill_id']];
        }

        $lists = SeckillGoods::alias('sg')
            ->leftJoin('seckill_time st', 'st.id=sg.seckill_id')
            ->leftJoin('goods g', 'sg.goods_id=g.id')
            ->leftJoin('shop s', 's.id=sg.shop_id')
            ->field('sg.seckill_id,sg.goods_id,review_status,review_desc,start_date,end_date,start_time,end_time,g.name,g.image,g.min_price,g.max_price,s.id as shop_id,s.name as shop_name,s.type as shop_type,s.logo as shop_logo')
            ->where($where)
            ->group('sg.seckill_id,sg.goods_id,review_status,review_desc,start_date,end_date,start_time,end_time,g.name,g.image,g.min_price,g.max_price,s.id,s.name,s.type,s.logo')
            ->order(['sg.id' => 'desc'])
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
        $shop_type_desc = [1=>'官方自营', 2=>'入驻商家'];
        foreach($lists as &$item) {
            $item['shop_logo'] = empty($item['shop_logo']) ? '' : UrlServer::getFileUrl($item['shop_logo']);
            // 秒杀价格
            $price = SeckillGoods::where([
                'del' => 0,
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
            // 商家类型
            $item['shop_type_desc'] = $shop_type_desc[$item['shop_type']];
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
            ->join('shop s', 's.id=sg.shop_id')
            ->where(['sg.del'=>0,'sg.goods_id'=>$id,'sg.seckill_id'=>$seckill_id, 'sg.start_date'=>$start_date,'sg.end_date'=>$end_date])
            ->field('sg.*,gi.image,gi.spec_value_str,gi.price as goods_price,s.name as shop_name')
            ->select()
            ->toArray();

        $goods_id = $skill_goods[0]['goods_id'];
        $goods = Goods::where(['del'=>0,'id'=>$goods_id])->field('image,name')->find()->toArray();

        foreach ($skill_goods as &$item){
            $item['name'] = $goods['name'];
            if(!$item['image']){
                $item['image'] = $goods['image'];
            }

            $item['date'] = $item['start_date'] . ' ~ ' . $item['end_date'];
        }

        return $skill_goods;
    }

    public static function reAudit($post)
    {
        try{
            $updateData = [
                'review_status' => 2,
                'review_desc' => $post['reason'],
                'update_time' => time()
            ];
            $where = [
                'del' => 0,
                'goods_id' => $post['goods_id'],
                'seckill_id' => $post['seckill_id'],
                'start_date' => $post['start_date'],
                'end_date' => $post['end_date'],
            ];
            SeckillGoods::where($where)->update($updateData);
            return true;
        }catch(\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    public static function audit($post)
    {
        try{
            $updateData = [
                'review_status' => $post['audit_status'],
                'review_desc' => $post['audit_remark'],
                'update_time' => time()
            ];
            $where = [
                'del' => 0,
                'goods_id' => $post['goods_id'],
                'seckill_id' => $post['seckill_id'],
                'start_date' => $post['start_date'],
                'end_date' => $post['end_date'],
            ];
            SeckillGoods::where($where)->update($updateData);
            return true;
        }catch(\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }
}