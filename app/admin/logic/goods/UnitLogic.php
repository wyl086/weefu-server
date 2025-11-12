<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\admin\logic\goods;


use app\common\basics\Logic;
use app\common\model\goods\GoodsUnit;

/**
 * 商品单位逻辑
 * Class UnitLogic
 * @package app\admin\logic\goods
 */
class UnitLogic extends Logic
{

    /**
     * Notes: 列表
     * @param $get
     * @author 段誉(2021/4/15 10:53)
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function lists($get)
    {
        $result = GoodsUnit::where(['del' =>0])
            ->order('sort')
            ->paginate([
                'list_rows'=> $get['limit'],
                'page'=> $get['page']
            ]);

        return ['count' => $result->total(), 'lists' => $result->getCollection()];
    }


    /**
     * Notes: 添加
     * @param $post
     * @return GoodsUnit|\think\Model
     *@author 段誉(2021/4/15 10:54)
     */
    public static function addUnit($post)
    {
        return GoodsUnit::create([
            'name'     => $post['name'],
            'sort'     => $post['sort'] ?? 100,
        ]);
    }


    /**
     * Notes: 编辑
     * @param $post
     * @return GoodsUnit
     *@author 段誉(2021/4/15 10:54)
     */
    public static function editUnit($post)
    {
        return GoodsUnit::update([
            'name' => $post['name'],
            'sort' => $post['sort'] ?? 100
        ], ['id' => $post['id']]);
    }


    /**
     * Notes: 删除
     * @param $id
     * @return GoodsUnit
     *@author 段誉(2021/4/15 10:54)
     */
    public static function del($id)
    {
        return GoodsUnit::update(['del' => 1], ['id' => $id]);
    }

}