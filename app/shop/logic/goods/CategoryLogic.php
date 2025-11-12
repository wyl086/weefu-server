<?php
namespace app\shop\logic\goods;

use app\common\model\shop\ShopGoodsCategory as ShopGoodsCategoryModel;

class CategoryLogic
{
  public static function lists($get)
  {
    $lists = ShopGoodsCategoryModel::where(['del' => 0, 'shop_id' => $get['shop_id']])
      ->page($get['page'], $get['limit'])
      ->order('sort', 'asc')
      ->select();
    $count = ShopGoodsCategoryModel::where(['del' => 0, 'shop_id' => $get['shop_id']])->count();
    if($lists) {
      $lists = $lists->toArray();
    }
    $data = [
      'count' => $count,
      'lists' => $lists
    ];
    return $data;
  }

  /**
   * 添加
   */
  public static function add($post)
  {
    $post['create_time'] = time();
    return ShopGoodsCategoryModel::create($post);
  }

  /**
   * 获取商品分类信息
   */
  public static function getCategory($id)
  {
    return ShopGoodsCategoryModel::find($id);
  }

  /**
   * 编辑商品分类
   */
  public static function edit($post)
  {
    $post['update_time'] = time();
    return ShopGoodsCategoryModel::update($post);
  }

  /**
   * 删除商品分类
   */
  public static function del($post)
  {
    $post['update_time'] = time();
    $post['del'] = 1;
    return ShopGoodsCategoryModel::update($post);
  }

  /**
   * 修改是否显示状态
   */
  public static function switchStatus($post)
  {
    $post['is_show'] = $post['status'];
    unset($post['status']);
    return ShopGoodsCategoryModel::update($post);
  }

  /**
   * 店铺商品分类
   */
  public static function listAll($shop_id)
  {
    $lists = ShopGoodsCategoryModel::field('id,name')->where([
        'del'=>0,
        'shop_id' => $shop_id
    ])->order('id', 'asc')->select();
    return empty($lists) ? [] : $lists->toArray();
  }
}