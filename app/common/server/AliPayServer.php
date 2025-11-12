<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\common\server;


use Alipay\EasySDK\Kernel\Config;
use app\common\enum\IntegralOrderEnum;
use app\common\logic\IntegralOrderRefundLogic;
use app\common\logic\PayNotifyLogic;
use app\common\model\Client_;
use app\common\enum\PayEnum;
use app\common\model\integral\IntegralOrder;
use think\facade\Db;
use think\facade\Log;
use Yansongda\Pay\Exceptions\GatewayException;
use Yansongda\Pay\Exceptions\InvalidConfigException;
use Yansongda\Pay\Exceptions\InvalidSignException;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Collection;

class AliPayServer
{


    protected $error = '未知错误';
    
    private $alipay = null;

    public function getError()
    {
        return $this->error;
    }


    public function __construct()
    {
        $this->alipay = Pay::alipay(YansongdaAliPayServer::config());
    }


    /**
     * Notes: 支付设置
     * @author 段誉(2021/3/23 10:33)
     * @return Config
     * @throws \Exception
     */
    // public function getOptions()
    // {
    //     $result = (new PayModel())->where(['codePay' => 'alipay'])->find();
    //     if (empty($result)) {
    //         throw new \Exception('请配置好支付设置');
    //     }
    //
    //     $options = new Config();
    //     $options->protocol      = 'https';
    //     $options->gatewayHost   = 'openapi.alipay.com';
    //     // $options->gatewayHost   = 'openapi.alipaydev.com'; //测试沙箱地址
    //     $options->signType      = 'RSA2';
    //     $options->appId         = $result['config']['app_id'] ?? '';
    //
    //     // 应用私钥
    //     $options->merchantPrivateKey = $result['config']['private_key'] ?? '';
    //     //支付宝公钥
    //     // $options->alipayPublicKey = $result['config']['ali_public_key'] ?? '';
    //
    //     // 支付宝公钥证书文件路径，例如：/foo/alipayCertPublicKey_RSA2.crt
    //     $options->alipayCertPath        = YansongdaAliPayServer::_cache_file('ali_public_cert', $result['config']['ali_public_cert'] ?? '');
    //     // 支付宝根证书文件路径，例如：/foo/alipayRootCert.crt
    //     $options->alipayRootCertPath    = YansongdaAliPayServer::_cache_file('ali_root_cert', $result['config']['ali_root_cert'] ?? '');
    //     // 应用公钥证书文件路径，例如：/foo/appCertPublicKey_2019051064521003.crt
    //     $options->merchantCertPath      = YansongdaAliPayServer::_cache_file('app_cert', $result['config']['app_cert'] ?? '');
    //
    //     //回调地址
    //     $options->notifyUrl = (string)url('pay/aliNotify', [], false, true);
    //
    //     return $options;
    // }

    static function getAliPayData($attach, $order_id) : array
    {
        if($attach == 'trade') {
            $trade          = Db::name('order_trade')->where(['id' => $order_id])->find();
            $sn             = $trade['t_sn'];
            $order_amount   = $trade['order_amount'];
        }
        
        if($attach == 'order') {
            $order          = Db::name('order')->where(['id' => $order_id])->find();
            $sn             = $order['order_sn'];
            $order_amount   = $order['order_amount'];
        }
        
        if($attach == 'recharge') {
            $order          = Db::name('recharge_order')->where(['id' => $order_id])->find();
            $sn             = $order['order_sn'];
            $order_amount   = $order['order_amount'];
        }
        
        if ($attach == 'integral') {
            $order          = IntegralOrder::where(['id' => $order_id])->find();
            $sn             = $order['order_sn'];
            $order_amount   = $order['order_amount'];
        }
        
        return [
            'sn'            => $sn ?? '',
            'order_amount'  => $order_amount ?? 0,
        ];
    }

    /**
     * Notes: pc支付
     * @param $attach
     * @param $order
     * @author 段誉(2021/3/22 18:38)
     * @return string
     */
    public function pagePay($attach, $order_id)
    {
        $domain = request()->domain();
        
        $data = static::getAliPayData($attach, $order_id);
        
        $ali_data = [
            'out_trade_no'      => $data['sn'],
            'total_amount'      => $data['order_amount'],
            'subject'           => '订单:' . $data['sn'],
            'return_url'        => $domain.'/pc/user/order',
            'passback_params'   => $attach,
        ];
    
        return $this->alipay->web($ali_data)->getContent();
    }


    /**
     * Notes: app支付
     * @param $attach
     * @param $order
     * @author 段誉(2021/3/22 18:38)
     * @return string
     */
    public function appPay($attach, $order_id)
    {
    
        $data = static::getAliPayData($attach, $order_id);
    
        $ali_data = [
            'out_trade_no'      => $data['sn'],
            'total_amount'      => $data['order_amount'],
            'subject'           => $data['sn'],
            'passback_params'   => $attach,
        ];
    
        return $this->alipay->app($ali_data)->getContent();
    }


    /**
     * Notes: 手机网页支付
     * @param $attach
     * @param $order
     * @author 段誉(2021/3/22 18:38)
     * @return string
     */
    public function wapPay($attach, $order_id)
    {
    
        $data = static::getAliPayData($attach, $order_id);
        
        $domain = request()->domain();
    
        $ali_data = [
            'out_trade_no'      => $data['sn'],
            'total_amount'      => $data['order_amount'],
            'subject'           => $data['sn'],
            'passback_params'   => $attach,
            'return_url'        => $domain . '/mobile/bundle/pages/user_order/user_order',
            'quit_url'          => $domain . '/mobile/bundle/pages/user_order/user_order',
        ];
    
        return $this->alipay->wap($ali_data)->getContent();
    }


    /**
     * Notes: 支付
     * @param $from
     * @param $order
     * @param $order_source
     * @author 段誉(2021/3/22 18:33)
     * @return bool|string
     */
    public function pay($from, $order_id, $order_source)
    {
        try{
            switch ($order_source){
                case Client_::pc:
                    $result = $this->pagePay($from, $order_id);
                    break;
                case Client_::ios:
                case Client_::android:
                    $result = $this->appPay($from, $order_id);
                    break;
                case Client_::h5:
                    $result = $this->wapPay($from, $order_id);
                    break;
                default:
                    throw new \Exception('支付方式错误');
            }
            return $result;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }


    /**
     * Notes: 支付回调验证
     * @param $data
     * @author 段誉(2021/3/22 17:22)
     * @return bool
     */
    public function verifyNotify($data)
    {
        try {
            $this->alipay->verify($data);
            
            if (!in_array($data['trade_status'], ['TRADE_SUCCESS', 'TRADE_FINISHED'])) {
                return true;
            }
            $extra['transaction_id'] = $data['trade_no'];
            //验证订单是否已支付
            switch ($data['passback_params']) {
                case 'order':
                    $order = Db::name('order')->where(['order_sn' => $data['out_trade_no']])->find();
                    if (!$order || $order['pay_status'] >= PayEnum::ISPAID) {
                        return true;
                    }
                    PayNotifyLogic::handle('order', $data['out_trade_no'], $extra);
                    break;

                case 'trade':
                    $order_trade = Db::name('order_trade')->where(['t_sn' => $data['out_trade_no']])->find();
                    $trade_id = $order_trade['id'];
                    $orders = Db::name('order')->where(['trade_id' => $trade_id])->select();
                    foreach ($orders as $order) {
                        if (!$order || $order['pay_status'] >= PayEnum::ISPAID) {
                            return true;
                        }
                    }
                    PayNotifyLogic::handle('trade', $data['out_trade_no'], $extra);
                    break;

                case 'recharge':
                    $order = Db::name('recharge_order')->where(['order_sn' => $data['out_trade_no']])->find();
                    if (!$order || $order['pay_status'] >= PayEnum::ISPAID) {
                        return true;
                    }
                    PayNotifyLogic::handle('recharge', $data['out_trade_no'], $extra);
                    break;

                case 'integral':
                    $order = IntegralOrder::where(['order_sn' => $data['out_trade_no']])->find();
                    if (!$order || $order['refund_status'] == IntegralOrderEnum::IS_REFUND) {
                        // 没有订单记录 或者 订单已发生退款 中断后续操作
                        return true;
                    }
                    if ($order['order_status'] == IntegralOrderEnum::ORDER_STATUS_DOWN) {
                        // 收到支付回调时，订单已被关闭, 则进行退款操作
                        IntegralOrderRefundLogic::refundOrderAmount($order['id']);
                        return true;
                    }
                    if ($order['pay_status'] >= PayEnum::ISPAID) {
                        return true;
                    }
                    PayNotifyLogic::handle('integral', $data['out_trade_no'], $extra);
                    break;
            }

            return true;
        } catch (\Throwable $e) {
            $record = [
                __CLASS__, __FUNCTION__, $e->getFile(), $e->getLine(), $e->getMessage()
            ];
            Log::record(implode('-', $record));
            return false;
        }
    }
    
    
    /**
     * @notes 查询订单
     * @param $order_sn
     * @return Collection
     * @throws InvalidConfigException
     * @throws InvalidSignException
     * @throws GatewayException
     * @author lbzy
     * @datetime 2023-08-01 11:04:49
     */
    public function checkPay($order_sn) : Collection
    {
        return $this->alipay->find([
            'out_trade_no'  => $order_sn,
        ]);
    }
    
    /**
     * @notes 退款
     * @param $order_sn
     * @param $order_amount
     * @param string $out_request_no 部分退款订单
     * @return array
     * @author lbzy
     * @datetime 2023-08-01 11:04:55
     */
    public function refund($order_sn, $order_amount, string $out_request_no = '')
    {
        try {
            
            $data = [
                'out_trade_no'  => "{$order_sn}",
                'refund_amount' => "{$order_amount}",
            ];
            
            if ($out_request_no) {
                $data['out_request_no'] = "{$out_request_no}";
            }
            
            return $this->alipay->refund($data)->toArray();
            
        } catch(InvalidConfigException |InvalidSignException|GatewayException|\Throwable $e) {
            Log::write([ $order_sn, $order_amount, $e->__toString() ], 'ALI_PAY_REFUND');
            return [ 'code' => 0, 'msg' => 'Failed', 'fund_change' => '', 'e' => [  $order_sn, $order_amount ] ];
        }
        
    }


}

