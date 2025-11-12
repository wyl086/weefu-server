<?php
namespace app\api\logic;

use app\common\basics\Logic;
use app\common\model\goods\Goods;
use app\common\model\order\OrderGoods;
use app\common\model\goods\GoodsComment;
use app\common\model\goods\GoodsCommentImage;
use app\common\model\order\Order;
use app\common\model\shop\Shop;
use app\common\server\UrlServer;
use app\common\model\goods\GoodsItem;
use think\facade\Db;

class GoodsCommentLogic extends Logic
{
    //分类列表
    public static function category($get){
        // 全部评论数
        $all_count = GoodsComment::where(['del'=>0,'status'=>1,'goods_id'=>$get['goods_id']])
            ->count();
        // 好评: 商品评价星级 > 3
        $good_count = GoodsComment::where(['del'=>0,'status'=>1,'goods_id'=>$get['goods_id']])
            ->where('goods_comment','>',3)
            ->count();
        // 中评：商品评价星级 = 3
        $medium_count = GoodsComment::where(['del'=>0,'status'=>1,'goods_id'=>$get['goods_id']])
            ->where('goods_comment','=',3)
            ->count();
        // 差评：商品评价星级 < 3
        $bad_count = GoodsComment::where(['del'=>0,'status'=>1,'goods_id'=>$get['goods_id']])
            ->where('goods_comment','<',3)
            ->count();
        // 图片评论数量
        $image_count = GoodsComment::where(['del'=>0,'status'=>1,'goods_id'=>$get['goods_id']])
            ->where('is_image_comment','=',1)
            ->count();
        // 好评率
        if($all_count == 0){
            $score ='100%';
        }else{
            $score = round($good_count/$all_count*100,2).'%';
        }

        return ['comment'=>
            [
                [
                    'name'  => '全部',
                    'id'    => 0,
                    'count' => $all_count
                ],
                [
                    'name'  => '晒图',
                    'id'    => 1,
                    'count' => $image_count
                ],
                [
                    'name'  => '好评',
                    'id'    => 2,
                    'count' => $good_count
                ],
                [
                    'name'  => '中评',
                    'id'    => 3,
                    'count' => $medium_count
                ],
                [
                    'name'  => '差评',
                    'id'    => 4,
                    'count' => $bad_count
                ]
            ] ,
            'percent'   => $score
        ];
    }

    public static function lists($get)
    {
        $where = [
            ['gc.goods_id', '=', $get['goods_id']],
            ['gc.del', '=', 0],
            ['gc.status', '=', 1],
        ];
        $get['type'] = $get['type'] ?? 0;
        switch($get['type']) {
            case 1:  // 晒图
                $where[] = ['gc.is_image_comment', '=', 1];
                break;
            case 2:  // 好评
                $where[] = ['gc.goods_comment', '>', 3];
                break;
            case 3:  // 中评
                $where[] = ['gc.goods_comment', '=', 3];
                break;
            case 4:  // 差评
                $where[] = ['gc.goods_comment', '<', 3];
                break;
        }

        $lists = GoodsComment::alias('gc')
            ->field('gc.id,gc.goods_comment,gc.create_time,gc.comment,u.avatar,u.nickname,g.name as goods_name,reply,spec_value_str')
            ->leftJoin('user u', 'u.id=gc.user_id')
            ->leftJoin('goods g', 'g.id=gc.goods_id')
            ->leftJoin('goods_item gi', 'gi.id=gc.item_id')
            ->where($where)
            ->order('gc.id', 'desc')
            ->page($get['page_no'], $get['page_size'])
            ->select()
            ->toArray();
        $count = GoodsComment::alias('gc')
            ->field('gc.goods_comment,gc.create_time,gc.comment,u.avatar,u.nickname,g.name as goods_name')
            ->leftJoin('user u', 'u.id=gc.user_id')
            ->leftJoin('goods g', 'g.id=gc.goods_id')
            ->where($where)
           ->count();

        foreach($lists as &$item) {
            // 头像
            $item['avatar'] = UrlServer::getFileUrl($item['avatar']);
            // 图片评价
            $item['image'] = GoodsCommentImage::where('goods_comment_id', $item['id'])->column('uri');
            foreach($item['image'] as $subKey => $subItem) {
                $item['image'][$subKey] = UrlServer::getFileUrl($subItem);
            }
        }
        $data = [
            'lists' => $lists,
            'count' => $count,
            'more'  => is_more($count, $get['page_no'], $get['page_size']),
            'page_no' => $get['page_no'],
            'page_size' => $get['page_size']
        ];

        return $data;
    }

    public static function addGoodsComment($post){
        Db::startTrans();
        try{
            $order_goods= OrderGoods::where(['id'=>$post['order_goods_id'],'is_comment'=>0])
                ->field('order_id,goods_id,item_id,shop_id')
                ->findOrEmpty();
            if($order_goods->isEmpty()){
                throw new \Exception('商品已评价，请勿重复评价');
            }

            // 校验商品是否已下架或删除
            $goods = Goods::where('id', $order_goods['goods_id'])->findOrEmpty();
            if ($goods->isEmpty() || $goods['del'] > 0 || $goods['status'] != 1) {
                throw new \Exception('商品已下架或不存在');
            }

            $order_goods = $order_goods->toArray();

            $time = time();
            $comment_data= [
                'order_goods_id'        =>$post['order_goods_id'],
                'user_id'               => $post['user_id'],
                'shop_id'               => $order_goods['shop_id'],
                'goods_id'              => $order_goods['goods_id'],
                'item_id'               => $order_goods['item_id'],
                'goods_comment'         => $post['goods_comment'],
                'description_comment'   => $post['description_comment'],
                'service_comment'       => $post['service_comment'],
                'express_comment'       => $post['express_comment'],
                'create_time'           => $time,
                'update_time'           => $time,
            ];
            // 文字评价
            isset($post['comment']) && $comment_data['comment'] = $post['comment'];

            $goodsComment = GoodsComment::create($comment_data);
            if(!$goodsComment->id){
                throw new \Exception('评价失败，请重新提交');
            }

            // 图片评价
            if(isset($post['image']) && !empty($post['image'])){
                foreach ($post['image'] as $image_val){
                    $image[]= ['shop_id' => $order_goods['shop_id'], 'uri'=>$image_val,'goods_comment_id'=>$goodsComment->id];
                }
                $goodsCommentImage = new GoodsCommentImage();
                $goodsCommentImage->saveAll($image);
                GoodsComment::update([
                    'id' => $goodsComment->id,
                    'is_image_comment' => 1
                ]);
            }

            // 将子订单是否评价标识置为已评价
            OrderGoods::where('id',$post['order_goods_id'])->update(['is_comment'=>1]);

            // 检查主订单下的所有子订单是否已完成评价
            $isCommentArr = OrderGoods::where('order_id', $order_goods['order_id'])->column('is_comment', 'id');
            if(in_array(0, $isCommentArr)) { // 还有未评价的子订单
                Order::where('id', $order_goods['order_id'])->update(['is_comment'=>1]); // 部分评价
            }else{ // 全部子订单已完成评价
                Order::where('id', $order_goods['order_id'])->update(['is_comment'=>2]); // 已全部评价
            }
            //更新店铺评分和评级
            $shop_id = $order_goods['shop_id'];
            self::setShopScore($shop_id);
            Db::commit();
            return true;
        }catch(\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }

    /***
     * 更新店铺评分评级
     * @param $shop_id
     */
    public static function setShopScore($shop_id){
        $comment_sum = GoodsComment::where('shop_id',$shop_id)->sum('goods_comment'); //该店铺商品评分之和
        $service_comment_sum = GoodsComment::where('shop_id',$shop_id)->sum('service_comment'); //该店铺服务评分之和
        $express_comment_sum = GoodsComment::where('shop_id',$shop_id)->sum('express_comment'); //该店铺物流评分之和
        $description_comment_sum = GoodsComment::where('shop_id',$shop_id)->sum('description_comment'); //该店铺描述相符评分之和
        $comment_count = GoodsComment::where('shop_id',$shop_id)->count(); //该店铺商品评论数
        $shop_score = round($comment_sum / $comment_count,1); //店铺评分
        $shop_star = round(($service_comment_sum + $express_comment_sum + $description_comment_sum) /(3*$comment_count),1); //店铺评级
        Shop::where('id',$shop_id)->update(['score'=>$shop_score,'star'=>$shop_star]);
    }

    public static function getUnCommentOrder($get){
        $where = [
            ['order_status', '=', Order::STATUS_FINISH],
            ['del', '=', 0],
            ['user_id', '=', $get['user_id']],
        ];

        $orderGoodsPreModel = 'order_goods_un_comment.goods_item';
        $where[] = ['is_comment', 'in', [0,1]]; // 未评价、部分未评价

        $lists = Order::field('id,shop_id,order_sn,create_time,is_comment')
            ->with(['shop', $orderGoodsPreModel])
            ->where($where)
            ->page($get['page_no'], $get['page_size'])
            ->select()
            ->toArray();

        $count = Order::where($where)->count();

        $data = [
            'lists' => $lists,
            'count' => $count,
            'more'  => is_more($count, $get['page_no'], $get['page_size']),
            'page_no' => $get['page_no'],
            'page_size' => $get['page_size']
        ];
        return $data;
    }

    public static function getCommentOrder($get)
    {
        $lists = GoodsComment::field('id,order_goods_id,shop_id,goods_id,item_id,goods_comment,comment,create_time')
            ->with(['goods', 'order_goods', 'goods_item', 'goods_comment_image'])
            ->where([
                'user_id' => $get['user_id'],
                'del' => 0,
                'status' => 1
            ])
            ->order('id', 'desc')
            ->page($get['page_no'], $get['page_size'])
            ->select()
            ->toArray();
        $count = GoodsComment::where('user_id', $get['user_id'])->count();

        // 获取所有店铺名称、logo
        $shop_ids = array_unique(array_column($lists,'shop_id'));
        $shop_name_lists = Shop::where(['id'=>$shop_ids])->column('name,logo', 'id');

        // 图片处理
        foreach($lists as &$item) {
            $item['shop_name'] = '';
            $item['shop_logo'] = '';
            if(isset($shop_name_lists[$item['shop_id']]) && $shop_name_lists[$item['shop_id']]){
                $shop_info = $shop_name_lists[$item['shop_id']];
                $item['shop_name'] = $shop_info['name'];
                $item['shop_logo'] = UrlServer::getFileUrl($shop_info['logo']);
            }
            foreach($item['goods_comment_image'] as $subItem) {
                $item['goods_comment_image_arr'][] = UrlServer::getFileUrl($subItem['uri']);
            }
        }

        $data = [
            'count' => $count,
            'lists' => $lists,
            'more'  => is_more($count, $get['page_no'], $get['page_size']),
            'page_no' => $get['page_no'],
            'page_size' => $get['page_size']
        ];
        return $data;
    }

    public static function getCommentPage($get)
    {
        $orderGoods = OrderGoods::field('id,item_id, shop_id,goods_name,goods_num,goods_price,total_price')
            ->with(['shop', 'goods_item'])
            ->where('id', $get['order_goods_id'])
            ->findOrEmpty();
        if($orderGoods->isEmpty()) {
            self::$error = '获取失败';
            return false;
        }
        return $orderGoods->toArray();
    }



    /**
     * @notes 校验商品
     * @param $goodsId
     * @return bool
     * @author 段誉
     * @date 2022/11/03 18:25
     */
    public static function checkGoods($goodsId)
    {
        $goods = Goods::findOrEmpty($goodsId);
        if ($goods->isEmpty() || $goods['del'] > 0 || $goods['status'] != 1) {
            self::$error = '商品已下架或不存在';
            return false;
        }
        return true;
    }
}
