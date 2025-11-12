<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\admin\controller;

use app\common\logic\ExpressLogic;
use app\common\basics\AdminBase;
use app\common\server\ConfigServer;
use app\common\logic\FreightLogic;
use app\common\model\Freight as FreightModel;
use app\common\server\JsonServer;

class Freight extends AdminBase
{
    /**
     * User: 意象信息科技 mjf
     * Desc: 设置快递方式
     */
    public function set()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $post['type'] = isset($post['type']) && $post['type'] == 'on' ? 1 : 0;
            ConfigServer::set('express', 'is_express', $post['type']);
            return JsonServer::success('操作成功');
        }
        $type = ConfigServer::get('express', 'is_express');
        return view('', [
            'type' => $type
        ]);
    }

    /**
     * User: 意象信息科技 mjf
     * Desc: 运费模板列表
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            return JsonServer::success('获取成功', FreightLogic::lists($get));//运费模板页
        }
        return view('index', [
            'charge_way_lists' => FreightModel::getChargeWay(true),
            'config'=>ExpressLogic::getExpress()
        ]);
    }

}