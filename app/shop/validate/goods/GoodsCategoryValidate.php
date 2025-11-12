<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | gitee下载：https://gitee.com/multshop_gitee
// | github下载：https://github.com/multshop-github
// | 访问官网：https://www.multshop.cn
// | 访问社区：https://home.multshop.cn
// | 访问手册：http://doc.multshop.cn
// | 微信公众号：multshop技术社区
// | multshop系列产品在gitee、github等公开渠道开源版本可免费商用，未经许可不能去除前后端官方版权标识
// |  multshop系列产品收费版本务必购买商业授权，购买去版权授权后，方可去除前后端官方版权标识
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\shop\validate\goods;

use think\Validate;
use app\common\model\shop\ShopGoodsCategory as ShopGoodsCategoryModel;
use app\common\model\goods\Goods as GoodsModel;

class GoodsCategoryValidate extends Validate
{
  protected $rule = [
    'id'    => 'require|checkCategory',
    'name'  => 'require|max: 30|checkName',
    'sort'  => 'integer|egt:0',
  ];

  protected $message = [
    'id.require'  => 'id不能为空',
    'name.require'  => '分类名称不能为空',
    'name.max'  => '分类名称不能超过30个字符',
    'pid.require'   => '请选择上级分类',
    'pid.integer'   => '上级id必须为整型',
    'sort.integer'   => '排序值须为整数',
    'sort.egt'   => '排序值须大于或等于0',
  ];
  
  /**
   * 添加场景
   */
  public function sceneAdd()
  {
    return $this->remove('id', ['require', 'checkCategory']);
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
    return $this->remove('id', 'checkCategory');
  }

  /*
  * 校验分类名称
  */
  protected function checkName($value,$rule,$data){
    $where[] = ['del','=',0];
    $where[] = ['shop_id', '=', $data['shop_id']];
    // 如果有id代表是编辑校验分类名称
    if(isset($data['id'])){
        $where[] = ['id','<>',$data['id']];
    }
    $where[] = ['name','=',$data['name']];

    $name = ShopGoodsCategoryModel::where($where)->value('name');
    if($name){
        return '分类名称已存在';
    }
    return true;
  }

  /*
  * 验证分类
  */
  protected function checkCategory($value, $rule, $data){
    // 已经有商品绑定了该分类，不能删除
    $goods = GoodsModel::where([
      'del' => 0,
      'shop_cate_id' => $value
    ])->find();
    if($goods) {
      return '已有商品绑定此分类不允许删除';
    }

    return true;
  }

}