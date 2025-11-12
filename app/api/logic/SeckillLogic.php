<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\api\logic;
use app\common\server\ConfigServer;
use app\common\server\UrlServer;
use think\facade\Db;

class SeckillLogic{

    public static function seckillTime(){
        $time_list = Db::name('seckill_time')
            ->where(['del'=>0])
            ->order('start_time asc')
            ->field('id,start_time,end_time')
            ->select();
        $now = time();
        foreach ($time_list as &$item){
            $item['status'] = 2;
            $item['tips'] = '';
            $start_time = strtotime(date('Y-m-d'.$item['start_time']));
            $end_time = strtotime(date('Y-m-d'.$item['end_time']));
            if($now >= $end_time ){
                $item['tips'] = '已结束';
            }
            if($start_time <= $now && $now < $end_time){
                $item['status'] = 1;
                $item['tips'] = '抢购中';
            }
            if($start_time >= $now){
                $item['tips'] = '未开始';
                $item['status'] = 0;
            }
        }
        return $time_list;
    }

    public static function seckillGoods($id,$page,$size){
        $where[] = ['g.del','=',0];
        $where[] = ['sg.del','=',0];
        $where[] = ['g.status','=',1];
        $where[] = ['sg.seckill_id','=',$id];

        $goods_count =  Db::name('goods g')
            ->join('seckill_goods sg','g.id = sg.goods_id')
            ->group('sg.goods_id')
            ->order('sg.sales_sum desc')
            ->where($where)
            ->count();

        $goods_list =  Db::name('goods g')
            ->join('seckill_goods sg','g.id = sg.goods_id')
            ->where($where)
            ->group('sg.goods_id')
            ->order('sg.sales_sum,sg.id desc')
            ->page($page,$size)
            ->field('g.id,g.name,g.image,g.min_price,sg.price as seckill_price,sg.sales_sum')
            ->select();


        $default_image = UrlServer::getFileUrl(ConfigServer::get('website', 'goods_image', ''));
        foreach ($goods_list as &$item){
            // 传入默认商品主图
            if(empty( $item['image'])) {
                $item['image'] = $default_image;
            }else{
                $item['image'] = UrlServer::getFileUrl($item['image']);
            }
        }

        $more = is_more($goods_count,$page,$size);  //是否有下一页

        $data = [
            'list'          => $goods_list,
            'page'          => $page,
            'size'          => $size,
            'count'         => $goods_count,
            'more'          => $more
        ];
        return $data;
    }


    //获取当前的秒杀时段
    public static function getSeckill()
    {
        $seckill_time = Db::name('seckill_time')
            ->where(['del'=>0])
            ->order('start_time asc')
            ->field('id,start_time,end_time')
            ->select();
        $seckill = [];
        $now = time();

        foreach ($seckill_time as $item){
            $start_time = strtotime(date('Y-m-d '.$item['start_time']));
            $end_time = strtotime(date('Y-m-d '.$item['end_time']));

            if($start_time <= $now && $now < $end_time){
                $item['end_time'] = $end_time;
                $seckill = $item;
                break;
            }
        }
        return $seckill;
    }
    
    //获取当前的秒杀信息和秒杀商品
    public static function getSeckillGoods()
    {

        $seckill = self::getSeckill();
        $seckill_goods = [];
        if ($seckill) {
            $seckill_goods = Db::name('seckill_goods')
                ->where(['seckill_id'=>$seckill['id'],'del'=>0,'review_status' => 1])
                // 需要加上日期限制
                ->where('start_date', '<=', date('Y-m-d'))
                ->where('end_date', '>=', date('Y-m-d'))
                ->column('id as seckill_goods_id,price,goods_id','item_id');
        }

        return ['seckill'=>$seckill,'seckill_goods'=>$seckill_goods];
    }
}