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

use app\admin\validate\setting\HfdgValidate;
use app\common\basics\AdminBase;
use app\common\server\ConfigServer;
use app\common\server\DouGong\pay\MerchantBusinessConfig;
use app\common\server\JsonServer;
use app\common\server\WeChatServer;
use think\response\Json;
use think\response\View;

/**
 * @notes 汇付斗拱
 * author lbzy
 * @datetime 2023-10-20 15:18:33
 * @class Hfdg
 * @package app\admin\controller\setting
 */
class Hfdg extends AdminBase
{
    /**
     * @notes 开发设置
     * @return Json|View
     * @author lbzy
     * @datetime 2023-10-20 15:19:41
     */
    function dev_set()
    {
        if (request()->isAjax()) {
            (new HfdgValidate())->goCheck('DevSet');
            ConfigServer::set('hfdg_dev_set', 'sys_id', input('sys_id'));
            ConfigServer::set('hfdg_dev_set', 'product_id', input('product_id'));
            ConfigServer::set('hfdg_dev_set', 'huifu_id', input('huifu_id'));
            ConfigServer::set('hfdg_dev_set', 'rsa_merch_private_key', input('rsa_merch_private_key'));
            ConfigServer::set('hfdg_dev_set', 'rsa_merch_public_key', input('rsa_merch_public_key'));
            ConfigServer::set('hfdg_dev_set', 'rsa_huifu_public_key', input('rsa_huifu_public_key'));
            return JsonServer::success('保存成功');
        }
        
        return view('', [ 'config' => ConfigServer::get('hfdg_dev_set') ]);
    }
    
    /**
     * @notes 微信商户设置
     * @return Json|View
     * @author lbzy
     * @datetime 2023-10-26 17:18:54
     */
    private function wechat_set()
    {
        if (request()->isAjax()) {
            if (empty(input('oa_app_id')) || empty(input('mnp_app_id'))) {
                return JsonServer::error('请先设置微信相关渠道');
            }
            $result = (new MerchantBusinessConfig(input()))->request()->getConfigResult();
            if ($result['code'] == 1) {
                return JsonServer::success('设置成功');
            } else {
                return JsonServer::error($result['msg']);
            }
        }
        
        return view('', [
            'oa_app_id'     => WeChatServer::getOaConfig()['app_id'] ?? '',
            'mnp_app_id'    => WeChatServer::getMnpConfig()['app_id'] ?? '',
        ]);
    }
}