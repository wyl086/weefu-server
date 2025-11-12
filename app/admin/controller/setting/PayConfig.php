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

use app\admin\logic\PayConfigLogic;
use app\common\basics\AdminBase;
use app\common\server\ConfigServer;
use app\common\server\JsonServer;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * Class PayConfig
 * @package app\admin\controller\setting
 */
class PayConfig extends AdminBase
{

    /**
     * @notes 支付列表
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/13 7:03 下午
     */
    public function lists()
    {

        if ($this->request->isAjax()) {
            return JsonServer::success('', PayConfigLogic::lists());
        }
        return view();
    }


    /**
     * @notes 余额配置
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/13 7:03 下午
     */
    public function editBalance()
    {

        if ($this->request->isAjax()) {
            $post = $this->request->post();
            if (empty($post['image']) && $post['status'] == 1) {
                return JsonServer::error('请选择支付图标');
            }
            PayConfigLogic::editBalance($post);
            return JsonServer::success('修改成功');
        }
        return view('', ['info' => PayConfigLogic::info('balance')]);
    }


    /**
     * @notes 微信配置
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/13 7:03 下午
     */
    public function editWechat()
    {

        if ($this->request->isAjax()) {
            $post = $this->request->post();
            if ($post['status'] == 1) {
                if (empty($post['image'])) {
                    return JsonServer::error('请选择支付图标');
                }
                if ($post['apiclient_cert'] == '' || $post['apiclient_key'] == '') {
                    return JsonServer::error('apiclient_cert或apiclient_key不能为空');
                }
            }
            PayConfigLogic::editWechat($post);
            return JsonServer::success('修改成功');
        }
        $domain_name = ConfigServer::get('website', 'domain_name', '');
        return view('', [
            'domain' => $domain_name ? $domain_name : request()->domain(),
            'info' => PayConfigLogic::info('wechat')
        ]);
    }


    /**
     * @notes 支付宝配置
     * @return \think\response\Json|\think\response\View
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\PDOException
     * @author suny
     * @date 2021/7/13 7:03 下午
     */
    public function editAlipay()
    {

        if ($this->request->isAjax()) {
            $post = $this->request->post();
            if (empty($post['image']) && $post['status'] == 1) {
                return JsonServer::error('请选择支付图标');
            }
            PayConfigLogic::editAlipay($post);
            return JsonServer::success('修改成功');
        }
        return view('', ['info' => PayConfigLogic::info('alipay')]);
    }
    
    /**
     * @notes 汇付斗拱微信配置
     * @return \think\response\Json|\think\response\View
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     * @author lbzy
     * @datetime 2023-10-08 14:47:06
     */
    function editHfdgWechat()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            if (empty($post['image']) && $post['status'] == 1) {
                return JsonServer::error('请选择支付图标');
            }
            PayConfigLogic::editHfdgWechat($post);
            return JsonServer::success('修改成功');
        }
        
        return view('', [ 'info' => PayConfigLogic::info('hfdg_wechat') ]);
    }
    
    
    /**
     * @notes 汇付斗拱支付宝配置
     * @return \think\response\Json|\think\response\View
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     * @author lbzy
     * @datetime 2023-10-18 14:55:30
     */
    function editHfdgAlipay()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            if (empty($post['image']) && $post['status'] == 1) {
                return JsonServer::error('请选择支付图标');
            }
            PayConfigLogic::editHfdgAlipay($post);
            return JsonServer::success('修改成功');
        }
        
        return view('', [ 'info' => PayConfigLogic::info('hfdg_alipay') ]);
    }

}