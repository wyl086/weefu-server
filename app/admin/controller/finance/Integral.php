<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\admin\controller\finance;


use app\admin\logic\finance\IntegralLogic;
use app\common\basics\AdminBase;
use app\common\server\JsonServer;

class Integral extends AdminBase
{
    /**
     * @notes 积分明细
     * @return \think\response\Json|\think\response\View
     * @author ljj
     * @date 2022/2/22 6:00 下午
     */
    public function integral()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $data = IntegralLogic::integral($get);
            return JsonServer::success('', $data, 1);
        }

        return view();
    }


    /**
     * @notes 导出积分明细Excel
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/4/24 10:20
     */
    public function export()
    {
        $params = $this->request->get();
        $result = IntegralLogic::integral($params, true);
        if(false === $result) {
            return JsonServer::error(IntegralLogic::getError() ?: '导出失败');
        }
        return JsonServer::success('', $result);
    }
}