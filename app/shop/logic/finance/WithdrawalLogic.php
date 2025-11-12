<?php


namespace app\shop\logic\finance;


use app\common\basics\Logic;
use app\common\enum\NoticeEnum;
use app\common\enum\OrderEnum;
use app\common\enum\ShopEnum;
use app\common\enum\PayEnum;
use app\common\enum\OrderRefundEnum;
use app\common\enum\ShopWithdrawEnum;
use app\common\enum\WithdrawalEnum;
use app\common\enum\AfterSaleEnum;
use app\common\model\order\Order;
use app\common\model\shop\Shop;
use app\common\model\shop\ShopAccountLog;
use app\common\model\shop\ShopAlipay;
use app\common\model\shop\ShopBank;
use app\common\model\shop\ShopWithdrawal;
use app\common\model\shop\ShopSettlement;
use app\common\server\ConfigServer;
use app\common\server\ExportExcelServer;
use think\Exception;
use think\facade\Db;

class WithdrawalLogic extends Logic
{
    /**
     * @Notes: 商家提现列表
     * @Author: 张无忌
     * @param $get
     * @param $shop_id
     * @return array
     */
    public static function lists($get, $shop_id, $is_export = false)
    {
        try {
            $where[] = ['shop_id', '=', $shop_id];
            $where[] = ['status', '=', $get['type'] ?? 0];

            if (!empty($get['start_time']) and $get['start_time']) {
                $where[] = ['create_time', '>=', strtotime($get['start_time'])];
            }

            if (!empty($get['end_time']) and $get['end_time']) {
                $where[] = ['create_time', '<=', strtotime($get['end_time'])];
            }

            // 导出
            if (true === $is_export) {
                return self::export($where);
            }

            $model = new ShopWithdrawal();
            $lists = $model->field(true)
                ->where($where)
                ->order('id desc')
                ->paginate([
                    'page'      => $get['page'],
                    'list_rows' => $get['limit'],
                    'var_page' => 'page'
                ])->toArray();

            foreach ($lists['data'] as &$item) {
                $item['status'] = WithdrawalEnum::getStatusDesc($item['status']);
            }

            return ['count' => $lists['total'], 'lists' => $lists['data']];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * @Notes: 财务汇总
     * @Author: 张无忌
     * @param $shop_id
     */
    public static function summary($shop_id)
    {
        $orderModel = new Order();

        // 成交订单笔数
        $detail['dealOrderCount'] = $orderModel->where(['shop_id' => $shop_id, 'pay_status' => OrderEnum::PAY_STATUS_PAID])->count();
        // 商家营业金额
        $detail['businessMoney'] = $orderModel->where(['shop_id' => $shop_id, 'pay_status' => OrderEnum::PAY_STATUS_PAID])->sum('order_amount');
        // 退款订单金额
        $detail['refundMoney'] = $orderModel->where(['shop_id' => $shop_id])->where('refund_status', '>', 0)->sum('order_amount');
    }

    /**
     * @Notes: 提现详细
     * @Author: 张无忌
     * @param $id
     * @param $shop_id
     * @return array
     */
    public static function detail($id, $shop_id)
    {
        $withdrawal = (new ShopWithdrawal())->findOrEmpty($id)->toArray();
        $shop       = (new Shop())->with(['category'])->findOrEmpty($shop_id)->toArray();
        $bank       = (new ShopBank())->findOrEmpty($withdrawal['bank_id'])->toArray();
        $alipay     = (new ShopAlipay())->findOrEmpty($withdrawal['alipay_id'])->toArray();

        $shop['type']               = ShopEnum::getShopTypeDesc($shop['type']);
        $withdrawal['status_text']  = WithdrawalEnum::getStatusDesc($withdrawal['status']);
        $withdrawal['type_text']    = ShopWithdrawEnum::getTypeText($withdrawal['type']);

        return [ 'withdrawal' => $withdrawal, 'shop' => $shop, 'bank' => $bank, 'alipay' => $alipay ];
    }

    /**
     * @Notes: 选项卡与汇总统计详细
     * @param $shop_id
     * @return array
     */
    public static function statistics($shop_id)
    {
        $model = new ShopWithdrawal();
        $apply   = $model->where(['status' => WithdrawalEnum::APPLY_STATUS, 'shop_id' => $shop_id])->count();
        $handle  = $model->where(['status' => WithdrawalEnum::HANDLE_STATUS, 'shop_id' => $shop_id])->count();
        $success = $model->where(['status' => WithdrawalEnum::SUCCESS_STATUS, 'shop_id' => $shop_id])->count();
        $error   = $model->where(['status' => WithdrawalEnum::ERROR_STATUS, 'shop_id' => $shop_id])->count();

        //成交订单笔数
        $modelOrder = new Order();
        $modelShopWithdrawal = new ShopWithdrawal();
        $orderNum = $modelOrder
            ->where([
                ['shop_id', '=', $shop_id],
                ['pay_status', '>', PayEnum::UNPAID]
            ])
            ->count('id');
        //营业额
        $orderAmount = $modelOrder
            ->where([
                ['shop_id', '=', $shop_id],
                ['pay_status', '>', PayEnum::UNPAID]
            ])
            ->sum('order_amount');

        //退款订单金额
        $refundAmount = $modelOrder
            ->where([
                ['shop_id', '=', $shop_id],
                ['shipping_status', '=', OrderEnum::SHIPPING_NO],
                ['pay_status', '=', PayEnum::REFUNDED],
                ['refund_status', 'in', [OrderEnum::REFUND_STATUS_PART_REFUND, OrderEnum::REFUND_STATUS_ALL_REFUND]],
            ])
            ->sum('refund_amount');

        //待退款订单金额
        $refundAmountIng = $modelOrder->alias('o')
            ->join('order_refund or', 'or.order_id = o.id')
            ->where([
                ['o.shop_id', '=', $shop_id],
                ['o.shipping_status', '=', OrderEnum::SHIPPING_NO],
                ['or.refund_status', '<>', OrderRefundEnum::REFUND_STATUS_COMPLETE]
            ])
            ->sum('or.refund_amount');

        //售后退款金额
        $salesRefundAmount = $modelOrder->alias('o')
            ->join('after_sale as', 'as.order_id = o.id')
            ->where([
                ['o.shop_id', '=', $shop_id],
                ['o.shipping_status', '=', OrderEnum::SHIPPING_FINISH],
                ['as.status', '=', AfterSaleEnum::STATUS_COMPLETE]
            ])
            ->sum('as.refund_price');

        //待售后退款金额
        $salesRefundAmountIng = $modelOrder->alias('o')
            ->join('after_sale as', 'as.order_id = o.id')
            ->where([
                ['o.shop_id', '=', $shop_id],
                ['o.shipping_status', '=', OrderEnum::SHIPPING_FINISH],
                ['as.status', '=', AfterSaleEnum::STATUS_WAITING]
            ])
            ->sum('as.refund_price');

        //已结算成交订单数
        $modelShopSettlement = new ShopSettlement();
        $settleOrederNum = $modelShopSettlement
            ->where([
                ['shop_id', '=', $shop_id],
            ])
            ->sum('deal_order_count');

        //已结算营业额
        $settleOrederAmount = $modelShopSettlement
            ->where([
                ['shop_id', '=', $shop_id],
            ])
            ->sum('business_money');

        //待结算营业额
        $settleOrederAmountWait = $modelOrder
            ->where([
                ['shop_id', '=', $shop_id],
                ['refund_status', '=', 0 ],
                ['settle_id', '=', OrderEnum::SETTLE_WAIT]
            ])
            ->sum('order_amount');

        //已结算分销佣金金额
        $settleDistributionAmount = $modelShopSettlement
            ->where([
                ['shop_id', '=', $shop_id],
            ])
            ->sum('distribution_money');

        //已结算入账金额
        $settleWithdrawalAmount = $modelShopSettlement
            ->where([
                ['shop_id', '=', $shop_id],
            ])
            ->sum('entry_account_money');

        //已结算交易服务费
        $settlePoundageAmount = $modelShopSettlement
            ->where([
                ['shop_id', '=', $shop_id],
            ])
            ->sum('trade_service_fee');

        //已提现金额    
        $withdrawaLeftamount = $modelShopWithdrawal
            ->where([
                ['shop_id', '=', $shop_id],
                ['status', '=', WithdrawalEnum::SUCCESS_STATUS]
            ])
            ->sum('apply_amount');

        //提现中金额
        $withdrawaLeftamountIng = $modelShopWithdrawal
            ->where([
                ['shop_id', '=', $shop_id],
                ['status', 'in', [WithdrawalEnum::APPLY_STATUS, WithdrawalEnum::HANDLE_STATUS]]
            ])
            ->sum('apply_amount');

        //可提现金额       
        $modelShop = new Shop();
        $shopWallet = $modelShop
            ->where([
                ['id', '=', $shop_id]
            ])
            ->sum('wallet');

        //提现手续费
        $procedMoney = $modelShopWithdrawal
            ->where([
                ['shop_id', '=', $shop_id],
                ['status', '=', WithdrawalEnum::SUCCESS_STATUS]
            ])
            ->sum('poundage_amount');


        return [
            'apply'                         =>  $apply,
            'handle'                        =>  $handle,
            'success'                       =>  $success,
            'error'                         =>  $error,
            'orderNum'                      =>  $orderNum, //成交订单笔数
            'orderAmount'                   =>  $orderAmount, //营业额
            'refundAmount'                  =>  $refundAmount, //退款订单金额
            'refundAmountIng'               =>  $refundAmountIng, //待退款订单金额
            'salesRefundAmount'             =>  $salesRefundAmount, //售后退款金额
            'salesRefundAmountIng'          =>  $salesRefundAmountIng, //待售后退款金额
            'settleOrederNum'               =>  $settleOrederNum, //已结算成交订单数
            'settleOrederAmount'            =>  $settleOrederAmount, //已结算营业额
            'settleOrederAmountWait'        =>  $settleOrederAmountWait, //待结算营业额
            'settleDistributionAmount'      =>  $settleDistributionAmount, //已结算分销佣金金额
            'settleWithdrawalAmount'        =>  $settleWithdrawalAmount, //已结算入账金额
            'settlePoundageAmount'          =>  $settlePoundageAmount, //已结算交易服务费
            'withdrawaLeftamount'           =>  $withdrawaLeftamount, //已提现金额
            'procedMoney'                   =>  $procedMoney, //提现手续费
            'withdrawaLeftamountIng'        =>  $withdrawaLeftamountIng, //提现中金额
            'shopWallet'                    =>  $shopWallet, //可提现金额
        ];
    }

    /**
     * @Notes: 申请提现
     * @Author: 张无忌
     * @param $post
     * @param $shop_id
     * @return bool
     */
    public static function add($post, $shop_id)
    {
        Db::startTrans();
        try {
            // 1、获取提现条件
            $min_withdrawal_money = ConfigServer::get('shop_withdrawal', 'min_withdrawal_money', 0);
            $max_withdrawal_money = ConfigServer::get('shop_withdrawal', 'max_withdrawal_money', 0);
            $withdrawal_service_charge = ConfigServer::get('shop_withdrawal', 'withdrawal_service_charge', 0);

            // 2、获取商家信息
            $shop   = (new Shop())->findOrEmpty($shop_id)->toArray();
            $wallet = $shop['wallet'];

            // 3、验证条件是否满足
            if (floatval($post['apply_amount']) > $wallet)
                throw new Exception('账户余额不足');
            if (floatval($post['apply_amount']) < $min_withdrawal_money)
                throw new Exception('最低提现金额不能少于' . $min_withdrawal_money . '元');
            if (floatval($post['apply_amount']) > $max_withdrawal_money)
                throw new Exception('最高提现金额不能大于' . $max_withdrawal_money . '元');

            // 4、获取商家提现手续费
            $poundage_amount   = 0;
            if ($withdrawal_service_charge > 0) {
                $proportion = $withdrawal_service_charge / 100;
                $poundage_amount = $post['apply_amount'] * $proportion;
                $poundage_amount = $poundage_amount <= 0 ? 0 : $poundage_amount;
            }

            // 5、创建申请记录
            $withdrawal = ShopWithdrawal::create([
                'sn'              => createSn('shop_withdrawal', 'sn'),
                'bank_id'         => $post['bank_id'] ?? 0,
                'alipay_id'       => $post['alipay_id'] ?? 0,
                'type'            => $post['type'],
                'shop_id'         => $shop_id,
                'apply_amount'    => floatval($post['apply_amount']),
                'left_amount'     => $post['apply_amount'] - $poundage_amount,
                'poundage_amount' => $poundage_amount,
                'poundage_ratio'  => $withdrawal_service_charge,
                'status'          => WithdrawalEnum::APPLY_STATUS
            ]);
            // 6、扣除商家可提现金额
            Shop::update([
                'wallet'      => ['dec', floatval($post['apply_amount'])],
                'update_time' => time()
            ], ['id' => $shop_id]);
            $left_amount =  Shop::where(['id' => $shop_id])->value('wallet');
            // 7、增加提现流水记录(待提现)
            $logType = ShopAccountLog::withdrawal_stay_money;
            ShopAccountLog::decData($shop_id, $logType, $post['apply_amount'], $left_amount, [
                'source_id' => $withdrawal['id'],
                'source_sn' => $withdrawal['sn'],
                'remark'    => '商家提现'
            ]);

            $platform_contacts = ConfigServer::get('website_platform', 'platform_mobile');
            if (!empty($platform_contacts)) {
                event('Notice', [
                    'scene' => NoticeEnum::SHOP_WITHDRAWAL_NOTICE_PLATFORM,
                    'mobile' => $platform_contacts,
                    'params' => [
                        'shop_withdrawal_sn' => $withdrawal['sn'],
                        'shop_name' => $shop['name'],
                    ]
                ]);
            }

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            static::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 导出
     * @param $where
     * @return array|false
     * @author 段誉
     * @date 2022/4/24 11:59
     */
    public static function export($where)
    {
        try {
            $lists = (new ShopWithdrawal())
                ->field(true)
                ->where($where)
                ->select()->toArray();

            foreach ($lists as &$item) {
                $item['status'] = WithdrawalEnum::getStatusDesc($item['status']);
            }

            $excelFields = [
                'sn' => '提现单号',
                'apply_amount' => '提现金额',
                'poundage_amount' => '提现手续费',
                'left_amount' => '到账金额',
                'status' => '提现状态',
                'create_time' => '提现时间',
            ];

            $export = new ExportExcelServer();
            $export->setFileName('财务中心');
            $result = $export->createExcel($excelFields, $lists);

            return ['url' => $result];

        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }
}
