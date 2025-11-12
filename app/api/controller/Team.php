<?php


namespace app\api\controller;


use app\api\logic\TeamLogic;
use app\api\validate\TeamValidate;
use app\common\basics\Api;
use app\common\model\team\TeamFound;
use app\common\model\team\TeamJoin;
use app\common\server\JsonServer;
use think\exception\ValidateException;

class Team extends Api
{
    public $like_not_need_login = ['activity'];

    /**
     * @Notes: 拼团活动
     * @Author: 张无忌
     */
    public function activity()
    {
        $get = $this->request->get();
        $lists = TeamLogic::activity($get);
        if ($lists === false) {
            $message = TeamLogic::getError() ?: '获取失败';
            return JsonServer::error($message);
        }
        return JsonServer::success('获取成功', $lists);
    }

    /**
     * @Notes: 开团
     * @Author: 张无忌
     */
    public function kaituan()
    {
        try{
            validate(TeamValidate::class)->scene('check')->check($this->request->post());
        }catch(ValidateException $e) {
            return JsonServer::error($e->getError(), [], 301);
        }
        $post = $this->request->post();
        $info = TeamLogic::kaituanInfo($post, $this->user_id);
        if ($info === false) {
            $message = TeamLogic::getError() ?: '获取团信息失败';
            return JsonServer::error($message);
        }
        if ($post['action'] == 'info') {
            return JsonServer::success('OK', $info);
        }

        $res = TeamLogic::kaituan($info, $this->user_info);
        if ($res === false) {
            $message = TeamLogic::getError() ?: '发起失败';
            return JsonServer::error($message);
        }
        return JsonServer::success('拼团成功', $res);
    }

    /**
     * @Notes: 拼团记录
     * @Author: 张无忌
     */
    public function record()
    {
        $get = $this->request->get();
        $lists = TeamLogic::record($get, $this->user_id);
        if ($lists === false) {
            $message = TeamLogic::getError() ?: '获取失败';
            return JsonServer::error($message);
        }
        return JsonServer::success('拼团成功', $lists);
    }

    /**
     * @Notes: 验证团信息
     * @Author: 张无忌
     */
    public function check()
    {
        (new TeamValidate())->goCheck('check');

        $post = $this->request->post();
        $res = TeamLogic::check($post, $this->user_id);
        if ($res === false) {
            $message = TeamLogic::getError() ?: '验证失败';
            return JsonServer::error($message);
        }
        return JsonServer::success('验证通过');
    }
}