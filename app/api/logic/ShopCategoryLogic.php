<?php
namespace app\api\logic;

use app\common\basics\Logic;
use app\common\model\shop\ShopCategory;

class ShopCategoryLogic extends Logic
{
    /**
     * 店铺主营类目
     */
    public static function getList()
    {
        $where = [
            'del' => 0, // 未删除
        ];
        $list = ShopCategory::field('id,name,image')
            ->where($where)
            ->order(['sort'=>'asc','id'=>'desc'])
            ->select()
            ->toArray();
        return $list;
    }
}