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

use app\admin\logic\setting\MapLogic;
use app\common\basics\AdminBase;
use app\common\server\JsonServer;

/**
 * 地图peizhi
 * Class Map
 * @package app\admin\controller\setting
 */
class Map extends AdminBase
{
    /**
     * @notes 地图配置
     * @return \think\response\Json|\think\response\View
     * @author 段誉
     * @date 2022/1/17 10:30
     */
    public function config()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            MapLogic::setConfig($post);
            return JsonServer::success('操作成功');
        }
        return view('', [
            'config' => MapLogic::getConfig()
        ]);
    }

}