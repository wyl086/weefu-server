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

use app\common\enum\ClientEnum;
use app\common\enum\IntegralOrderEnum;
use app\common\enum\PayEnum;
use app\common\logic\IntegralOrderRefundLogic;
use app\common\logic\PayNotifyLogic;
use app\common\model\integral\IntegralOrder;
use app\common\model\order\Order;
use app\common\model\order\OrderTrade;
use app\common\model\RechargeOrder;
use app\common\model\user\User;
use app\common\model\user\UserAuth;
use app\common\server\DouGong\BaseRequest;
use app\common\server\WeChatServer;
use Endroid\QrCode\QrCode;

/**
 * @notes 聚合正扫
 * author lbzy
 * @datetime 2023-09-28 16:13:41
 * @class PayZhengSao
 * @package app\common\server\DouGong\pay
 */
class PayZhengSao extends BaseRequest
{
    protected $request_uri = '/v2/trade/payment/jspay';
    
    private $pays = [
        PayEnum::HFDG_WECHAT => [
            ClientEnum::mnp     => 'T_MINIAPP',
            ClientEnum::oa      => 'T_JSAPI',
            ClientEnum::ios     => 'T_MINIAPP',
            ClientEnum::android => 'T_MINIAPP',
            ClientEnum::pc      => 'T_JSAPI',
            ClientEnum::h5      => 'T_MINIAPP',
        ],
        PayEnum::HFDG_ALIPAY    => [
            // ClientEnum::mnp     => 'A_JSAPI',
            // ClientEnum::oa      => 'A_JSAPI',
            ClientEnum::ios     => 'A_NATIVE',
            ClientEnum::android => 'A_NATIVE',
            ClientEnum::pc      => 'A_NATIVE',
            ClientEnum::h5      => 'A_NATIVE',
        ],
    ];
    
    protected function initialize()
    {
        $this->initUser();
        $this->initRequestData();
        $this->parseOrder();
    }
    
    private function initUser(): void
    {
        if (isset($this->params['user_id']) && $this->params['user_id'] > 0) {
            $this->userInfo = User::findOrEmpty($this->params['user_id'])->toArray();
        }
        
        if (isset($this->userInfo['id']) && $this->params['pay_way'] == PayEnum::HFDG_WECHAT) {
            switch ($this->params['client'] ?? '') {
                case ClientEnum::mnp:
                case ClientEnum::h5:
                case ClientEnum::ios:
                case ClientEnum::android:
                    $this->userAuth = UserAuth::where('user_id', $this->params['user_id'])
                        ->where('client', $this->params['client'])
                        ->order('id desc')
                        ->findOrEmpty()->toArray();
                    $wechat_config  = WeChatServer::getMnpConfig();
                    break;
                case ClientEnum::oa:
                case ClientEnum::pc:
                    $this->userAuth = UserAuth::where('user_id', $this->params['user_id'])
                        ->where('client', $this->params['client'])
                        ->order('id desc')
                        ->findOrEmpty()->toArray();
                    $wechat_config  = WeChatServer::getOaConfig();
                    break;
                default:
                    break;
            }
    
            $this->config['appid']          = $wechat_config['app_id'] ?? '';
            $this->config['sub_appid']      = $wechat_config['app_id'] ?? '';
    
            $this->request_data['data']['wx_data']['sub_appid']     = $this->config['sub_appid'] ?? '';
            $this->request_data['data']['wx_data']['openid']        = $this->userAuth['openid'] ?? '';
            $this->request_data['data']['wx_data']['sub_openid']    = $this->userAuth['openid'] ?? '';
        }
    }
    
    protected function beforeRequestCheck() : bool
    {
        if ($this->params['pay_way'] == PayEnum::HFDG_WECHAT && empty($this->userAuth['openid'])) {
            $this->request_message = '支付失败:授权信息失效';
            return false;
        }
        
        return true;
    }
    
    protected function initRequestData()
    {
        // 请求日期req_date
        $this->request_data['data']['req_date']     = date("Ymd");
        // 交易类型trade_type
        $this->request_data['data']['trade_type']   = $this->pays[$this->params['pay_way']][$this->params['client']];
        // 商户id
        $this->request_data['data']['huifu_id']     = $this->config['huifu_id'];
        // 备注
        $this->request_data['data']['remark']       = implode('-', [
            $this->params['pay_way'],
            $this->params['client'],
            $this->params['from'],
            $this->params['order_id'],
        ]);
        // 回调
        if ($this->params['pay_way'] == PayEnum::HFDG_WECHAT) {
            $this->request_data['data']['notify_url']   = (string) url('pay/hfdgPayWechatNotify', [], false, true);
        }
        if ($this->params['pay_way'] == PayEnum::HFDG_ALIPAY) {
            $this->request_data['data']['notify_url']   = (string) url('pay/hfdgPayAlipayNotify', [], false, true);
        }
    }
    
    function getPayResult() : array
    {
        if ($this->request_success) {
            // 记录请求时间等信息
            $update = [
                'hfdg_params' => [
                    'pay_request_time'  => $_SERVER['REQUEST_TIME'],
                    'pay_request_date'  => $this->request_data['data']['req_date'],
                    'req_seq_id'        => $this->request_data['data']['req_seq_id'],
                ],
            ];
            
            switch ($this->params['from']) {
                case 'trade':
                    OrderTrade::update($update, [ [ 'id', '=', $this->params['order_id'] ] ]);
                    Order::update($update, [ [ 'trade_id', '=', $this->params['order_id'] ] ]);
                    break;
                case 'order':
                    Order::update($update, [ [ 'id', '=', $this->params['order_id'] ] ]);
                    break;
                case 'recharge':
                    RechargeOrder::update($update, [ [ 'id', '=', $this->params['order_id'] ] ]);
                    break;
                case 'integral':
                    IntegralOrder::update($update, [ [ 'id', '=', $this->params['order_id'] ] ]);
                    break;
                default:
                    break;
            }
            
            $data = '';
            $code = 1;
            
            if ($this->params['pay_way'] == PayEnum::HFDG_WECHAT) {
                switch ($this->params['client'] ?? '') {
                    case ClientEnum::mnp:
                    case ClientEnum::oa:
                    case ClientEnum::pc:
                    case ClientEnum::h5:
                    case ClientEnum::ios:
                    case ClientEnum::android:
                        $data = json_decode($this->request_result['data']['pay_info'], true);
                        break;
                    default:
                        break;
                }
            }
            
            if ($this->params['pay_way'] == PayEnum::HFDG_ALIPAY) {
                $code = 10001;
                switch ($this->params['client'] ?? '') {
                    case ClientEnum::pc:
                        $qrCode = new QrCode();
                        $qrCode->setText($this->request_result["data"]["qr_code"]);
                        $qrCode->setSize(1000);
                        $base64 = chunk_split(base64_encode($qrCode->writeString()));
                        $data   = 'data:image/png;base64,' . $base64;
                        break;
                    case ClientEnum::h5:
                    case ClientEnum::ios:
                    case ClientEnum::android:
                        $data = $this->request_result["data"]["qr_code"];
                        break;
                    default:
                        break;
                }
            }
            
            return [
                'code'  => $code,
                'msg'   => $this->request_message,
                'show'  => 0,
                'pay_way'   => $this->params['pay_way'],
                'data'  => $data,
            ];
        }
        
        return [
            'code'  => 0,
            'msg'   => $this->request_message,
            'show'  => 1,
            'pay_way'   => $this->params['pay_way'],
            'data'  => [],
        ];
    }
    
    protected function parseOrder()
    {
        // 请求流水号
        $this->request_data['data']['req_seq_id'] = $this->params['order']['sn'] . mt_rand(100000, 999999);
        // 交易金额
        $this->request_data['data']['trans_amt'] = bcadd($this->params['order']['order_amount'], 0, 2);
        // 商品描述
        switch ($this->params['from']) {
            case 'trade':
                $this->request_data['data']['goods_desc'] = "商品总订单";
                break;
            case 'order':
                $this->request_data['data']['goods_desc'] = "商品子订单";
                break;
            case 'recharge':
                $this->request_data['data']['goods_desc'] = "充值";
                break;
            case 'integral':
                $this->request_data['data']['goods_desc'] = "积分商城";
                break;
            default:
                $this->request_data['data']['goods_desc'] = "商品";
                break;
        }
    }
    
    static function asyncSuccessDeal($async_result)
    {
        $data = (array) json_decode($async_result['resp_data'], true);
        
        $remarks = explode('-', $data['remark']);
        
        switch ($remarks[2]) {
            case 'order':
                $order = Order::findOrEmpty($remarks[3]);
                if (! $order || $order['pay_status'] >= PayEnum::ISPAID) {
                    break ;
                }
                PayNotifyLogic::handle('order', $order['order_sn'], [ 'transaction_id' => $data['hf_seq_id'] ]);
                break;
            
            case 'trade':
                $order_trade = OrderTrade::findOrEmpty($remarks[3]);
                
                $orders = Order::where(['trade_id' => $remarks[3] ])->select();
                foreach ($orders as $order) {
                    if (!$order || $order['pay_status'] >= PayEnum::ISPAID) {
                        break 2;
                    }
                }
                PayNotifyLogic::handle('trade', $order_trade['t_sn'], [ 'transaction_id' => $data['hf_seq_id'] ]);
                break;
            
            case 'recharge':
                $order = RechargeOrder::findOrEmpty($remarks[3]);
                if (! $order || $order['pay_status'] >= PayEnum::ISPAID) {
                    break ;
                }
                PayNotifyLogic::handle('recharge', $order['order_sn'], [ 'transaction_id' => $data['hf_seq_id'] ]);
                break;
            
            case 'integral':
                $order = IntegralOrder::findOrEmpty($remarks[3]);
                if (! $order || $order['refund_status'] == IntegralOrderEnum::IS_REFUND) {
                    // 没有订单记录 或者 订单已发生退款 中断后续操作
                    break ;
                }
                if ($order['order_status'] == IntegralOrderEnum::ORDER_STATUS_DOWN) {
                    // 收到支付回调时，订单已被关闭, 则进行退款操作
                    IntegralOrderRefundLogic::refundOrderAmount($order['id']);
                    break ;
                }
                
                if ($order['pay_status'] >= PayEnum::ISPAID) {
                    break ;
                }
                
                PayNotifyLogic::handle('integral', $order['order_sn'], [ 'transaction_id' => $data['hf_seq_id'] ]);
                break;
        }
        
        return true;
    }
}