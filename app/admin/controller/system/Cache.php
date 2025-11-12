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
use app\common\server\JsonServer;

/**
 * 系统缓存
 * Class Cache
 * @package app\admin\controller\system
 */
class Cache extends AdminBase
{
    public function cache()
    {
        if ($this->request->isAjax()) {
            \think\facade\Cache::clear();
            del_target_dir(app()->getRootPath().'runtime/file/export', true);
            return JsonServer::success('清除成功');
        }
        return view();
    }
}