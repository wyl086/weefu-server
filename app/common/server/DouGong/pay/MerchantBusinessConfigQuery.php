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

class MerchantBusinessConfigQuery extends BaseRequest
{
    protected $request_uri = '/v2/merchant/busi/config/query';
    
    protected function initialize()
    {
        // 请求流水号
        $this->request_data['data']['req_seq_id']  = time() . mt_rand(100000, 999999);
        // 请求日期req_date
        $this->request_data['data']['req_date']     = date("Ymd");
        // 商户id
        $this->request_data['data']['huifu_id']     = $this->config['huifu_id'];
    }
}