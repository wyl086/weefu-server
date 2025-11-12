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

use app\common\model\Client_;
use app\common\model\Pay;
use think\Exception;

/**
 * 微信服务 服务类
 * Class WeChatServer
 * @package app\common\server
 */
class WeChatServer
{
    /**
     * @notes 获取小程序配置
     * @return array
     * @author suny
     * @date 2021/7/13 6:35 下午
     */
    public static function getMnpConfig()
    {

        $config = [
            'app_id' => ConfigServer::get('mnp', 'app_id'),
            'secret' => ConfigServer::get('mnp', 'secret'),
            'mch_id' => ConfigServer::get('mnp', 'mch_id'),
            'key' => ConfigServer::get('mnp', 'key'),
            'response_type' => 'array',
            'log' => [
                'level' => 'debug',
                'file' => app()->getRootPath() . 'runtime/wechat/' . date('Ym') . '/' . date('d') . '.log'
            ],
        ];
        return $config;
    }

    /**
     * @notes 获取微信公众号配置
     * @return array
     * @author suny
     * @date 2021/7/13 6:35 下午
     */
    public static function getOaConfig()
    {

        $config = [
            'app_id' => ConfigServer::get('oa', 'app_id'),
            'secret' => ConfigServer::get('oa', 'secret'),
            'mch_id' => ConfigServer::get('oa', 'mch_id'),
            'key' => ConfigServer::get('oa', 'key'),
            'token' => ConfigServer::get('oa', 'token', ''),
            'response_type' => 'array',
            'log' => [
                'level' => 'debug',
                'file' => app()->getRootPath() . 'runtime/wechat/' . date('Ym') . '/' . date('d') . '.log'
            ],
        ];
        return $config;
    }


    //微信开放平台->web应用
    public static function getOpWebConfig()
    {
        $config = [
            'app_id'   => ConfigServer::get('op', 'web_app_id'),
            'secret'   => ConfigServer::get('op', 'web_secret'),
        ];
        return $config;
    }


    /**
     * @notes 获取url
     * @param $str
     * @return string
     * @author suny
     * @date 2021/7/13 6:35 下午
     */
    public static function getUrl($str)
    {

        return (string)url($str, [], false, true);
    }

    /**
     * @notes 根据不同来源获取支付配置
     * @param $order_source
     * @return array
     * @throws Exception
     * @author suny
     * @date 2021/7/13 6:36 下午
     */
    public static function getPayConfigBySource($order_source)
    {

        $notify_url = '';
        switch ($order_source) {
            case Client_::mnp:
                $notify_url = self::getUrl('pay/notifyMnp');
                break;
            case Client_::oa:
            case Client_::h5:
            case Client_::pc:
                $notify_url = self::getUrl('pay/notifyOa');
                break;
            case Client_::android:
            case Client_::ios:
                $notify_url = self::getUrl('pay/notifyApp');
                break;
        }
        $config = self::getPayConfig($order_source);
        if (empty($config) ||
            empty($config['key']) ||
            empty($config['mch_id']) ||
            empty($config['app_id']) ||
            empty($config['secret'])
        ) {
            throw new Exception('请在后台配置好微信支付！');
        }

        return [
            'config' => $config,
            'notify_url' => $notify_url,
        ];
    }

    //===================================支付配置=======================================================

    /**
     * @notes 微信支付设置 H5支付 appid 可以是公众号appid
     * @param $client
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/13 6:36 下午
     */
    public static function getPayConfig($client)
    {

        switch ($client) {
            case Client_::mnp:
                $appid = ConfigServer::get('mnp', 'app_id');
                $secret = ConfigServer::get('mnp', 'secret');
                break;
            case Client_::oa:
            case Client_::h5:
            case Client_::pc:
                $appid = ConfigServer::get('oa', 'app_id');
                $secret = ConfigServer::get('oa', 'secret');
                break;
            case Client_::android:
            case Client_::ios:
                $appid = ConfigServer::get('op', 'app_id');
                $secret = ConfigServer::get('op', 'secret');
                break;
            default:
                $appid = '';
                $secret = '';
        }

        $pay = Pay::where(['code' => 'wechat'])->find()->toArray();

        $config = [
            'app_id' => $appid,
            'secret' => $secret,
            'mch_id' => $pay['config']['mch_id'] ?? '',
            'key' => $pay['config']['pay_sign_key'] ?? '',
            'cert_path' => $pay['config']['apiclient_cert'] ?? '',
            'key_path' => $pay['config']['apiclient_key'] ?? '',
            'response_type' => 'array',
            'log' => [
                'level' => 'debug',
                'file' => app()->getRootPath() . 'runtime/wechat/' . date('Ym') . '/' . date('d') . '.log'
            ],
        ];

        if (is_cli()) {
            if (!defined('ROOT_PATH')) {
                define('ROOT_PATH', __DIR__);
            }
            $config['cert_path'] = ROOT_PATH . '/public/' . $pay['config']['apiclient_cert'];
            $config['key_path'] = ROOT_PATH . '/public/' . $pay['config']['apiclient_key'];
        }

        return $config;
    }
}