<?php
namespace app\admin\controller\distribution;

use app\admin\logic\distribution\DistributionLevelLogic;
use app\admin\logic\distribution\DistributionMemberLogic;
use app\common\basics\AdminBase;
use app\common\server\JsonServer;

/**
 * 分销会员
 * Class DistributionMember
 * @package app\admin\controller\distribution
 */
class DistributionMember extends AdminBase
{
    /**
     * @notes 分销会员列表
     * @return \think\response\View
     * @author Tab
     * @date 2021/9/2 18:26
     */
    public function index()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();
            $result = DistributionMemberLogic::lists($params);
            return JsonServer::success('', $result);
        }
        $levels = DistributionLevelLogic::getLevels();
        return view('', ['levels' => $levels]);
    }

    /**
     * @notes 开通分销会员
     * @return \think\response\View
     * @author Tab
     * @date 2021/9/2 19:32
     */
    public function open()
    {
        if($this->request->isPost()) {
            $params = $this->request->post();
            $result = DistributionMemberLogic::open($params);
            if($result) {
                return JsonServer::success('开通成功');
            }
            return JsonServer::error(DistributionMemberLogic::getError());
        }
        $levels = DistributionLevelLogic::getLevels();
        return view('', ['levels' => $levels]);
    }

    /**
     * @notes 用户列表
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/9/3 11:50
     */
    public function userLists()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();
            $lists = DistributionMemberLogic::getUserLists($params);
            return JsonServer::success('', $lists);
        }
        return view();
    }

    /**
     * @notes 分销会员等级调整
     * @return \think\response\Json|\think\response\View
     * @author Tab
     * @date 2021/9/3 14:10
     */
    public function adjust()
    {
        if($this->request->isPost()) {
            $params = $this->request->post();
            $result = DistributionMemberLogic::adjust($params);
            if($result) {
                return JsonServer::success('调整成功');
            }
            return JsonServer::error(DistributionMemberLogic::getError());
        }
        $params = $this->request->get();
        $user = DistributionMemberLogic::getUser($params);
        $levels = DistributionLevelLogic::getLevels();
        return view('', [
            'user' => $user,
            'levels' => $levels
        ]);
    }

    /**
     * @notes 冻结资格/恢复资格
     * @return \think\response\Json
     * @author Tab
     * @date 2021/9/3 14:20
     */
    public function isFreeze()
    {
        $params = $this->request->post();
        $result = DistributionMemberLogic::isFreeze($params);
        if($result) {
            return JsonServer::success('操作成功');
        }
        return JsonServer::error(DistributionMemberLogic::getError());
    }
}