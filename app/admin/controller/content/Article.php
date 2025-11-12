<?php


namespace app\admin\controller\content;


use app\admin\logic\content\ArticleCategoryLogic;
use app\admin\logic\content\ArticleLogic;
use app\admin\validate\content\ArticleValidate;
use app\common\basics\AdminBase;
use app\common\server\JsonServer;

class Article extends AdminBase
{
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $lists = ArticleLogic::lists($get);
            return JsonServer::success("获取成功", $lists);
        }

        return view('', [
            'category' => ArticleCategoryLogic::getCategory()
        ]);
    }

    public function add()
    {
        if ($this->request->isAjax()) {
            (new ArticleValidate())->goCheck('add');
            $post = $this->request->post();
            $res = ArticleLogic::add($post);
            if ($res === false) {
                $error = ArticleLogic::getError() ?: '新增失败';
                return JsonServer::error($error);
            }
            return JsonServer::success('新增成功');
        }

        return view('', [
            'category' => ArticleCategoryLogic::getCategory()
        ]);
    }

    /***
     * @Notes: 编辑文章
     * @Author: 张无忌
     */
    public function edit()
    {
        if ($this->request->isAjax()) {
            (new ArticleValidate())->goCheck('edit');
            $post = $this->request->post();
            $res = ArticleLogic::edit($post);
            if ($res === false) {
                $error = ArticleLogic::getError() ?: '编辑失败';
                return JsonServer::error($error);
            }
            return JsonServer::success('编辑成功');
        }

        $id = $this->request->get('id');
        return view('', [
            'detail'   => ArticleLogic::detail($id),
            'category' => ArticleCategoryLogic::getCategory()
        ]);
    }

    /**
     * @Notes: 删除文章
     * @Author: 张无忌
     */
    public function del()
    {
        if ($this->request->isAjax()) {
            (new ArticleValidate())->goCheck('id');
            $id = $this->request->post('id');
            $res = ArticleLogic::del($id);
            if ($res === false) {
                $error = ArticleLogic::getError() ?: '删除失败';
                return JsonServer::error($error);
            }
            return JsonServer::success('删除成功');
        }

        return JsonServer::success('异常');
    }

    /**
     * @Notes: 隐藏文章
     * @Author: 张无忌
     */
    public function hide()
    {
        if ($this->request->isAjax()) {
            (new ArticleValidate())->goCheck('id');
            $id = $this->request->post('id');
            $res = ArticleLogic::hide($id);
            if ($res === false) {
                $error = ArticleLogic::getError() ?: '操作失败';
                return JsonServer::error($error);
            }
            return JsonServer::success('操作成功');
        }

        return JsonServer::success('异常');
    }
}