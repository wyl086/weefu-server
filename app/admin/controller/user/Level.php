<?php
namespace app\admin\controller\user;


use app\common\basics\AdminBase;
use app\common\server\ConfigServer;
use app\common\server\JsonServer;
use think\exception\ValidateException;
use app\admin\validate\user\LevelValidate;
use app\admin\logic\user\LevelLogic;

class Level extends AdminBase
{
    public function lists()
    {
        if($this->request->isAjax()){
            $get = $this->request->get();
            $lists = LevelLogic::lists($get);
            return JsonServer::success('', $lists);
        }
        return view();
    }

    public function add()
    {
        if($this->request->isAjax()) {
            try{
                $post = $this->request->post();
                validate(LevelValidate::class)->scene('add')->check($post);
            }catch(ValidateException $e) {
                return JsonServer::error($e->getError());
            }
            $result = LevelLogic::add($post);
            if($result === true) {
                return JsonServer::success('添加成功');
            }
            return JsonServer::error(LevelLogic::getError());
        }
        return view();
    }

    public function edit(){
        if($this->request->isAjax()){
            try{
                $post = $this->request->post();
                validate(LevelValidate::class)->scene('edit')->check($post);
            }catch(ValidateException $e) {
                return JsonServer::error($e->getError());
            }
            $result = LevelLogic::edit($post);
            if($result === true) {
                return JsonServer::success('编辑成功');
            }
            return JsonServer::error(LevelLogic::getError());
        }

        $id = $this->request->get('id', '', 'intval');
        $detail = LevelLogic::getUserLevel($id);
        return view('', [
            'detail' => $detail
        ]);
    }

    public function del()
    {
        $id = $this->request->post('id', '',  'intval');
        $result = LevelLogic::del($id);
        if($result === true) {
            return JsonServer::success('删除成功');
        }
        return JsonServer::error(LevelLogic::getError());
    }

    public function set()
    {
        if($this->request->isAjax()) {
            $post = $this->request->post();
            ConfigServer::set('user_level', 'intro', $post['intro']);
            return JsonServer::success('设置成功');
        }
        $intro = ConfigServer::get('user_level', 'intro');
        $intro_default = config('default.user_level.intro');

        return view('', [
            'intro' => $intro,
            'intro_default' => $intro_default
        ]);
    }
}