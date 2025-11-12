<?php
namespace app\admin\controller\distribution;

use app\admin\logic\distribution\DistributionSettingLogic;
use app\common\basics\AdminBase;
use app\common\server\JsonServer;

class DistributionSetting extends AdminBase
{
    /**
     * @notes 基础设置
     * @return \think\response\View
     * @author Tab
     * @date 2021/9/1 9:14
     */
    public function index()
    {
        $config = DistributionSettingLogic::getConfig();
        return view('', ['config' => $config]);
    }

    /**
     * @notes 分销设置
     * @return \think\response\Json
     * @author Tab
     * @date 2021/9/1 9:15
     */
    public function set()
    {
        $params = $this->request->post();
        $result = DistributionSettingLogic::set($params);
        if ($result) {
            return JsonServer::success('设置成功');
        }
        return JsonServer::error(DistributionSettingLogic::getError());
    }

    /**
     * @notes 结算设置
     * @return \think\response\View
     * @author Tab
     * @date 2021/9/1 9:17
     */
    public function settlement()
    {
        $config = DistributionSettingLogic::getConfig();
        return view('', ['config' => $config]);
    }
}