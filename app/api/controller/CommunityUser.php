<?php


namespace app\api\controller;


use app\api\logic\CommunityUserLogic;
use app\common\basics\Api;
use app\common\server\JsonServer;

/**
 * 种草社区用户相关
 * Class CommunityArticle
 * @package app\api\controller
 */
class CommunityUser extends Api
{

    /**
     * @notes 个人中心
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/5/5 18:15
     */
    public function center()
    {
        $get = $this->request->get();
        $result = CommunityUserLogic::getCenterInfo($this->user_id, $get);
        return JsonServer::success('', $result);
    }



    /**
     * @notes 获取设置
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/5/5 18:28
     */
    public function getSetting()
    {
        $result = CommunityUserLogic::getSetting($this->user_id);
        return JsonServer::success('', $result);
    }



    /**
     * @notes 设置签名，背景图
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/5/5 18:28
     */
    public function setSetting()
    {
        $post = $this->request->post();
        $result = CommunityUserLogic::setSetting($this->user_id, $post);
        if (false === $result) {
            return JsonServer::error(CommunityUserLogic::getError() ?: '设置失败');
        }
        return JsonServer::success('操作成功');
    }


}