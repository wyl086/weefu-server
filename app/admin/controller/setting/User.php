<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\admin\controller\setting;

use app\admin\logic\setting\UserLogic;
use app\common\basics\AdminBase;
use app\common\server\JsonServer;

/**
 * 用户设置
 * Class User
 * @package app\admin\controller\setting
 */
class User extends AdminBase
{
    /**
     * @notes 用户设置
     * @return \think\response\View
     * @author Tab
     * @date 2021/9/1 10:07
     */
    public function index()
    {
        $config = UserLogic::getConfig();
        return view('', ['config' => $config,'user_level'=>UserLogic::getUserLevel()]);
    }

    /**
     * @notes 用户设置
     * @return \think\response\Json
     * @author Tab
     * @date 2021/9/1 10:33
     */
    public function set()
    {
        $params = $this->request->post();
        $result = UserLogic::set($params);
        if($result) {
            return JsonServer::success('保存成功');
        }
        return JsonServer::error(UserLogic::getError());
    }
}