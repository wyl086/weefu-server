<?php
namespace app\api\logic;

use app\common\basics\Logic;
use app\common\model\goods\GoodsColumn;

class GoodsColumnLogic extends Logic
{
    /**
     * 获取商品栏目列表
     */
    public static function getGoodsColumnList()
    {
        $where = [
            'del' => 0, // 未删除
            'status' => 1, // 显示
        ];
        $list = GoodsColumn::field('id,name,remark')
            ->where($where)
            ->order('sort', 'asc')
            ->select()
            ->toArray();
        return $list;
    }
}