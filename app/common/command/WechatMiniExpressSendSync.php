<?php

namespace app\common\command;

use app\common\enum\OrderEnum;
use app\common\enum\PayEnum;
use app\common\model\order\Order;
use app\common\model\RechargeOrder;
use app\common\server\ConfigServer;
use app\common\server\WechatMiniExpressSendSyncServer;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class WechatMiniExpressSendSync extends Command
{
    protected function configure()
    {
        $this->setName('wechat_mini_express_send_sync')->setDescription('微信小程序发货同步');
    }
    
    protected function execute(Input $input, Output $output)
    {
        // 未开启发货同步
        if (! ConfigServer::get('mnp', 'express_send_sync', 1)) {
            return ;
        }
        
        // 订单
        static::order();
        // 用户充值
        static::user_recharge();
    }
    
    private static function order()
    {
        // 快递
        $list = Order::where('delivery_type', OrderEnum::DELIVERY_TYPE_EXPRESS)
            ->where('shipping_status', 1)
            ->where('pay_status', 1)
            ->where('pay_way', PayEnum::WECHAT_PAY)
            ->where('wechat_mini_express_sync', 0)
            ->where('order_status', 'in', [ OrderEnum::ORDER_STATUS_GOODS, OrderEnum::ORDER_STATUS_COMPLETE ])
            ->limit(60)
            ->order('id desc')
            ->select()->toArray();
        // 自提
        $list2 = Order::where('delivery_type', OrderEnum::DELIVERY_TYPE_SELF)
            ->where('pay_status', 1)
            ->where('pay_way', PayEnum::WECHAT_PAY)
            ->where('wechat_mini_express_sync', 0)
            ->where('order_status', 'in', [ OrderEnum::ORDER_STATUS_DELIVERY, OrderEnum::ORDER_STATUS_GOODS, OrderEnum::ORDER_STATUS_COMPLETE ])
            ->limit(20)
            ->order('id desc')
            ->select()->toArray();
        // 虚拟发货
        $list3 = Order::where('delivery_type', OrderEnum::DELIVERY_TYPE_VIRTUAL)
            ->where('pay_status', 1)
            ->where('pay_way', PayEnum::WECHAT_PAY)
            ->where('wechat_mini_express_sync', 0)
            ->where('order_status', 'in', [ OrderEnum::ORDER_STATUS_GOODS, OrderEnum::ORDER_STATUS_COMPLETE ])
            ->limit(20)
            ->order('id desc')
            ->select()->toArray();
        // dump([ $list, $list2, $list3 ]);
    
        foreach ([ $list, $list2, $list3 ] as $items) {
            foreach ($items as $item) {
                WechatMiniExpressSendSyncServer::_sync_order($item);
            }
        }
    }
    
    private static function user_recharge()
    {
        $list = RechargeOrder::where('pay_status', 1)
            ->where('pay_way', PayEnum::WECHAT_PAY)
            ->where('wechat_mini_express_sync', 0)
            ->limit(60)
            ->order('id desc')
            ->select()->toArray();
        // dump($list);
        
        foreach ($list as $item) {
            WechatMiniExpressSendSyncServer::_sync_recharge($item);
        }
    }
    
}