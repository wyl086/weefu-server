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

use app\common\model\Pay;
use think\facade\Log;
use Yansongda\Pay\Exceptions\GatewayException;
use Yansongda\Pay\Exceptions\InvalidConfigException;
use Yansongda\Pay\Exceptions\InvalidGatewayException;
use Yansongda\Pay\Exceptions\InvalidSignException;

class YansongdaAliPayTransferServer
{
    
    
    function _cache_file($key,$content, $ext = 'crt') : string
    {
        $path = runtime_path() . 'admin/alipay';
        
        if (! file_exists($path)) {
            mkdir($path, 0775, true);
        }
        
        $file = $path . '/' . $key . '.' . md5($content) . ".{$ext}";
        
        if (! file_exists($file)) {
            file_put_contents($file, $content);
        }
        
        return $file;
    }
    
    function config()
    {
        $result = (new Pay())->where([ 'code' => 'alipay' ])->find();
        
        return [
    
            // 支付宝异步通知地址
            // 'notify_url' => '',
    
            // 支付成功后同步通知地址
            // 'return_url' => '',
    
            // 支付宝公钥地址 新版已使用证书方式
            // 'ali_public_key' => "",
            
            // 支付宝分配的 APPID
            'app_id'                => $result['config']['app_id'] ?? '',
        
            'sign_type'             => 'RSA2',
        
            //商户私钥地址（默认沙箱通用私钥，如需调试线上环境请换成线上的私钥：https://docs.open.alipay.com/291/106103/）
            'private_key'           => $result['config']['private_key'] ?? '',
        
            // 应用公钥证书路径
            'app_cert_public_key'   => $this->_cache_file('app_cert', $result['config']['app_cert'] ?? ''),
        
            // 支付宝根证书路径
            'alipay_root_cert'      => $this->_cache_file('ali_root_cert', $result['config']['ali_root_cert'] ?? ''),
        
            // 公钥证书
            'ali_public_key'        => $this->_cache_file('ali_public_cert', $result['config']['ali_public_cert'] ?? ''),
        
            // 日志
            'log' => [
                'file' => runtime_path() . 'log/yansongda/log',
            ],
        
            // optional，设置此参数，将进入沙箱模式
            // 'mode' => 'dev',
        ];
    }
    
    function shopWithdrawTransfer($ShopWithdrawal)
    {
        try {
            if ($ShopWithdrawal['left_amount'] > 100000000) {
                return '支付宝在线转账最高金额：100000000';
            }
            
            if ($ShopWithdrawal['left_amount'] < 0.1) {
                return '支付宝在线转账最低金额：0.1';
            }
            
            $order = [
                'out_biz_no'        => $ShopWithdrawal['sn'],
                'product_code'      => 'TRANS_ACCOUNT_NO_PWD',
                'trans_amount'      => $ShopWithdrawal['left_amount'],
                'biz_scene'         => 'DIRECT_TRANSFER',
                'remark'            => '提现到账',
                'payee_info'        => [
                    // 提现人支付宝
                    'identity'          => $ShopWithdrawal['alipay']['account'] ?? '',
                    'identity_type'     => 'ALIPAY_LOGON_ID',
                    // 提现人真实姓名
                    'name'              => $ShopWithdrawal['alipay']['username'] ?? '',
                ]
            ];
    
            $result = \Yansongda\Pay\Pay::alipay($this->config())->transfer($order);
    
            return (isset($result['code']) && $result['code'] == 10000) ? : ($result['sub_msg'] ?? '支付宝提现失败');
        } catch (GatewayException|InvalidSignException|InvalidConfigException|InvalidGatewayException $e) {
            return $e->raw['alipay_fund_trans_uni_transfer_response']['sub_msg'] ?? $e->getMessage();
        } catch (\Throwable $e) {
            return '支付宝提现错误';
        }
    }
}