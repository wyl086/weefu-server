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

use app\common\enum\IntegralOrderEnum;
use app\common\enum\PayEnum;
use app\common\logic\IntegralOrderRefundLogic;
use app\common\logic\PayNotifyLogic;
use app\common\model\integral\IntegralOrder;
use app\common\model\order\Order;
use app\common\model\order\OrderTrade;
use app\common\model\RechargeOrder;
use app\common\server\ConfigServer;
use think\facade\Log;

class BaseAsync
{
    protected $async_result     = [];
    protected $async_success    = false;
    protected $async_message    = '异步信息处理失败';
    
    protected $config;
    protected $params;
    
    function __construct(array $result, array $params = [])
    {
        $this->async_result = $result;
        $this->params       = $params;
    
        $this->initAdminConfig();
    }
    
    private function initAdminConfig()
    {
        $adminConfig = ConfigServer::get('hfdg_dev_set');
        
        $this->config['rsa_huifu_public_key']   = $adminConfig['rsa_huifu_public_key'] ?? '';
    }
    
    function checkAsync()
    {
        $data = $this->async_result;
        
        try {
            if (! isset($data['resp_data']) || ! isset($data['sign'])) {
                return $this;
            }
            if (! BaseFunc::verifySign_sort($data['sign'], $data['resp_data'], $this->config['rsa_huifu_public_key'])) {
                Log::write('验签失败', 'hfdg_async_data');
                return $this;
            }
    
            Log::write($data, 'hfdg_async_data');
    
            $data['resp_data'] = (array) json_decode($this->async_result['resp_data'], true);
            
            // 00000000 受理成功 00000100 下单成功
            $this->async_success  = in_array($data['resp_data']['resp_code'], [ '00000000', '00000100' ]);
            $this->async_message  = $data['resp_data']['resp_desc'];
            
        } catch(\Throwable $e) {
            $this->async_message = $e->getMessage();
            Log::write($e->__toString(), 'hfdg_async_error');
        }
        
        return $this;
    }
    
    
    
    function getCheckSuccess()
    {
        return $this->async_success;
    }
}