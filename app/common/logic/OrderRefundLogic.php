<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\common\logic;


use app\common\enum\OrderEnum;
use app\common\enum\OrderGoodsEnum;
use app\common\enum\OrderLogEnum;
use app\common\enum\PayEnum;
use app\common\model\order\Order;
use app\common\model\AccountLog;
use app\common\model\order\Order as CommonOrder;
use app\common\model\order\OrderGoods;
use app\common\model\order\OrderLog;
use app\common\model\Pay;
use app\common\model\user\User;
use app\common\server\AliPayServer;
use app\common\server\DouGong\pay\PayZhengsaoRefund;
use app\common\server\WeChatPayServer;
use app\common\server\WeChatServer;
use think\Exception;
use think\facade\Db;
use think\facade\Event;
use think\facade\Log;

/**
 * 订单退款逻辑
 * Class OrderRefundLogic
 * @package app\common\logic
 */
class OrderRefundLogic
{

    /**
     * Notes:  取消订单
     * @param $order_id
     * @param int $handle_type
     * @param int $handle_id
     * @author 段誉(2021/1/28 15:23)
     * @return Order
     */
    public static function cancelOrder($order_id, $handle_type = OrderLogEnum::TYPE_SYSTEM, $handle_id = 0)
    {
        //更新订单状态
//        $order = order::get($order_id);
        $order = Order::where('id',$order_id)->find();
        $order->order_status = OrderEnum::ORDER_STATUS_DOWN;
        $order->update_time = time();
        $order->cancel_time = time();
        $order->save();

        // 取消订单后的操作
        switch ($handle_type) {
            case OrderLogEnum::TYPE_USER:
                $channel = OrderLogEnum::USER_CANCEL_ORDER;
                break;
            case OrderLogEnum::TYPE_SHOP:
                $channel = OrderLogEnum::SHOP_CANCEL_ORDER;
                break;
            case OrderLogEnum::TYPE_SYSTEM:
                $channel = OrderLogEnum::SYSTEM_CANCEL_ORDER;
                break;
        }

        event('AfterCancelOrder', [
            'type'    => $handle_type,
            'channel'    => $channel,
            'order_id'    => $order->id,
            'handle_id' => $handle_id,
        ]);
    }


    /**
     * @notes 处理订单退款(事务在取消订单逻辑处)
     * @param $order
     * @param $order_amount
     * @param $refund_amount
     * @return bool
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2021/12/20 15:13
     */
    public static function refund($order, $order_amount, $refund_amount)
    {
        //退款记录
        $refund_id = self::addRefundLog($order, $order_amount, $refund_amount);

        if ($refund_amount <= 0) {
            return false;
        }

        switch ($order['pay_way']) {
            //余额退款
            case PayEnum::BALANCE_PAY:
                self::balancePayRefund($order, $refund_amount);
                break;
            //微信退款
            case PayEnum::WECHAT_PAY:
                self::wechatPayRefund($order, $refund_id);
                break;
            //支付宝退款
            case PayEnum::ALI_PAY:
                self::aliPayRefund($order, $refund_id);
                break;
            case PayEnum::HFDG_WECHAT:
            case PayEnum::HFDG_ALIPAY:
                $payZsRefund = new PayZhengsaoRefund([
                    'refund'    => [
                        'id'                => $refund_id,
                        'money'             => $refund_amount,
                    ],
                    'order'     => [
                        'id'                => $order['id'],
                        'transaction_id'    => $order['transaction_id'],
                        'hfdg_params'       => $order['hfdg_params'],
                    ],
                    'from'      => 'order',
                ]);
                $result = $payZsRefund->request()->getRefundResult();
                if ($result['code'] != 1) {
                    throw new \Exception($result['msg']);
                }
                break;
        }

        return true;
    }


    /**
     * Notes: 微信支付退款
     * @param $order mixed (订单信息)
     * @param $refund_id mixed (退款记录id)　
     * @author 段誉(2021/1/27 16:04)
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public static function wechatPayRefund($order, $refund_id)
    {
        $config = WeChatServer::getPayConfigBySource($order['order_source'])['config'];

        if (empty($config)) {
            throw new Exception('请联系管理员设置微信相关配置!');
        }

        if (!isset($config['cert_path']) || !isset($config['key_path'])) {
            throw new Exception('请联系管理员设置微信证书!');
        }
        if (!file_exists($config['cert_path']) || !file_exists($config['key_path'])) {
            throw new Exception('微信证书不存在,请联系管理员!');
        }

        $refund_log = Db::name('order_refund')->where(['id' => $refund_id])->find();
        $total_fee = Db::name('order_trade')->where(['transaction_id' => $order['transaction_id']])->value('order_amount');
        // 单独支付的子订单 父订单未记录transaction_id 使用子订单的金额
        $total_fee = $total_fee ? : $order['order_amount'];
        $data = [
            'transaction_id'    => $order['transaction_id'],
            'refund_sn'         => $refund_log['refund_sn'],
            'total_fee'         => bcmul($total_fee, 100),//订单金额,单位为分
            'refund_fee'        => bcmul($refund_log['refund_amount'], 100),//退款金额
        ];

        $result = WeChatPayServer::refund($config, $data);

        if (isset($result['return_code']) && $result['return_code'] == 'FAIL') {
            throw new Exception($result['return_msg']);
        }

        if (isset($result['err_code_des'])) {
            throw new Exception($result['err_code_des']);
        }

        if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
            $update_data = [
                'wechat_refund_id' => $result['refund_id'] ?? 0,
                'refund_msg' => json_encode($result, JSON_UNESCAPED_UNICODE),
            ];
            //更新退款日志记录
            Db::name('order_refund')->where(['id' => $refund_id])->update($update_data);

        } else {
            throw new Exception('微信支付退款失败');
        }
    }


    /**
     * Notes: 支付宝退款
     * @param $order
     * @param $refund_id
     * @author 段誉(2021/3/23 15:48)
     * @throws Exception
     * @throws \think\exception\PDOException
     */
    public static function aliPayRefund($order, $refund_id)
    {
        $refund_log = Db::name('order_refund')->where(['id' => $refund_id])->find();
        $trade_id   = $order['trade_id'];
        $trade      = Db::name('order_trade')->where(['id' => $trade_id])->find();
        $result     = (new AliPayServer())->refund($trade['t_sn'], $refund_log['refund_amount'], $refund_log['refund_sn']);
        // $result = (array)$result

        if ($result['code'] == '10000' && $result['msg'] == 'Success' && $result['fund_change'] == 'Y') {
            //更新退款日志记录
            $update_data = [ 'refund_msg' => json_encode($result['msg'], JSON_UNESCAPED_UNICODE) ];
            Db::name('order_refund')->where(['id' => $refund_id])->update($update_data);
        } else {
            
            $result = (new AliPayServer())->refund($order['order_sn'], $refund_log['refund_amount'], $refund_log['refund_sn']);
            
            // $result = (array)$result;
            if ($result['code'] == '10000' && $result['msg'] == 'Success' && $result['fund_change'] == 'Y') {
                //更新退款日志记录
                $update_data = [ 'refund_msg' => json_encode($result['msg'], JSON_UNESCAPED_UNICODE) ];
                Db::name('order_refund')->where(['id' => $refund_id])->update($update_data);
            }else{
                throw new Exception('支付宝退款失败');
            }
        }
    }


    /**
     * Notes: 增加退款记录
     * @param $order
     * @param $order_amount
     * @param $refund_amount
     * @param string $result_msg
     * @author 段誉(2021/1/28 15:23)
     * @return int|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function addRefundLog($order, $order_amount, $refund_amount, $result_msg = '退款成功')
    {
        $data = [
            'order_id' => $order['id'],
            'user_id' => $order['user_id'],
            'refund_sn' => createSn('order_refund', 'refund_sn'),
            'order_amount' => $order_amount,
            'refund_amount' => $refund_amount,
            'transaction_id' => $order['transaction_id'],
            'create_time' => time(),
            'refund_status' => 1,
            'refund_at' => time(),
            'refund_msg' => json_encode($result_msg, JSON_UNESCAPED_UNICODE),
        ];
        return Db::name('order_refund')->insertGetId($data);
    }


    /**
     * Notes: 取消订单,退款后更新订单和订单商品信息
     * @param $order
     * @author 段誉(2021/1/28 14:21)
     * @throws Exception
     * @throws \think\exception\PDOException
     */
    public static function cancelOrderRefundUpdate($order)
    {
        //订单商品=>标记退款成功状态
        Db::name('order_goods')
            ->where(['order_id' => $order['id']])
            ->update(['refund_status' => OrderGoodsEnum::REFUND_STATUS_SUCCESS]);

        //更新订单支付状态为已退款
        Db::name('order')->where(['id' => $order['id']])->update([
            'pay_status' => PayEnum::REFUNDED,
            'refund_status' => OrderEnum::REFUND_STATUS_ALL_REFUND,//订单退款状态; 0-未退款；1-部分退款；2-全部退款
            'refund_amount' => $order['order_amount'],
            'is_cancel' => OrderEnum::ORDER_CANCEL,
        ]);
    }


    /**
     * Notes:售后退款更新订单或订单商品状态
     * @param $order
     * @param $order_goods_id
     * @author 段誉(2021/1/28 15:22)
     */
    public static function afterSaleRefundUpdate($order, $order_goods_id, $admin_id = 0)
    {
        $order_goods = OrderGoods::find(['id' => $order_goods_id]);
        $order_goods->refund_status = OrderGoodsEnum::REFUND_STATUS_SUCCESS;//退款成功
        $order_goods->save();

        //更新订单状态
        $order = Order::find(['id' => $order['id']]);
        $order->pay_status = PayEnum::REFUNDED;
        $order->refund_amount += $order_goods['total_pay_price'];//退款金额 + 以前的退款金额
        $order->refund_status = 1;//退款状态：0-未退款；1-部分退款；2-全部退款

        //如果订单商品已全部退款
        if (self::checkOrderGoods($order['id'])) {
            $order->order_status = CommonOrder::STATUS_CLOSE;
            $order->refund_status = 2;

            OrderLogLogic::record(
                OrderLogEnum::TYPE_SHOP,
                OrderLogEnum::SYSTEM_CANCEL_ORDER,
                $order['id'],
                $admin_id,
                OrderLogEnum::getLogDesc(OrderLogEnum::SYSTEM_CANCEL_ORDER)
            );
        }
        $order->save();
    }


    //订单内商品是否已全部
    public static function checkOrderGoods($order_id)
    {
        $order_goods = OrderGoods::where('order_id', $order_id)->select();
        if (empty($order_goods)) {
            return false;
        }

        foreach ($order_goods as $item) {
            if ($item['refund_status'] != OrderGoodsEnum::REFUND_STATUS_SUCCESS) {
                return false;
            }
        }
        return true;
    }


    /**
     * Notes: 余额退款
     * @param $order
     * @param $refund_amount
     * @author 段誉(2021/1/28 15:24)
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function balancePayRefund($order, $refund_amount)
    {
        $user = User::find($order['user_id']);
        $user->user_money = ['inc', $refund_amount];
        $user->save();

        AccountLogLogic::AccountRecord(
            $order['user_id'],
            $refund_amount,
            1,
            AccountLog::cancel_order_refund,
            '',
            $order['id'],
            $order['order_sn']
        );
        return true;
    }


    /**
     * Notes: 退款失败增加错误记录
     * @param $order
     * @param $err_msg
     * @author 段誉(2021/1/28 15:24)
     * @return int|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function addErrorRefund($order, $err_msg)
    {
        $refund_data = [
            'order_id' => $order['id'],
            'user_id' => $order['user_id'],
            'refund_sn' => createSn('order_refund', 'refund_sn'),
            'order_amount' => $order['order_amount'],//订单应付金额
            'refund_amount' => $order['order_amount'],//订单退款金额
            'transaction_id' => $order['transaction_id'],
            'create_time' => time(),
            'refund_status' => 2,
            'refund_msg' => json_encode($err_msg, JSON_UNESCAPED_UNICODE),
        ];
        return Db::name('order_refund')->insertGetId($refund_data);
    }

}