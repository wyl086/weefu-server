<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\common\server\DouGong\pay;

use app\common\server\DouGong\BaseRequest;

/**
 * @notes 微信商户配置
 * https://paas.huifu.com/partners/api/#/shgl/shjj/api_shjj_wxshpz
 * author lbzy
 * @datetime 2023-10-26 17:12:11
 * @class MerchantBusinessConfig
 * @package app\common\server\DouGong\pay
 */
class MerchantBusinessConfig extends BaseRequest
{
    protected $request_uri = '/v2/merchant/busi/config';
    
    protected function initialize()
    {
        // 请求流水号
        $this->request_data['data']['req_seq_id']  = time() . mt_rand(100000, 999999);
        // 请求日期req_date
        $this->request_data['data']['req_date']     = date("Ymd");
        // 商户id
        $this->request_data['data']['huifu_id']     = $this->config['huifu_id'];
        // 业务开通类型
        $this->request_data['data']['fee_type']     = '01';
        // 公众号
        if (isset($this->params['oa_app_id'])) {
            $this->request_data['data']['wx_woa_app_id'] = $this->params['oa_app_id'];
            // $this->request_data['data']['wx_woa_path'] = request()->domain() . '/';
        }
        // 小程序
        if (isset($this->params['mnp_app_id'])) {
            $this->request_data['data']['wx_applet_app_id'] = $this->params['mnp_app_id'];
        }
    }
    
    function getConfigResult() : array
    {
        return [
            'code'  => intval($this->request_success),
            'msg'   => $this->request_message,
            'show'  => 0,
            'data'  => [],
        ];
    }
}