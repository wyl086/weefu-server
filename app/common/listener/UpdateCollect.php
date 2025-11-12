<?php

namespace app\common\listener;

use app\common\enum\GoodsEnum;
use app\common\model\goods\Goods;
use app\common\model\goods\GoodsCollect;

/**
 * 下架或移除商品更新商品收藏夹
 * Class UpdateCollect
 * @package app\common\listener
 */
class UpdateCollect
{

    public function handle($params)
    {
        try {
            $goods_ids = $params['goods_id'] ?? '';

            if (empty($goods_ids)) {
                return true;
            }

            $goods_ids = is_array($goods_ids) ? $goods_ids : [$goods_ids];

            $map1 = [
                ['id', 'in', $goods_ids],
                ['status', '<>', GoodsEnum::STATUS_SHELVES],
            ];
            $map2 = [
                ['id', 'in', $goods_ids],
                ['del', '<>', GoodsEnum::DEL_NORMAL],
            ];
            $map3 = [
                ['id', 'in', $goods_ids],
                ['audit_status', '=', GoodsEnum::AUDIT_STATUS_REFUSE],
            ];
            $need_handle = Goods::whereOr([$map1, $map2, $map3])->column('id');

            if (empty($need_handle)) {
                return true;
            }

            GoodsCollect::whereIn('goods_id', $need_handle)->update([
                'status' => 0,
                'update_time' => time()
            ]);

            return true;

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }


}