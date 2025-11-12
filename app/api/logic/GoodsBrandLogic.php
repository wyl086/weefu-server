<?php
namespace app\api\logic;

use app\common\basics\Logic;
use app\common\model\goods\GoodsBrand;

class GoodsBrandLogic extends Logic
{
    /**
     * 获取品牌列表
     */
    public static function getGoodsBrandList()
    {
        $where = [
            'del' => 0, // 未删除
            'is_show' => 1, // 显示
        ];
        $list = GoodsBrand::field('id,name,image,initial')
            ->where($where)
            ->order('sort', 'asc')
            ->select()
            ->toArray();

        return self::format($list);
    }

    /**
     * 格式化品牌数据
     */
    public static function format($list)
    {
        // 生成A-Z字母
        $letters = range('A', 'Z');
        $newList = [];
        foreach($letters as $key => $letter) {
            $newList[$key]['letter'] = $letter;
            $newList[$key]['list'] = [];
            foreach($list as $item) {
                if(strtoupper($item['initial']) == $letter) {
                    $newList[$key]['list'][] = $item;
                }
            }
            // 去除字母下没有品牌的项
            if(!$newList[$key]['list']) {
                unset($newList[$key]);
            }
        }
        // 重置下标索引
        $newList = array_merge([], $newList);
        return $newList;
    }
}