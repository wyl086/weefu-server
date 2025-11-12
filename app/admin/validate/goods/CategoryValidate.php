<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\admin\validate\goods;

use think\Validate;
use app\common\model\goods\GoodsCategory as GoodsCategoryModel;
use app\common\model\goods\Goods as GoodsModel;
use app\admin\logic\goods\CategoryLogic;

class CategoryValidate extends Validate
{
  protected $rule = [
    'id'    => 'require|checkCategory',
    'name'  => 'require|max: 30|checkName',
    'pid'   => 'require|integer|addPid|editPid',
  ];

  protected $message = [
    'id.require'  => 'id不能为空',
    'name.require'  => '分类名称不能为空',
    'name.max'  => '分类名称不能超过30个字符',
    'pid.require'   => '请选择上级分类',
    'pid.integer'   => '上级id必须为整型',
  ];
  
  /**
   * 添加场景
   */
  public function sceneAdd()
  {
    return $this->remove('id', ['require', 'checkCategory'])
      ->remove('pid','editPid');
  }

  /**
   * 删除场景
   */
  public function sceneDel()
  {
    return $this->only(['id']);
  }

  /**
   * 编辑场景
   */
  public function sceneEdit()
  {
    return $this->remove('id', 'checkCategory')
      ->remove('pid', 'addPid');
  }

  /*
  * 校验分类名称(同一个上级分类下不允许出现相同分类名称)
  */
  protected function checkName($value,$rule,$data){
    $where[] = ['del','=',0];
    // 如果有id代表是编辑校验分类名称
    if(isset($data['id'])){
        $where[] = ['id','<>',$data['id']];
    }
    $where[] = ['name','=',$data['name']];
    $where[] = ['pid','=',$data['pid']];

    $name = GoodsCategoryModel::where($where)->value('name');
    if($name){
        return '分类名称已存在';
    }
    return true;
  }

  /*
  * 添加时，校验上级
  */
  protected function addPid($value, $rule, $data){
    // 顶级分类直接通过
    if($value == 0) return true;

    $goods_category = GoodsCategoryModel::where([
      'id' => $value,
      'del' => 0
    ])->find();

    if($goods_category)  return true;

    return '上级分类不存在，请重新选择';
  }

  /*
  * 验证分类
  */
  protected function checkCategory($value, $rule, $data){
    $children = GoodsCategoryModel::where([
      'del' => 0,
      'pid' => $value
    ])->find();
    if($children) {
      return '该分类下还有子分类不允许删除';
    }
    // 已经有商品绑定了该分类，不能删除
    $goods = GoodsModel::where([
      'del' => 0,
      'third_cate_id' => $value
    ])->find();
    if($goods) {
      return '已有商品绑定此分类不允许删除';
    }

    return true;
  }

  /*
  * 编辑时，验证上级分类
  */
  protected function editPid($value, $rule, $data){
    // 目标上级分类为顶部分类时，直接通过
    if($value == 0 ) return true;
    // 当前分类
    $category = GoodsCategoryModel::where(['id'=>$data['id'],'del'=>0])->find();
    // 目标上级分类
    $partner = GoodsCategoryModel::where(['id'=>$value,'del'=>0])->find();
    // 当前分类下的子分类
    $level = CategoryLogic::getCategoryLevel($category);

    if($category['id'] == $partner['id']) return '上级分类不能是自己';
    // 限制分类不超过3级
    if($level == 3 && $partner) return '该分类下有完整的子分类，不可修改上级分类';
    if($partner['level'] == 2 && $level != 1) return '该分类下有子分类,请先调整该分类下的子分类';
    if($partner['level'] == 3) return '父级分类不能是第三级';

    return true;
  }
}