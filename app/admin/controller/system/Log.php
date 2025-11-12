<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\admin\controller\system;

use app\common\basics\AdminBase;
use app\common\utils\Time;
use app\admin\logic\system\LogLogic;
use app\common\server\JsonServer;

/**
 * 系统日志
 * Class Log
 * @package app\admin\controller
 */
class Log extends AdminBase
{
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $data = LogLogic::lists($get);
            return JsonServer::success('', $data);
        }

        return view('', Time::getTime());
    }

}