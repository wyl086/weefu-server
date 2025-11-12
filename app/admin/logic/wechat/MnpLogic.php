<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\admin\logic\wechat;

use app\common\basics\Logic;
use app\common\server\ConfigServer;
use app\common\server\UrlServer;
use app\common\server\WeChatServer;
use EasyWeChat\Factory;
use EasyWeChat\Kernel\Exceptions\Exception;

class MnpLogic extends Logic
{
    /**
     * 获取小程序配置
     */
    public static function getMnp()
    {
        $domain_name = $_SERVER['SERVER_NAME'];
        $qr_code = ConfigServer::get('mnp', 'qr_code', '');
        $config = [
            'name'                  => ConfigServer::get('mnp', 'name', ''),
            'original_id'           => ConfigServer::get('mnp', 'original_id', ''),
            'qr_code'               => $qr_code,
            'abs_qr_code'           => UrlServer::getFileUrl($qr_code),
            'app_id'                => ConfigServer::get('mnp', 'app_id', ''),
            'app_secret'            => ConfigServer::get('mnp', 'secret', ''),
            'request_domain'        => 'https://'.$domain_name,
            'socket_domain'         => 'wss://'.$domain_name,
            'uploadfile_domain'     => 'https://'.$domain_name,
            'downloadfile_domain'   => 'https://'.$domain_name,
            'udp_domain'            => 'udp://'.$domain_name,
            'tcp_domain'            => 'tcp://'.$domain_name,
            'business_domain'       => $domain_name,
            'url'                   => url('api/wechat/index',[],'',true),
            'token'                 => ConfigServer::get('mnp', 'token', 'LikeMall'),
            'encoding_ses_key'      => ConfigServer::get('mnp', 'encoding_ses_key', ''),
            'encryption_type'       => ConfigServer::get('mnp', 'encryption_type', ''),
            'data_type'             => ConfigServer::get('mnp', 'data_type', ''),
            
            // 小程序同步发货开关 1开启 0关闭
            'express_send_sync'     => ConfigServer::get('mnp', 'express_send_sync', 1),
        ];
        return $config;
    }

    public static function setMnp($post)
    {
        if($post){
            $encryption_type  = $post['encryption_type'] ?? '';
            $data_type  = $post['data_type'] ?? '';

            ConfigServer::set('mnp','name',$post['name']);
            ConfigServer::set('mnp','original_id',$post['original_id']);
            ConfigServer::set('mnp','qr_code', $post['qr_code']);
            ConfigServer::set('mnp','app_id',$post['app_id']);
            ConfigServer::set('mnp','secret',$post['app_secret']);
            ConfigServer::set('mnp','token',$post['token']);
            ConfigServer::set('mnp','encoding_ses_key',$post['encoding_ses_key']);
            ConfigServer::set('mnp','encryption_type', $encryption_type);
            ConfigServer::set('mnp','data_type', $data_type);
    
    
            ConfigServer::set('mnp','express_send_sync', $post['express_send_sync'] ?? 1);
        }
        return true;
    }
}