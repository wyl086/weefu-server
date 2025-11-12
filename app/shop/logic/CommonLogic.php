<?php
namespace  app\shop\logic;

use app\common\basics\Logic;
use app\common\model\goods\Goods;
use app\common\model\goods\GoodsItem;
use app\common\server\UrlServer;
use app\common\enum\GoodsEnum;

class CommonLogic extends Logic
{
    public static function getGoodsList($get)
    {
        $where = [
            ['shop_id', '=', $get['shop_id']],
            ['del', '=', GoodsEnum::DEL_NORMAL], // 未删除
            ['audit_status', '=', GoodsEnum::AUDIT_STATUS_OK], // 审核通过
            ['status', '=', GoodsEnum::STATUS_SHELVES], // 上架中
            ['type', '=', GoodsEnum::TYPE_ACTUAL] // 实物商品才参与
        ];
        if(!empty($get['keyword'])) {
            $where[] = ['name', 'like','%'. $get['keyword'].'%'];
        }
        if(!empty($get['cid'])) {
            $where[] = ['shop_cate_id', '=', $get['cid']];
        }

        $lists = Goods::field('id,name,image,min_price as min_max_price,min_price,max_price,stock')
            ->where($where)
            ->order('id', 'desc')
            ->page($get['page'], $get['limit'])
            ->select()
            ->toArray();

        $count = Goods::field('id,name,image,min_price,max_price,stock')
            ->where($where)
            ->count();

        foreach($lists as &$item) {
           $item['image'] = UrlServer::getFileUrl($item['image']);
        }

        return [
            'count' => $count,
            'lists' => $lists
        ];
    }

    //获取商品列表
    public static function getGoodsListTwo($get,$is_item = false){
        $where = [
            ['shop_id', '=', $get['shop_id']],
            ['del', '=', GoodsEnum::DEL_NORMAL], // 未删除
            ['audit_status', '=', GoodsEnum::AUDIT_STATUS_OK], // 审核通过
            ['status', '=', GoodsEnum::STATUS_SHELVES], // 上架中
            ['type', '=', GoodsEnum::TYPE_ACTUAL] // 实物商品才参与
        ];

        if (isset($get['keyword']) && $get['keyword']) {
            $where[] = ['name', 'like', '%' . $get['keyword'] . '%'];
        }
        if(!empty($get['cid'])) {
            $where[] = ['shop_cate_id', '=', $get['cid']];
        }

        $goods_count = Goods::where($where)->count();

        $goods_list = Goods::where($where)
            ->page($get['page'], $get['limit'])
            ->column('*','id');

        foreach ($goods_list as &$item) {
            $item['goods_item'] = [];
            $item['price'] = '￥'.$item['min_price'];
            if($item['max_price'] != $item['min_price']){
                $item['price'] = '￥'.$item['min_price'].'~'.'￥'.$item['max_price'];
            }
            $item['create_time_desc'] = date('Y-m-d H:i:s',$item['create_time']);
            $item['image'] = UrlServer::getFileUrl($item['image']);
        }

        if($is_item){
            $goods_ids = array_keys($goods_list);
            $goods_item = GoodsItem::where(['goods_id'=>$goods_ids])->select()->toArray();
            foreach ($goods_item as $items){
                if(isset($goods_list[$items['goods_id']])){
                    if($items['image']){
                        $items['image'] = UrlServer::getFileUrl($items['image']);
                    }else{
                        $items['image'] = $goods_list[$items['goods_id']]['image'];
                    }
                    $goods_list[$items['goods_id']]['goods_item'][] = $items;
                }
            }
        }
        return ['count' => $goods_count, 'list' =>array_values($goods_list)];
    }
}