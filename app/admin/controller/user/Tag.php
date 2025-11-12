<?php
namespace app\admin\controller\user;

use app\admin\logic\user\TagLogic;
use app\common\basics\AdminBase;
use app\common\server\JsonServer;
use think\exception\ValidateException;
use app\admin\validate\user\TagValidate;

class Tag extends AdminBase
{
    public function lists()
    {
        if($this->request->isAjax()) {
            $get = $this->request->get();
            $data = TagLogic::lists($get);
            return JsonServer::success('', $data);
        }
        return view();
    }

    public function add()
    {
        if($this->request->isPost()) {
            $post = $this->request->post();
            try{
                validate(TagValidate::class)->scene('add')->check($post);
            }catch(ValidateException $e) {
                return JsonServer::error($e->getError());
            }
            $result = TagLogic::add($post);
            if($result === true) {
                return JsonServer::success('添加成功');
            }
            return JsonServer::error(TagLogic::getError());
        }
        return view();
    }

    public function edit()
    {
        if($this->request->isPost()) {
            $post = $this->request->post();
            try{
                validate(TagValidate::class)->scene('edit')->check($post);
            }catch(ValidateException $e) {
                return JsonServer::error($e->getError());
            }
            $result = TagLogic::edit($post);
            if($result === true) {
                return JsonServer::success('编辑成功');
            }
            return JsonServer::error(TagLogic::getError());
        }

        $id = $this->request->get('id', '', 'intval');
        $detail = TagLogic::detail($id);
        return view('', [
            'detail' => $detail
        ]);
    }

    public function del()
    {
        $id = $this->request->post('id', '', 'intval');
        $result = TagLogic::del($id);
        if($result === true) {
            return JsonServer::success('删除成功');
        }
        return JsonServer::error(TagLogic::getError());
    }
}