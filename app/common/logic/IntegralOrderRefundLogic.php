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


use app\common\enum\IntegralGoodsEnum;
use app\common\enum\IntegralOrderEnum;
use app\common\enum\PayEnum;
use app\common\model\integral\IntegralGoods;
use app\common\model\integral\IntegralOrder;
use app\common\model\AccountLog;
use app\common\model\integral\IntegralOrderRefund;
use app\common\model\user\User;
use app\common\server\AliPayServer;
use app\common\server\DouGong\pay\PayZhengsaoRefund;
use app\common\server\WeChatPayServer;
use app\common\server\WeChatServer;
use think\Exception;

/**
 * 积分订单退款逻辑
 * Class OrderRefundLogic
 * @package app\common\logic
 */
class IntegralOrderRefundLogic
{

    /**
     * @notes 取消订单(标记订单状态,退回库存,扣减销量)
     * @param int $order_id
     * @author 段誉
     * @date 2022/3/3 11:01
     */
    public static function cancelOrder(int $order_id)
    {
        // 订单信息
        $order = IntegralOrder::findOrEmpty($order_id);
        $order->cancel_time = time();
        $order->order_status = IntegralOrderEnum::ORDER_STATUS_DOWN;
        $order->save();

        // 订单商品信息
        $goods_snap = $order['goods_snap'];
        // 退回库存, 扣减销量
        IntegralGoods::where([['id', '=', $goods_snap['id']], ['sales', '>=', $order['total_num']]])
            ->inc('stock', $order['total_num'])
            ->dec('sales', $order['total_num'])
            ->update();
    }


    /**
     * @notes 退回已支付金额
     * @param int $order_id
     * @return bool
     * @throws Exception
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/3/3 16:00
     */
    public static function refundOrderAmount(int $order_id)
    {
        // 订单信息
        $order = IntegralOrder::findOrEmpty($order_id);
        // 订单商品信息
        $goods_snap = $order['goods_snap'];

        //已支付的商品订单,取消,退款
        if ($goods_snap['type'] == IntegralGoodsEnum::TYPE_GOODS
            && $order['refund_status'] == IntegralOrderEnum::NO_REFUND
        ) {
            if ($order['order_amount'] <= 0) {
               return true;
            }
            // 更新订单退款状态为已退款
            IntegralOrder::where(['id' => $order['id']])->update([
                'refund_status' => IntegralOrderEnum::IS_REFUND,//订单退款状态; 0-未退款；1-已退款
                'refund_amount' => $order['order_amount'],
            ]);

            // 发起退款
            $refund_log = self::addRefundLog($order, $order['order_amount'], 1, $order['order_amount']);

            switch ($order['pay_way']) {
                //余额退款
                case PayEnum::BALANCE_PAY:
                    self::balancePayRefund($order, $order['order_amount']);
                    break;
                //微信退款
                case PayEnum::WECHAT_PAY:
                    self::wechatPayRefund($order, $refund_log);
                    break;
                //支付宝退款
                case PayEnum::ALI_PAY:
                    self::aliPayRefund($order, $refund_log);
                    break;
                case PayEnum::HFDG_WECHAT:
                case PayEnum::HFDG_ALIPAY:
                    $payZsRefund = new PayZhengsaoRefund([
                        'refund'    => [
                            'id'                => $refund_log['id'],
                            'money'             => $order['order_amount'],
                        ],
                        'order'     => [
                            'id'                => $order['id'],
                            'transaction_id'    => $order['transaction_id'],
                            'hfdg_params'       => $order['hfdg_params'],
                        ],
                        'from'      => 'integral',
                    ]);
                    $result = $payZsRefund->request()->getRefundResult();
                    if ($result['code'] != 1) {
                        throw new \Exception($result['msg']);
                    }
                    break;
            }
        }
        return true;
    }


    /**
     * @notes 退回已支付积分
     * @param $id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/3/3 16:02
     */
    public static function refundOrderIntegral($id)
    {
        $order = IntegralOrder::findOrEmpty($id);
        if ($order['order_integral'] > 0) {
            // 退回积分
            User::where(['id' => $order['user_id']])
                ->inc('user_integral', $order['order_integral'])
                ->update();

            AccountLogLogic::AccountRecord(
                $order['user_id'],
                $order['order_integral'], 1,
                AccountLog::cancel_integral_order,
                '', $order['id'], $order['order_sn']
            );

            IntegralOrder::where(['id' => $id])->update([
                'refund_integral' => $order['order_integral']
            ]);
        }
        return true;
    }



    /**
     * @notes 增加退款记录
     * @param $order
     * @param $refund_amount
     * @param $status
     * @param string $msg
     * @return IntegralOrderRefund|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/3/3 14:51
     */
    public static function addRefundLog($order, $refund_amount, $status, $msg = '')
    {
        return IntegralOrderRefund::create([
            'order_id' => $order['id'],
            'user_id' => $order['user_id'],
            'refund_sn' => createSn('order_refund', 'refund_sn'),
            'order_amount' => $order['order_amount'],
            'refund_amount' => $refund_amount,
            'transaction_id' => $order['transaction_id'],
            'create_time' => time(),
            'refund_status' => $status,
            'refund_at' => time(),
            'refund_msg' => json_encode($msg, JSON_UNESCAPED_UNICODE),
        ]);
    }


    /**
     * @notes 微信支付退款
     * @param $order
     * @param $refund_id
     * @throws Exception
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @author 段誉
     * @date 2022/3/3 14:52
     */
    public static function wechatPayRefund($order, $refund)
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

        $result = WeChatPayServer::refund($config, [
            'transaction_id' => $order['transaction_id'],
            'refund_sn' => $refund['refund_sn'],
            'total_fee' => intval(strval($refund['order_amount'] * 100)),//订单金额,单位为分
            'refund_fee' => intval(strval($refund['refund_amount'] * 100)),//退款金额
        ]);

        if (isset($result['return_code']) && $result['return_code'] == 'FAIL') {
            throw new Exception($result['return_msg']);
        }
        if (isset($result['err_code_des'])) {
            throw new Exception($result['err_code_des']);
        }
        if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
            //更新退款日志记录
            IntegralOrderRefund::where(['id' => $refund['id']])->update([
                'wechat_refund_id' => $result['refund_id'] ?? 0,
                'refund_msg' => json_encode($result, JSON_UNESCAPED_UNICODE),
            ]);
        } else {
            throw new Exception('微信支付退款失败');
        }
    }


    /**
     * @notes 支付宝退款
     * @param $order
     * @param $refund_id
     * @throws Exception
     * @author 段誉
     * @date 2022/3/3 14:52
     */
    public static function aliPayRefund($order, $refund)
    {
        $result = (new AliPayServer())->refund($order['order_sn'], $order['order_amount']);
        // $result = (array)$result;
        if ($result['code'] == '10000' && $result['msg'] == 'Success' && $result['fund_change'] == 'Y') {
            //更新退款日志记录
            IntegralOrderRefund::where(['id' => $refund])->update([
                'refund_msg' => json_encode($result, JSON_UNESCAPED_UNICODE),
            ]);
        } else {
            throw new Exception('支付宝退款失败');
        }
    }



    /**
     * @notes 余额退款
     * @param $order
     * @param $refund_amount
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/3/3 14:52
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



}