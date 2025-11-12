<?php
namespace app\admin\controller\user;

use app\admin\logic\user\TagLogic;
use app\common\basics\AdminBase;
use app\admin\logic\user\LevelLogic;
use app\admin\logic\user\UserLogic;
use app\common\model\user\UserLevel;
use app\common\server\JsonServer;
use app\common\enum\ClientEnum;
use app\admin\validate\user\UserValidate;
use think\exception\ValidateException;

class User extends  AdminBase
{
    public function lists(){
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            return JsonServer::success('', UserLogic::lists($get));
        }

        return view('', [
            'level_list' => LevelLogic::getLevelList(),
            'tag_list' => TagLogic::getTagList(),
            'client_list' => ClientEnum::getClient(true)
        ]);
    }

    public function setTag(){
        if($this->request->isAjax()){
            $post = $this->request->post();
            try{
                validate(UserValidate::class)->scene('setTag')->check($post);
            }catch(ValidateException $e) {
                return JsonServer::error($e->getMessage());
            }
            $result = UserLogic::setTag($post);
            if($result === true) {
                return JsonServer::success('设置成功');
            }
            return JsonServer::error(UserLogic::getError());
        }
        return view('', [
            'tag_list' => json_encode(TagLogic::getTagList())
        ]);
    }

    public function edit(){
        if($this->request->isAjax()){
            $post = $this->request->post();
            try{
                validate(UserValidate::class)->scene('edit')->check($post);
            }catch(ValidateException $e) {
                return JsonServer::error($e->getMessage());
            }
            $result = UserLogic::edit($post);
            if($result === true) {
                return JsonServer::success('编辑成功');
            }
            return JsonServer::error(UserLogic::getError());
        }

        $id = $this->request->get('id', '', 'intval');
        $detail = UserLogic::getUser($id);

        return view('', [
            'info' => $detail,
            'tag_list' => json_encode(TagLogic::getTagList())
        ]);
    }

    public function info(){
        $id = $this->request->get('id', '', 'intval');
        $detail = UserLogic::getInfo($id);
        return view('', [
            'detail' => $detail
        ]);
    }

    public function adjustAccount(){
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            try{
                validate(UserValidate::class)->scene('adjustAccount')->check($post);
            }catch(ValidateException $e) {
                return JsonServer::error($e->getError());
            }

            $result = UserLogic::adjustAccount($post);
            if($result === true) {
                return JsonServer::success('调整成功');
            }
            return JsonServer::error(UserLogic::getError());

        }
        $id = $this->request->get('id', '', 'intval');
        return view('', [
            'info' => UserLogic::getUser($id)
        ]);
    }

    public function adjustLevel(){
        if ($this->request->isPost()) {
            $params = $this->request->post();
            $result = UserLogic::adjustLevel($params);
            if ($result) {
                return JsonServer::success('调整成功');
            }
            return JsonServer::error(UserLogic::getError());
        }

        $id = $this->request->get('id/d');
        $levels = UserLevel::where('del', 0)->order('growth_value', 'asc')->column('id,name', 'id');
        $userLevel = \app\common\model\user\User::where('id', $id)->value('level');
        $userLevelName = isset($levels[$userLevel]) ? $levels[$userLevel]['name'] : '无等级';
        return view('', [
            'levels' => $levels,
            'user_level_name' => $userLevelName,
            'user_id' => $id
        ]);
    }

    public function adjustFirstLeader()
    {
        if($this->request->isPost()) {
            $params = $this->request->post();
            $result = UserLogic::adjustFirstLeader($params);
            if ($result) {
                return JsonServer::success('调整成功');
            }
            return JsonServer::error(UserLogic::getError());
        }

        $id = $this->request->get('id/d');
        $user =  \app\common\model\user\User::field('id,sn,nickname,first_leader')->findOrEmpty($id)->toArray();
        $firstLeader = \app\common\model\user\User::getUserInfo($user['first_leader']);
        return view('', [
            'user_id' => $id,
            'user' => $user,
            'first_leader' => $firstLeader
        ]);
    }

    public function userLists()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();
            $lists = UserLogic::userLists($params);
            return JsonServer::success('', $lists);
        }
        return view();
    }

    /**
     * @notes 推荐下级
     * @return \think\response\View
     * @author Tab
     * @date 2021/9/8 20:40
     */
    public function fans()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();
            $result = UserLogic::fans($params);
            return JsonServer::success('', $result);
        }

        $id = $this->request->get('id/d');
        return view('', ['id' => $id]);
    }
}