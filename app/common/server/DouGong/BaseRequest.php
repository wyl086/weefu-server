<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\common\server\DouGong;

use Alipay\EasySDK\Kernel\Base;
use app\common\enum\PayEnum;
use app\common\model\Pay;
use app\common\model\user\User;
use app\common\model\user\UserAuth;
use app\common\server\ConfigServer;
use app\common\server\WeChatServer;
use Requests;
use think\facade\Log;

/**
 * @notes 请求
 * 文档 https://paas.huifu.com/partners/api/#/
 * author lbzy
 * @datetime 2023-09-28 09:27:16
 * @class Request
 * @package app\common\server\DouGong
 */
abstract class BaseRequest
{
    protected $params;
    protected $userInfo;
    protected $userAuth;
    protected $config;
    
    protected $request_host = 'https://api.huifu.com';
    protected $request_uri;
    protected $request_data = [
        'sys_id'        => '',
        'product_id'    => '',
        'data'          => [],
        // 'sign'          => '',
    ];
    protected $request_result   = [];
    protected $request_success  = false;
    protected $request_message  = '';
    
    /**
     * @param array $params 参数
     * $params['pay_way'] 支付方式
     * $params['client'] 用户client
     * $params['form'] 订单类型 trade：商城总订单 order：商城订单 recharge：充值订单 integral：积分订单
     * $params['order_id'] 订单id
     * $params['user_id'] 用户id
     */
    function __construct(array $params)
    {
        $this->params = $params;
    
        $this->initAdminConfig();
        
        $this->initBaseParams();
        
        $this->initialize();
    }
    
    protected function initialize(){}
    
    protected function initBaseParams()
    {
        $this->request_data['sys_id']       = $this->config['sys_id'];
        $this->request_data['product_id']   = $this->config['product_id'];
        // $this->request_data['huifu_id']     = $this->config['huifu_id'];
    }
    
    function initAdminConfig()
    {
        $adminConfig = ConfigServer::get('hfdg_dev_set');
        
        $this->config['sys_id']                 = $adminConfig['sys_id'] ?? '';
        $this->config['product_id']             = $adminConfig['product_id'] ?? '';
        $this->config['huifu_id']               = $adminConfig['huifu_id'] ?? '';
        $this->config['rsa_merch_private_key']  = $adminConfig['rsa_merch_private_key'] ?? '';
        $this->config['rsa_merch_public_key']   = $adminConfig['rsa_merch_public_key'] ?? '';
        $this->config['rsa_huifu_public_key']   = $adminConfig['rsa_huifu_public_key'] ?? '';
    }
    
    protected function beforeRequestCheck() : bool
    {
        return true;
    }
    
    function request(): BaseRequest
    {
        try {
            
            if (! $this->beforeRequestCheck()) {
                throw new \Exception($this->request_message);
            }
            
            $data = $this->request_data;
    
            $data['data'] = BaseFunc::get_data_json($data['data']);
            
            $data['sign'] = BaseFunc::sha_with_rsa_sign($data['data'], $this->config['rsa_merch_private_key']);
            
            $result = Requests::post($this->request_host . $this->request_uri, [
                'Content-Type'  => 'application/json',
                'charset'       => 'UTF-8',
            ], BaseFunc::json($data));
    
            $this->request_result = (array) json_decode($result->body, true);
    
            if (app()->isDebug()) {
                Log::write($this->request_result, 'hfdg_request_result');
                Log::write($result->body, 'hfdg_request_result_body');
                Log::write($this->params, 'hfdg_request_params');
            }
        } catch(\Throwable $e) {
            $this->request_message = $e->getMessage();
            Log::write($e->__toString(), 'hfdg_request_error');
        }
        
        $this->checkRequestResult();
        
        return $this;
    }
    
    private function checkRequestResult()
    {
        if (! isset($this->request_result['data']['resp_code'])) {
            $this->request_success  = false;
            $this->request_message  = $this->request_message ? : '请求失败';
        } else {
            // 00000000 受理成功 00000100 下单成功
            $this->request_success  = in_array($this->request_result['data']['resp_code'], [ '00000000', '00000100' ]);
            $this->request_message  = $this->request_result['data']['resp_desc'];
            if (! $this->request_success) {
                Log::write($this->request_result, 'hfdg_request_result');
            }
        }
    }
    
    function getRequestResult()
    {
        return $this->request_result;
    }
}