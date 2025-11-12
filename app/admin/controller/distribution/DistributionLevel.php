<?php
namespace app\admin\controller\distribution;

use app\admin\logic\distribution\DistributionLevelLogic;
use app\admin\validate\distribution\DistributionLevelValidate;
use app\common\basics\AdminBase;
use app\common\server\JsonServer;

class DistributionLevel extends AdminBase
{
    /**
     * @notes 分销等级列表
     * @return \think\response\View
     * @author Tab
     * @date 2021/9/1 11:01
     */
    public function index()
    {
        if ($this->request->isPost()) {
            $result = DistributionLevelLogic::index();
            return JsonServer::success('', $result);
        }
        return view();
    }

    /**
     * @notes 添加分销等级
     * @return \think\response\View
     * @author Tab
     * @date 2021/9/1 12:02
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = (new DistributionLevelValidate())->goCheck('add');
            $result = DistributionLevelLogic::add($params);
            if($result) {
                return JsonServer::success('添加成功');
            }
            return JsonServer::error(DistributionLevelLogic::getError());
        }
        // 显示添加页面
        return view();
    }

    /**
     * @notes 编辑分销等级
     * @return \think\response\View
     * @author Tab
     * @date 2021/9/1 15:39
     */
    public function edit()
    {
        if ($this->request->isPost()) {
            $params = (new DistributionLevelValidate())->goCheck('edit');
            $result = DistributionLevelLogic::edit($params);
            if($result) {
                return JsonServer::success('编辑成功');
            }
            return JsonServer::error(DistributionLevelLogic::getError());
        }

        $params = $this->request->get();
        $detail = DistributionLevelLogic::detail($params);
        $template = $detail['is_default'] ? 'edit_default' : 'edit';
        return view($template, ['detail' => $detail]);
    }

    /**
     * @notes 删除分销等级
     * @return \think\response\Json
     * @author Tab
     * @date 2021/9/1 16:18
     */
    public function delete()
    {
        $params = $this->request->post();
        $result = DistributionLevelLogic::delete($params);
        if($result) {
            return JsonServer::success('删除成功');
        }
        return JsonServer::error(DistributionLevelLogic::getError());
    }
}