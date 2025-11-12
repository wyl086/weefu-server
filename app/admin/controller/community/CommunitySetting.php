<?php


namespace app\admin\controller\community;


use app\admin\logic\community\CommunitySettingLogic;
use app\common\basics\AdminBase;
use app\common\server\JsonServer;

/**
 * 种草社区设置
 * Class CommunityTopic
 * @package app\admin\controller\content
 */
class CommunitySetting extends AdminBase
{


    /**
     * @notes 社区配置
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/4/28 16:16
     */
    public function setting()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            CommunitySettingLogic::setConfig($post);
            return JsonServer::success('操作成功');
        }
        $config = CommunitySettingLogic::getConfig();
        return view('', ['config' => $config]);
    }

}