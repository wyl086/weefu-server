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

use app\common\model\goods\GoodsCategory as GoodsCategoryModel;
use app\common\server\UrlServer;

/**
 * 平台商品分类 逻辑层
 * Class CategoryLogic
 * @package app\admin\logic\goods
 */
class CategoryLogic
{
  /**
   *  获取分类列表(所有)
   */
  public static function lists()
  {
    $lists = GoodsCategoryModel::field('id,name,pid,is_show,level,image, bg_image, sort')
      ->where('del', 0)
      ->order('sort', 'asc')
      ->select()
      ->toArray();

    foreach ($lists as $k => $item){
      $lists[$k]['image'] = $lists[$k]['image'] ? UrlServer::getFileUrl($item['image']) : '';
    }
    // 线性结构转树形结构(顶级分类树)
    $lists = linear_to_tree($lists);
    return $lists;
  }

  /**
   *  获取分类列表(二级)
   */
  public static function categoryTwoTree()
  {
    $cateogry_list = GoodsCategoryModel::with('sons')
      ->field('id,name,pid,level')
      ->where(['del' => 0, 'level' => 1])
      ->select()
      ->toArray();

    return self::categoryToSelect($cateogry_list);
  }

  /**
   * Desc：将树形结构数组输出
   * @param $items  array 要输出的数组
   * @param $select_id int 已选中项
   * @return string
   */
  public static function categoryToSelect($lists, $select_id = 0)
  {
    $tree = [];
    foreach ($lists as $val) {
      $tree[$val['id']]['level'] = $val['level'];
      $tree[$val['id']]['name'] = '|----' . $val['name'];
      if ($val['sons']) {
        foreach ($val['sons'] as $val_sons) {
          $tree[$val_sons['id']]['level'] = $val_sons['level'];
          $tree[$val_sons['id']]['name'] = '|--------' . $val_sons['name'];
        }
      }
    }
    return $tree;
  }


  /**
   * 添加分类
   */
  public static function add($post)
  {
    $level = 0;
    if ($post['pid']) {
      $level = GoodsCategoryModel::where(['id' => $post['pid']], ['del' => 0])->value('level');
    }

    $data = [
      'name'              => trim($post['name']),
      'pid'               => $post['pid'],
      'sort'              => $post['sort'],
      'is_show'           => $post['is_show'],
      'image'             => isset($post['image']) ? clearDomain($post['image']) : '',
      'bg_image'          => isset($post['bg_image']) ? clearDomain($post['bg_image']) : '',
      'level'             => $level + 1,
      'remark'            => $post['remark'],
      'create_time'       => time(),
      'update_time'       => time(),
    ];
    return GoodsCategoryModel::create($data);
  }

  /**
   * 删除分类
   */
  public static function del($post)
  {
    return GoodsCategoryModel::update([
      'id' => $post['id'],
      'del' => 1,
      'update_time' => time(),
    ]);
  }


  /**
   * 分类详情
   */
  public static function getCategory($id)
  {
    $detail = GoodsCategoryModel::where([
      'del' => 0,
      'id' => $id
    ])->find();
    $detail['image'] = UrlServer::getFileUrl($detail['image']);
    $detail['bg_image'] =  $detail['bg_image'] ? UrlServer::getFileUrl($detail['bg_image']) : '';
    return $detail;
  }

  /**
   * 获取叶子分类的级数
   */
  public static function getCategoryLevel($category)
  {
    $level = 1;
    $two_ids = GoodsCategoryModel::where(['pid' => $category['id'], 'del' => 0])->column('id');
    if ($two_ids) {
      $level = 2;
      $three_id = GoodsCategoryModel::where([
        ['pid', 'in', $two_ids],
        ['del', '=', 0]
        ])->column('id');
      if ($three_id) $level = 3;
    }
    return $level;
  }

  /**
   *  编辑
   */
  public static function edit($post)
  {
    $level = 0;
    if ($post['pid']) {
      $level = GoodsCategoryModel::where(['id' => $post['pid']], ['del' => 0])->value('level');
    }
    $data = [
        'name'              => $post['name'],
        'sort'              => $post['sort'],
        'is_show'           => $post['is_show'],
        'image'             => isset($post['image']) ? clearDomain($post['image']) : '',
        'bg_image'          => isset($post['bg_image']) ? clearDomain($post['bg_image']) : '',
        'level'             => $level+1,
        'pid'               => $post['pid'],
        'remark'            => $post['remark'],
        'update_time'       => time(),
    ];
    return GoodsCategoryModel::where(['id'=>$post['id']])->update($data);
  }

  // 修改分类显示状态
  public static function switchStatus($post)
  {
    $update_data = [
      'is_show'       => $post['status'],
      'update_time'   => time(),
    ];
    return GoodsCategoryModel::where(['del' =>0,'id' =>$post['id']])->update($update_data);
  }

  /**
   * 平台商品分类（三级）
   */
  public static function categoryTreeeTree()
  {
    $lists = GoodsCategoryModel::where(['del' => 0])->column('id,name,pid,level', 'id');
    return self::cateToTree($lists, 0, '|-----', 1);
  }

  /**
   * 转树形结构
   */
  public static function cateToTree($lists, $pid = 0, $html = '|-----', $level = 1, $clear = true)
  {
    static $tree = [];
    if ($clear) $tree = [];
    foreach ($lists as $k => $v) {
        if ($v['pid'] == $pid) {
            $v['html'] = str_repeat($html, $level);
            $tree[] = $v;
            unset($lists[$k]);
            self::cateToTree($lists, $v['id'], $html, $level + 1, false);
        }
    }
    return $tree;
  }

  /**
   * 获取所有分类树形结构
   */
  public static function getAllTree()
  {
    $lists = GoodsCategoryModel::field(['name', 'id', 'pid', 'level'])
        ->where(['del' => 0])
        ->order(['sort' => 'desc'])
        ->select();
    return $lists;
  }
}
