<?php


namespace app\admin\controller\content;


use app\admin\logic\content\HelpCategoryLogic;
use app\admin\logic\content\HelpLogic;
use app\admin\validate\content\HelpValidate;
use app\common\basics\AdminBase;
use app\common\server\JsonServer;

class Help extends AdminBase
{
    /**
     * @NOTES: 帮助分类列表
     * @author: 张无忌
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $lists = HelpLogic::lists($get);
            return JsonServer::success("获取成功", $lists);
        }

        return view('', [
            'category' => HelpCategoryLogic::getCategory()
        ]);
    }

    /**
     * @NOTES: 添加帮助类
     * @author: 张无忌
     */
    public function add()
    {
        if ($this->request->isAjax()) {
            (new HelpValidate())->goCheck('add');
            $post = $this->request->post();
            $res = HelpLogic::add($post);
            if ($res === false) {
                $error = HelpLogic::getError() ?: '新增失败';
                return JsonServer::error($error);
            }
            return JsonServer::success('新增成功');
        }


        return view('', [
            'category' => HelpCategoryLogic::getCategory()
        ]);
    }

    /**
     * @NOTES: 编辑帮助分类
     * @author: 张无忌
     */
    public function edit()
    {
        if ($this->request->isAjax()) {
            (new HelpValidate())->goCheck('edit');
            $post = $this->request->post();
            $res = HelpLogic::edit($post);
            if ($res === false) {
                $error = HelpLogic::getError() ?: '编辑失败';
                return JsonServer::error($error);
            }
            return JsonServer::success('编辑成功');
        }

        $id = $this->request->get('id');
        return view('', [
            'detail'   => HelpLogic::detail($id),
            'category' => HelpCategoryLogic::getCategory()
        ]);
    }

    /**
     * @NOTES: 删除帮助分类
     * @author: 张无忌
     */
    public function del()
    {
        if ($this->request->isAjax()) {
            (new HelpValidate())->goCheck('id');
            $id = $this->request->post('id');
            $res = HelpLogic::del($id);
            if ($res === false) {
                $error = HelpLogic::getError() ?: '删除失败';
                return JsonServer::error($error);
            }
            return JsonServer::success('删除成功');
        }

        return JsonServer::error('异常');
    }

    /**
     * @Notes: 隐藏帮助分类
     * @Author: 张无忌
     */
    public function hide()
    {
        if ($this->request->isAjax()) {
            (new HelpValidate())->goCheck('id');
            $id = $this->request->post('id');
            $res = HelpLogic::hide($id);
            if ($res === false) {
                $error = HelpLogic::getError() ?: '操作失败';
                return JsonServer::error($error);
            }
            return JsonServer::success('操作成功');
        }

        return JsonServer::success('异常');
    }
}