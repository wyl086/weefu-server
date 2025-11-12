<?php
namespace app\api\logic;

use app\common\basics\Logic;
use app\common\model\goods\GoodsCollect;

class GoodsCollectLogic extends Logic
{
    /**
     * 商品收藏
     */
    public static function changeStatus($goodsId, $userId)
    {
        $data = GoodsCollect::where(['goods_id'=>$goodsId,'user_id'=>$userId])->findOrEmpty();
        if($data->isEmpty()) { // 没数据，首次收藏
            $insertData = [
                'goods_id' => $goodsId,
                'user_id' => $userId,
                'status' => 1,
                'create_time' => time()
            ];
            $result = GoodsCollect::create($insertData);

            return [
                'result' => $result,
                'msg' => '收藏成功'
            ];
        }else{ // 收藏过，修改收藏状态
            $newStatus = $data['status'] ? 0 : 1;
            $msg = $newStatus ? '收藏成功' : '取消收藏';
            $updateData = [
                'id' => $data['id'],
                'status' => $newStatus,
                'update_time' => time()
            ];
            $result = GoodsCollect::update($updateData);
            return [
                'result' => $result,
                'msg' => $msg
            ];
        }
    }

    public static function lists($get)
    {
        $where = [
            'gc.user_id' => $get['user_id'],
            'gc.status' => 1,
        ];
        $lists = GoodsCollect::alias('gc')
            ->field('g.id,g.shop_id,g.name,g.image,g.min_price,g.status,g.audit_status,g.del')
            ->leftJoin('goods g', 'g.id=gc.goods_id')
            ->where($where)
            ->order('gc.update_time', 'desc')
            ->page($get['page_no'], $get['page_size'])
            ->select()
            ->toArray();
        $count = GoodsCollect::alias('gc')
            ->field('g.id,g.name,g.image,g.min_price,g.status')
            ->leftJoin('goods g', 'g.id=gc.goods_id')
            ->where($where)
            ->count();
        foreach($lists as &$item) {
            // 标识商品是否失效
            $item['is_valid'] = 1;
            // 下架、待审核、审核拒绝、已删除的均标识为失效
            if($item['status'] == 0 || $item['audit_status'] != 1 || $item['del'] == 1) {
                $item['is_valid'] = 0;
            }
        }
        $data = [
            'lists' => $lists,
            'count' => $count,
            'more' => is_more($count, $get['page_no'], $get['page_size']),
            'page_no' => $get['page_no'],
            'page_size' => $get['page_size'],
        ];
        return $data;
    }
}