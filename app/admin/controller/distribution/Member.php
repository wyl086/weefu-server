<?php
namespace app\admin\controller\distribution;

use app\common\basics\AdminBase;
use app\common\model\distribution\DistributionMemberApply;
use app\common\server\JsonServer;
use app\admin\logic\distribution\MemberLogic;
use app\admin\validate\distribution\MemberValidate;
use think\exception\ValidateException;

class Member extends AdminBase
{
    public function index()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $type = $get['type'] ?? 'member';
            if ($type == 'member') {
                return JsonServer::success('获取成功', MemberLogic::memberLists($get));
            }
            return JsonServer::success('获取成功', MemberLogic::auditLists($get));
        }

        return view('index', ['status' => DistributionMemberApply::getApplyStatus(true)]);
    }

    public function addMember()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            try{
                validate(MemberValidate::class)->scene('add')->check($post);
            }catch(ValidateException $e) {
                return JsonServer::error($e->getError());
            }

            $result = MemberLogic::addMember($post);
            if($result === true) {
               return JsonServer::success('添加成功');
            }
            return JsonServer::error($result);
        }
        return view();
    }

    public function info()
    {
        $get = $this->request->get();
        $info = MemberLogic::getMemberInfo($get);
        return view('info', ['detail'=>$info]);
    }

    public function fans()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            return JsonServer::success('', MemberLogic::getFansLists($get));
        }

        $user_id = $this->request->get('id');
        return view('', ['user_id'=>$user_id]);
    }

    public function earningsDetail()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            return JsonServer::success('', MemberLogic::getEarningsDetail($get));
        }

        $user_id = $this->request->get('id');
        return view('', ['user_id'=>$user_id]);
    }

    public function updateLeader()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            try{
                validate(MemberValidate::class)->scene('updateLeader')->check($post);
            }catch(ValidateException $e) {
                return JsonServer::error($e->getError());
            }
            $result = MemberLogic::updateRelation($post);
            if ($result === true){
                return JsonServer::success('操作成功');
            }
            return JsonServer::error($result);
        }

        $user_id = $this->request->get('id');
        return view('',[
            'first_leader' => MemberLogic::getLeaderInfo($user_id),
            'user_id' => $user_id
        ]);
    }

    public function freeze()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            try{
                validate(MemberValidate::class)->scene('freeze')->check($post);
            }catch(ValidateException $e) {
                return JsonServer::error($e->getError());
            }
            $result = MemberLogic::freeze($post);
            if($result === true) {
                return JsonServer::success('操作成功');
            }
            return JsonServer::error('操作失败');
        }
    }

    // 删除分销资格
    public function del()
    {
        if($this->request->isPost()) {
            $post = $this->request->post();
            $result = MemberLogic::del($post);
            if($result === true) {
                return JsonServer::success('操作成功');
            }
            return JsonServer::error('操作失败');
        }
    }


    /**
     * 审核分销会员
     */
    public function audit()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            try{
                validate(MemberValidate::class)->scene('audit')->check($post);
            }catch(ValidateException $e) {
                return JsonServer::error($e->getError());
            }
            if ($post['type'] == 'pass') {
                $res = MemberLogic::auditPass($post);
            } else {
                $res = MemberLogic::auditRefuse($post);
            }

            if ($res !== true) {
                return JsonServer::error('操作失败');
            }
            return JsonServer::success('操作成功');
        }
    }

}