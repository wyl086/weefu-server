<?php

namespace app\api\controller;

use app\common\basics\Api;
use app\api\validate\WithdrawValidate;
use app\common\server\JsonServer;
use app\api\logic\WithdrawLogic;

/**
 * Class Withdraw
 * @package app\api\controller
 */
class Withdraw extends Api
{

    /**
     * @notes 提现申请
     * @return \think\response\Json
     * @throws \think\Exception
     * @author suny
     * @date 2021/7/13 6:16 下午
     */
    public function apply()
    {

        $post = $this->request->post();
        $post['user_id'] = $this->user_id;
        (new WithdrawValidate())->goCheck('apply', $post);
        $id = WithdrawLogic::apply($this->user_id, $post);
        return JsonServer::success('申请成功', ['id' => $id]);
    }

    /**
     * @notes 提现配置
     * @return \think\response\Json
     * @author suny
     * @date 2021/7/13 6:16 下午
     */
    public function config()
    {

        $data = WithdrawLogic::config($this->user_id);
        return JsonServer::success('', $data);
    }

    /**
     * @notes 提现记录
     * @return \think\response\Json
     * @author suny
     * @date 2021/7/13 6:16 下午
     */
    public function records()
    {

        $get = $this->request->get();
        $page = $this->request->get('page_no', $this->page_no);
        $size = $this->request->get('page_size', $this->page_size);
        $res = WithdrawLogic::records($this->user_id, $get, $page, $size);
        return JsonServer::success('', $res);
    }

    /**
     * @notes 提现详情
     * @return \think\response\Json
     * @author suny
     * @date 2021/7/13 6:16 下午
     */
    public function info()
    {

        $get = $this->request->get('');
        (new WithdrawValidate())->goCheck('info', $get);
        $res = WithdrawLogic::info($get['id'], $this->user_id);
        return JsonServer::success('', $res);
    }
}