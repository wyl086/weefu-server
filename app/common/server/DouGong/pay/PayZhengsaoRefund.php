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

use app\common\enum\PayEnum;
use app\common\model\integral\IntegralOrderRefund;
use app\common\model\order\OrderRefund;
use app\common\server\DouGong\BaseRequest;

/**
 * @notes 退款
 * author lbzy
 * @datetime 2023-10-23 14:45:05
 * @class PayZhengsaoRefund
 * @package app\common\server\DouGong\pay
 */
class PayZhengsaoRefund extends BaseRequest
{
    protected $request_uri = '/v2/trade/payment/scanpay/refund';
    
    protected function initialize()
    {
        $this->initRequestData();
    }
    
    protected function initRequestData()
    {
        // 商户id
        $this->request_data['data']['huifu_id']         = $this->config['huifu_id'];
        // 请求日期req_date
        $this->request_data['data']['req_date']         = date("Ymd");
        // 退款金额
        $this->request_data['data']['ord_amt']          = bcadd($this->params['refund']['money'], 0, 2);
        // 退款单号
        if ($this->params['from'] == 'order') {
            $this->request_data['data']['req_seq_id']   = OrderRefund::where('id', $this->params['refund']['id'])->value('refund_sn');
        }
        if ($this->params['from'] == 'integral') {
            $this->request_data['data']['req_seq_id']   = IntegralOrderRefund::where('id', $this->params['refund']['id'])->value('refund_sn');
        }
        // 原全局流水号
        $this->request_data['data']['org_hf_seq_id']    = $this->params['order']['transaction_id'];
        // 原请求日期org_req_date
        $this->request_data['data']['org_req_date']     = $this->params['order']['hfdg_params']['pay_request_date'];
        
        $this->request_data['data']['remark']           = implode('-', [
            $this->params['from'],
            $this->params['order']['id'],
            $this->params['refund']['id'],
        ]);
        
        // 回调
        // $this->request_data['data']['notify_url'];
    }
    
    function getRefundResult()
    {
        if ($this->request_success && in_array($this->request_result['data']['trans_stat'], [ 'P', 'S' ])) {
            // 记录请求时间等信息
            $update = [
                'hfdg_params' => [
                    'refund_request_time'  => $_SERVER['REQUEST_TIME'],
                    'refund_request_date'  => $this->request_data['data']['req_date'],
                ],
            ];
    
            switch ($this->params['from']) {
                case 'order':
                    OrderRefund::update($update, [ [ 'id', '=', $this->params['refund']['id'] ] ]);
                    break;
                case 'integral':
                    IntegralOrderRefund::update($update, [ [ 'id', '=', $this->params['refund']['id'] ] ]);
                    break;
                default:
                    break;
            }
            
            return [
                'code'  => 1,
                'msg'   => $this->request_message,
                'show'  => 0,
                'data'  => [],
            ];
        }
    
        return [
            'code'  => 0,
            'msg'   => $this->request_message,
            'show'  => 0,
            'data'  => [],
        ];
    }
}