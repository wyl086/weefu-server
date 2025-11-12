<?php
namespace app\api\logic;

use app\common\basics\Logic;
use app\common\model\goods\GoodsCategory;
use app\common\server\UrlServer;

class GoodsCategoryLogic extends Logic
{
    /**
     * 获取平台一级分类
     */
    public static function getLevelOneList()
    {
        $where = [
            'del' => 0, // 未删除
            'is_show' => 1, // 显示
            'pid' => 0
        ];
        //
        $list = GoodsCategory::field('id,name,image,bg_image')
            ->withAttr('bg_image', function ($value, $data) {
                if (!empty($value)) {
                    return UrlServer::getFileUrl($value);
                }
                return $value;
            })
            ->where($where)
            ->order('sort', 'asc')
            ->select()
            ->toArray();

        return $list;
    }

    /**
     * 获取一级分类下的后代分类
     */
    public static function getListByLevelOne($id)
    {
        $where = [
            'del' => 0, // 未删除
            'is_show' => 1, // 显示
            'pid' => $id
        ];

        $list = GoodsCategory::field('id,name,image')
            ->where($where)
            ->order('sort', 'asc')
            ->select()
            ->toArray();

        foreach($list as &$item) {
            $where = [
                'del' => 0, // 未删除
                'is_show' => 1, // 显示
                'pid' => $item['id']
            ];
            $item['children'] =  GoodsCategory::field('id,name,image')
                ->where($where)
                ->order('sort', 'asc')
                ->select()
                ->toArray();
        }

        return $list;
    }
}