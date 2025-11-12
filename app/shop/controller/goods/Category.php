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

namespace app\shop\controller\goods;

use app\common\basics\ShopBase;
use app\common\server\JsonServer;
use think\exception\ValidateException;
use think\facade\View;
use app\shop\logic\goods\CategoryLogic;
use app\shop\validate\goods\GoodsCategoryValidate;

/**
 * 店铺商品分类
 * Class Category
 */
class Category extends ShopBase
{
  /**
   * 列表
   */
  public function lists(){
    if($this->request->isAjax()) {
      $get = $this->request->get();
      $get['shop_id'] = $this->shop_id;
      $data = CategoryLogic::lists($get);
      return JsonServer::success('获取列表成功', $data);
    }
    return view();
  }

  /**
   * 添加
   */
  public function add(){
    if($this->request->isAjax()) {
      $post = $this->request->post();
      $post['del'] = 0;
      $post['shop_id'] = $this->shop_id;
      try {
        validate(GoodsCategoryValidate::class)->scene('add')->check($post);
      } catch (ValidateException $e) {
          return JsonServer::error($e->getError());
      }
      $res = CategoryLogic::add($post);
      if($res) {
        return JsonServer::success('分类添加成功');
      }else{
        return JsonServer::error('分类添加失败');
      }
    }

    return view();
  }

  /**
   * 删除
   */
  public function del(){
    $post = $this->request->post();
    try {
      validate(GoodsCategoryValidate::class)->scene('del')->check($post);
    } catch (ValidateException $e) {
        return JsonServer::error($e->getError());
    }
    $res = CategoryLogic::del($post);
    if($res) {
      return JsonServer::success('删除分类成功');
    }else{
      return JsonServer::error('删除分类失败');
    }
  }


  /**
   * 编辑
   */
  public function edit(){
    if ($this->request->isAjax()) {
      $post = $this->request->post();
      $post['del'] = 0;
      $post['shop_id'] = $this->shop_id;
      try {
        validate(GoodsCategoryValidate::class)->scene('edit')->check($post);
      } catch (ValidateException $e) {
          return JsonServer::error($e->getError());
      }
      $res = CategoryLogic::edit($post);
      if($res) {
        return JsonServer::success('编辑分类成功');
      }else{
        return JsonServer::error('编辑分类失败');
      }
    }

    $id = $this->request->get('id');
    $detail = CategoryLogic::getCategory($id);
    return view('edit', ['detail' => $detail]);
  }

  /**
   * 修改显示状态
   */
  public function switchStatus(){
    $post = $this->request->post();
    $res = CategoryLogic::switchStatus($post);
    if($res) {
      return JsonServer::success('修改成功');
    }else{
      return JsonServer::error('修改失败');
    }
  }
}