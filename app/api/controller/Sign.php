<?php


namespace app\api\controller;


use app\api\logic\SignLogic;
use app\api\validate\SignValidate;
use app\common\basics\Api;
use app\common\server\JsonServer;

/**
 * 签到
 * Class Sign
 * @package app\api\controller
 */
class Sign extends Api
{


    /**
     * @notes 签到列表
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/2/17 18:29
     */
    public function lists()
    {
        $lists = SignLogic::lists($this->user_id);
        return JsonServer::success('', $lists);
    }


    /**
     * @notes 签到
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/2/17 18:29
     */
    public function sign()
    {
        (new SignValidate())->goCheck(null, ['user_id' => $this->user_id]);
        $result = SignLogic::sign($this->user_id);
        if (false === $result) {
            return JsonServer::error(SignLogic::getError() ?: '签到失败');
        }
        return JsonServer::success('', $result);
    }


    /**
     * @notes 签到规则
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/2/17 14:53
     */
    public function rule()
    {
        return JsonServer::success('', SignLogic::getRule());
    }

}