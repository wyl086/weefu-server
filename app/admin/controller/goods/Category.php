<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\admin\controller\goods;

use app\common\basics\AdminBase;
use app\admin\logic\goods\CategoryLogic;
use app\admin\validate\goods\CategoryValidate;
use think\exception\ValidateException;
use app\common\server\JsonServer;
use think\facade\View;

/**
 * 平台商品分类
 * Class Category
 * @package app\admin\controller\goods
 */
class Category extends AdminBase
{
    /**
     * 列表
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $category_tree = CategoryLogic::lists();
            // reqData方式渲染
            $treeTableData = [
                'code' => 0,
                'msg' => '分类列表',
                'data' => json_encode($category_tree)
            ];
            return json($treeTableData);
        }
        return view();
    }

    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $post['del'] = 0;
            try {
                validate(CategoryValidate::class)->scene('add')->check($post);
            } catch (ValidateException $e) {
                return JsonServer::error($e->getError());
            }
            $res = CategoryLogic::add($post);
            if ($res) {
                return JsonServer::success('分类添加成功');
            } else {
                return JsonServer::error('分类添加失败');
            }
        }

        $category_list = CategoryLogic::categoryTwoTree();
        return view('add', ['category_list' => $category_list]);
    }

    /**
     * 删除
     */
    public function del()
    {
        $post = $this->request->post();
        try {
            validate(CategoryValidate::class)->scene('del')->check($post);
        } catch (ValidateException $e) {
            return JsonServer::error($e->getError());
        }
        $res = CategoryLogic::del($post);
        if ($res) {
            return JsonServer::success('删除分类成功');
        } else {
            return JsonServer::error('删除分类失败');
        }
    }


    /**
     * 编辑
     */
    public function edit()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $post['del'] = 0;
            try {
                validate(CategoryValidate::class)->scene('edit')->check($post);
            } catch (ValidateException $e) {
                return JsonServer::error($e->getError());
            }
            $res = CategoryLogic::edit($post);
            if ($res) {
                return JsonServer::success('编辑分类成功');
            } else {
                return JsonServer::error('编辑分类失败');
            }
        }

        $id = $this->request->get('id');
        $detail = CategoryLogic::getCategory($id);
        $category_list = CategoryLogic::categoryTwoTree();
        return view('edit', [
            'detail' => $detail,
            'category_list' => $category_list
        ]);
    }

    /**
     * 修改显示状态
     */
    public function switchStatus()
    {
        $post = $this->request->post();
        $res = CategoryLogic::switchStatus($post);
        if ($res) {
            return JsonServer::success('修改成功');
        } else {
            return JsonServer::error('修改失败');
        }
    }
}