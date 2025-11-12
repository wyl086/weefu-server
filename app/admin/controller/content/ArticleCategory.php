<?php


namespace app\admin\controller\content;


use app\admin\logic\content\ArticleCategoryLogic;
use app\admin\validate\content\ArticleCategoryValidate;
use app\common\basics\AdminBase;
use app\common\server\JsonServer;

class ArticleCategory extends AdminBase
{
    /**
     * @NOTES: 文章分类列表
     * @author: 张无忌
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $lists = ArticleCategoryLogic::lists($get);
            return JsonServer::success("获取成功", $lists);
        }

        return view();
    }

    /**
     * @NOTES: 添加文章分类
     * @author: 张无忌
     */
    public function add()
    {
        if ($this->request->isAjax()) {
            (new ArticleCategoryValidate())->goCheck('add');
            $post = $this->request->post();
            $res = ArticleCategoryLogic::add($post);
            if ($res === false) {
                $error = ArticleCategoryLogic::getError() ?: '新增失败';
                return JsonServer::error($error);
            }
            return JsonServer::success('新增成功');
        }


        return view();
    }

    /**
     * @NOTES: 编辑文章分类
     * @author: 张无忌
     */
    public function edit()
    {
        if ($this->request->isAjax()) {
            (new ArticleCategoryValidate())->goCheck('edit');
            $post = $this->request->post();
            $res = ArticleCategoryLogic::edit($post);
            if ($res === false) {
                $error = ArticleCategoryLogic::getError() ?: '编辑失败';
                return JsonServer::error($error);
            }
            return JsonServer::success('编辑成功');
        }

        $id = $this->request->get('id');
        return view('', [
            'detail' => ArticleCategoryLogic::detail($id)
        ]);
    }

    /**
     * @NOTES: 删除文章分类
     * @author: 张无忌
     */
    public function del()
    {
        if ($this->request->isAjax()) {
            (new ArticleCategoryValidate())->goCheck('id');
            $id = $this->request->post('id');
            $res = ArticleCategoryLogic::del($id);
            if ($res === false) {
                $error = ArticleCategoryLogic::getError() ?: '删除失败';
                return JsonServer::error($error);
            }
            return JsonServer::success('删除成功');
        }

        return JsonServer::error('异常');
    }

    /**
     * @Notes: 隐藏分类
     * @Author: 张无忌
     */
    public function hide()
    {
        if ($this->request->isAjax()) {
            (new ArticleCategoryValidate())->goCheck('id');
            $id = $this->request->post('id');
            $res = ArticleCategoryLogic::hide($id);
            if ($res === false) {
                $error = ArticleCategoryLogic::getError() ?: '操作失败';
                return JsonServer::error($error);
            }
            return JsonServer::success('操作成功');
        }

        return JsonServer::success('异常');
    }
}