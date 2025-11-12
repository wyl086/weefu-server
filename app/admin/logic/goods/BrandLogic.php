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
use app\common\model\goods\GoodsBrand;


/**
 * 商品品牌
 * Class GoodsBrandLogic
 * @package app\admin\logic
 */
class BrandLogic extends Logic
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
        $where[] = ['del', '=', 0];
        if(isset($get['name']) && $get['name']) {
            $where[] = ['name','like','%'.$get['name'].'%'];
        }

        $lists = GoodsBrand::where($where)
            ->order('sort')
            ->paginate([
                'list_rows'=> $get['limit'],
                'page'=> $get['page'],
            ]);
        return ['count' => $lists->total(), 'lists' => $lists->getCollection()];
    }


    /**
     * Notes: 添加
     * @param $post
     * @return GoodsBrand|\think\Model
     *@author 段誉(2021/4/15 10:54)
     */
    public static function add($post)
    {
        return GoodsBrand::create([
            'name'     => $post['name'],
            'initial'  => $post['initial'],
            'image'    => $post['image'] ?? '',
            'sort'     => $post['sort'] ?? 100,
            'is_show'  => $post['is_show'],
            'remark'   => $post['remark'] ?? '',
        ]);
    }


    /**
     * Notes: 编辑
     * @param $post
     * @return GoodsBrand
     *@author 段誉(2021/4/15 10:54)
     */
    public static function edit($post)
    {
        return GoodsBrand::update([
            'name'     => $post['name'],
            'initial'  => $post['initial'],
            'image'    => $post['image'] ?? '',
            'sort'     => $post['sort'] ?? 100,
            'is_show'  => $post['is_show'],
            'remark'   => $post['remark'] ?? '',
        ], ['id' => $post['id']]);
    }


    /**
     * Notes: 删除
     * @param $id
     * @return GoodsBrand
     *@author 段誉(2021/4/15 10:54)
     */
    public static function del($id)
    {
        return GoodsBrand::update(['del' => 1], ['id' => $id]);
    }

}