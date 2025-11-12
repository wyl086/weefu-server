<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\api\controller;

use app\api\logic\UserDeleteLogic;
use app\common\basics\Api;
use app\common\server\JsonServer;

class UserDelete extends Api
{
    /**
     * @notes 检测是否可注销
     * @return \think\response\Json
     * @author lbzy
     * @datetime 2023-08-21 16:23:31
     */
    function check(): \think\response\Json
    {
        return JsonServer::success('获取成功', UserDeleteLogic::checkCanDelete($this->user_id));
    }
    
    /**
     * @notes 确定注销
     * @return \think\response\Json
     * @author lbzy
     * @datetime 2023-08-21 16:46:32
     */
    function delete()
    {
        $result = UserDeleteLogic::sureDelete($this->user_id);
        
        if ($result !== true) {
            return JsonServer::error((string) $result);
        }
    
        return JsonServer::success('成功');
    }
}