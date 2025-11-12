<?php

namespace app\api\controller;

use app\api\logic\PayLogic;
use app\common\basics\Api;
use app\common\enum\OrderEnum;
use app\common\enum\PayEnum;
use app\common\model\Client_;
use app\common\model\order\OrderTrade;
use app\common\model\order\Order;
use app\common\model\order\OrderLog;
use app\common\model\RechargeOrder;
use app\common\server\AliPayServer;
use app\common\server\DouGong\BaseAsync;
use app\common\server\DouGong\pay\PayZhengSao;
use app\common\server\JsonServer;
use app\common\model\Test;
use app\common\server\WeChatPayServer;
use app\common\server\WeChatServer;
use app\common\model\integral\IntegralOrder;
use think\facade\Log;

/**
 * Class Pay
 * @package app\api\controller
 */
class Pay extends Api
{
    public $like_not_need_login = [ 'notifyMnp', 'notifyOa', 'notifyApp', 'aliNotify', 'hfdgPayWechatNotify', 'hfdgPayAlipayNotify' ];

    /**
     * @notes 支付入口
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/13 6:13 下午
     */
    public function unifiedpay()
    {
        $post = $this->request->post();
        if(!isset($post['pay_way'])) {
            return JsonServer::error('请选择支付方式');
        }
    
        $pay_way = $post['pay_way'];
        
        // 订单
        if ($post['from'] == 'order') {
            // 更新支付方式
            $order = Order::findOrEmpty($post['order_id']);
            Order::where('id', $post['order_id'])->update([ 'pay_way' => $pay_way ]);
        }
        // 总订单
        if ($post['from'] == 'trade') {
            $order = OrderTrade::findOrEmpty($post['order_id']);
            // 更新支付方式
            Order::where('trade_id', $post['order_id'])->update([ 'pay_way' => $pay_way ]);
        }
        // 充值订单
        if ($post['from'] == 'recharge') {
            $order = RechargeOrder::findOrEmpty($post['order_id']);
            // 更新支付方式
            RechargeOrder::where('id', $post['order_id'])->update([ 'pay_way' => $pay_way ]);
        }

        // 积分订单
        if ($post['from'] == 'integral') {
            $order = IntegralOrder::findOrEmpty($post['order_id']);
            // 更新支付方式
            IntegralOrder::where('id', $post['order_id'])->update([ 'pay_way' => $pay_way ]);
        }

        // order,trade方式金额为0直接走余额支付
        if (isset($order) && $order['order_amount'] == 0) {
            return PayLogic::balancePay($post['order_id'], $post['from']);
        }
        
        switch ($pay_way) {
            case PayEnum::BALANCE_PAY://余额支付
                $result = PayLogic::balancePay($post['order_id'], $post['from']);
                break;
            case PayEnum::WECHAT_PAY://微信支付
                $result = PayLogic::wechatPay($post['order_id'], $post['from'], $this->client);
                break;
            case PayEnum::ALI_PAY://支付宝支付
                $result = PayLogic::aliPay($post['order_id'], $post['from'],$this->client);
    
                if (app()->isDebug()) {
                    Log::write($result, 'unifiedpay');
                }
                
                $data = [
                    'code' => 10001,
                    'msg' => '发起成功',
                    'data' => $result,
                    'show' => 0,
                ];
                return json($data);
            // 汇付斗拱 微信 支付宝
            case PayEnum::HFDG_WECHAT:
            case PayEnum::HFDG_ALIPAY:
                $result = (new PayZhengSao([
                    'pay_way'           => $pay_way,
                    'client'            => $this->client,
                    'from'              => $post['from'],
                    'order_id'          => $post['order_id'],
                    'user_id'           => $this->user_id,
                    'order'             => $order,
                ]))->request()->getPayResult();
                
                if (app()->isDebug()) {
                    Log::write($result, 'unifiedpay');
                }
                
                return json($result);
        }

        return $result;

    }



    /**
     * @notes 小程序回调
     * @throws \EasyWeChat\Kernel\Exceptions\Exception
     * @author suny
     * @date 2021/7/13 6:13 下午
     */
    public function notifyMnp()
    {

        $config = WeChatServer::getPayConfig(Client_::mnp);
        return WeChatPayServer::notify($config);
    }


    /**
     * @notes 公众号回调
     * @throws \EasyWeChat\Kernel\Exceptions\Exception
     * @author suny
     * @date 2021/7/13 6:13 下午
     */
    public function notifyOa()
    {

        $config = WeChatServer::getPayConfig(Client_::oa);
        return WeChatPayServer::notify($config);
    }


    /**
     * @notes APP回调
     * @throws \EasyWeChat\Kernel\Exceptions\Exception
     * @author suny
     * @date 2021/7/13 6:14 下午
     */
    public function notifyApp()
    {

        $config = WeChatServer::getPayConfig(Client_::ios);
        return WeChatPayServer::notify($config);
    }



    /**
     * @notes 支付宝回调
     * @return bool
     * @author suny
     * @date 2021/7/13 6:14 下午
     */
    public function aliNotify()
    {
        $data = $this->request->post();
        $result = (new AliPayServer())->verifyNotify($data);
        if (true === $result) {
            echo 'success';
        } else {
            echo 'fail';
        }
    }
    
    function hfdgPayWechatNotify()
    {
        $data = input();
        
        $async = new BaseAsync($data);
    
        $async->checkAsync();
        
        if ($async->getCheckSuccess()) {
            PayZhengSao::asyncSuccessDeal($data);
        }
        
        return $async->getCheckSuccess() ? 'success' : 'failed';
    }
    
    function hfdgPayAlipayNotify()
    {
        $data = input();
    
        $async = new BaseAsync($data);
    
        $async->checkAsync();
    
        if ($async->getCheckSuccess()) {
            PayZhengSao::asyncSuccessDeal($data);
        }
    
        return $async->getCheckSuccess() ? 'success' : 'failed';
    }
}