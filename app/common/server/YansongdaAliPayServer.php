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

class YansongdaAliPayServer
{
    
    
    static function _cache_file($key,$content, $ext = 'crt') : string
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
    
    static function config()
    {
        $result = (new Pay())->where([ 'code' => 'alipay' ])->find();
        
        return [
    
            // 支付宝异步通知地址
            'notify_url' => (string) url('pay/aliNotify', [], false, true),
    
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
            'app_cert_public_key'   => static::_cache_file('app_cert', $result['config']['app_cert'] ?? ''),
        
            // 支付宝根证书路径
            'alipay_root_cert'      => static::_cache_file('ali_root_cert', $result['config']['ali_root_cert'] ?? ''),
        
            // 公钥证书
            'ali_public_key'        => static::_cache_file('ali_public_cert', $result['config']['ali_public_cert'] ?? ''),
        
            // 日志
            'log' => [
                'file' => runtime_path() . 'log/yansongda/log',
            ],
        
            // optional，设置此参数，将进入沙箱模式
            // 'mode' => 'dev',
        ];
    }
}