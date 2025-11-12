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
use app\common\model\goods\Goods;
use app\common\model\goods\GoodsColumn;

/**
 * 商品栏目-逻辑
 * Class GoodsColumnLogic
 * @package app\admin\logic
 */
class ColumnLogic extends Logic
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
        $result = GoodsColumn::where(['del' =>0])
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
     * @return GoodsColumn|\think\Model
     *@author 段誉(2021/4/15 10:54)
     */
    public static function add($post)
    {
        return GoodsColumn::create([
            'name'     => $post['name'],
            'remark'   => $post['remark'] ?? '',
            'status'   => isset($post['status']) && $post['status'] == 'on' ? 1 : 0,
        ]);
    }


    /**
     * Notes: 编辑
     * @param $post
     * @return GoodsColumn
     *@author 段誉(2021/4/15 10:54)
     */
    public static function edit($post)
    {
        return GoodsColumn::update([
            'name'     => $post['name'],
            'remark'   => $post['remark'] ?? '',
            'status'   => isset($post['status']) && $post['status'] == 'on' ? 1 : 0,
        ], ['id' => $post['id']]);
    }


    /**
     * Notes: 删除
     * @param $id
     * @author 段誉(2021/6/24 2:51)
     * @return bool
     */
    public static function del($id)
    {
        //栏目删除,则栏目商品都删除
        GoodsColumn::update(['del' => 1], ['id' => $id]);
        Goods::whereFindInSet('column_ids', $id)->update(['column_ids' => '']);
        return true;
    }

    /**
     * 列表（不分页）
     */
    public static function getList()
    {
        return GoodsColumn::where(['del' => 0])->order('sort', 'desc')->column('id,name');
    }
}