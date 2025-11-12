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


use app\admin\logic\setting\SmsLogic;
use app\common\basics\AdminBase;
use app\common\enum\SmsEnum;
use app\common\server\ConfigServer;
use app\common\server\JsonServer;

/**
 * 短信设置
 * Class Sms
 * @package app\admin\controller\setting
 */
class Sms extends AdminBase
{

    /**
     * Notes: 列表
     * @author 段誉(2021/6/7 14:46)
     * @return \think\response\Json|\think\response\View
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $lists = SmsLogic::configLists();
            return JsonServer::success('获取成功', $lists);
        }
        return view('', ['status_list' => SmsEnum::getSendStatusDesc(true)]);
    }

    /**
     * Notes: 短信配置
     * @author 段誉(2021/6/7 14:46)
     * @return \think\response\Json|\think\response\View
     */
    public function config()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $res = SmsLogic::setConfig($post);
            if (false === $res) {
                return JsonServer::error(SmsLogic::getError());
            }
            return JsonServer::success('设置成功');
        }
        $engine = $this->request->get('engine');
        $info = SmsLogic::getConfigInfo($engine);
        if (false === $info) {
            return JsonServer::error('数据错误');
        }
        return view('', [
            'engine'    => $engine,
            'info'      => $info
        ]);
    }




    /**
     * Notes: 短信记录->列表
     * @author 段誉(2021/6/7 14:46)
     * @return \think\response\Json
     */
    public function logLists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $lists = SmsLogic::logLists($get);
            return JsonServer::success('', $lists);
        }
    }

    /**
     * Notes: 短信记录->详情
     * @author 段誉(2021/6/7 14:46)
     * @return \think\response\View
     */
    public function detail()
    {
        $id = $this->request->get('id');
        $info = SmsLogic::detail($id);
        return view('', ['info' => $info]);
    }
}