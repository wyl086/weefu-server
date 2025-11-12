<?php


namespace app\admin\logic\finance;


use app\common\basics\Logic;
use app\common\model\AccountLog;
use app\common\model\order\Order;
use app\common\enum\PayEnum;
use app\common\enum\OrderEnum;
use app\common\enum\OrderRefundEnum;
use app\common\enum\AfterSaleEnum;
use app\common\enum\WithdrawalEnum;
use app\common\enum\DistributionOrderGoodsEnum;
use app\common\enum\WithdrawEnum;
use app\common\model\user\User;
use app\common\model\shop\Shop;
use app\common\model\shop\ShopWithdrawal;
use app\common\model\shop\ShopSettlement;
use app\common\model\WithdrawApply;


class FinanceLogic extends Logic
{
    /**
     * @Notes: 商家汇总
     */
    public static function shop()
    {

        $model = new Order();
        $modelShopSettlement = new ShopSettlement();
        $modelShopWithdrawal = new ShopWithdrawal();
        $modelWithdrawApply  = new WithdrawApply();




        //已结算交易服务费（平台收入）、（商家）已结算交易服务费
        $shopAmount = $settlePoundageAmount = $modelShopSettlement
            ->sum('trade_service_fee');

        //会员提现手续费（平台收入
        $userPoundage =  $modelWithdrawApply
            ->where([
                ['status', '=', WithdrawEnum::STATUS_SUCCESS]
            ])
            ->sum('poundage');

        //商家提现手续费（平台收入）
        $commissionAmount =  $modelShopWithdrawal
            ->sum('poundage_amount');

        //成交订单笔数
        $orderNum = $model
            ->where([
                ['pay_status', '>', PayEnum::UNPAID]
            ])
            ->count('id');

        //营业额
        $orderAmount = $model
            ->where([
                ['pay_status', '>', PayEnum::UNPAID]
            ])
            ->sum('order_amount');


        //退款订单金额
        $refundAmount = $model
            ->where([
                ['shipping_status', '=', OrderEnum::SHIPPING_NO],
                ['pay_status', '=', PayEnum::REFUNDED],
                ['refund_status', 'in', [OrderEnum::REFUND_STATUS_PART_REFUND, OrderEnum::REFUND_STATUS_ALL_REFUND]],
            ])
            ->sum('refund_amount');

        //待退款订单金额
        $refundAmountIng = $model->alias('o')
            ->join('order_refund or', 'or.order_id = o.id')
            ->where([
                ['o.shipping_status', '=', OrderEnum::SHIPPING_NO],
                ['or.refund_status', '<>', OrderRefundEnum::REFUND_STATUS_COMPLETE]
            ])
            ->sum('or.refund_amount');

        //售后退款金额
        $salesRefundAmount = $model->alias('o')
            ->join('after_sale as', 'as.order_id = o.id')
            ->where([
                ['o.shipping_status', '=', OrderEnum::SHIPPING_FINISH],
                ['as.status', '=', AfterSaleEnum::STATUS_COMPLETE]
            ])
            ->sum('as.refund_price');

        //待售后退款金额
        $salesRefundAmountIng = $model->alias('o')
            ->join('after_sale as', 'as.order_id = o.id')
            ->where([
                ['o.shipping_status', '=', OrderEnum::SHIPPING_FINISH],
                ['as.status', '=', AfterSaleEnum::STATUS_WAITING]
            ])
            ->sum('as.refund_price');


        //已结算成交订单数
        $settleOrederNum = $modelShopSettlement
            ->sum('deal_order_count');

        //已结算营业额
        $settleOrederAmount = $modelShopSettlement
            ->sum('business_money');

        //待结算营业额
        $settleOrederAmountWait = $model
            ->where([
                ['refund_status', '=', 0 ],
                ['settle_id', '=', OrderEnum::SETTLE_WAIT]
            ])
            ->sum('order_amount');

        //已结算分销佣金金额
        $settleDistributionAmount = $modelShopSettlement
            ->sum('distribution_money');

        //已结算入账金额
        $settleWithdrawalAmount = $modelShopSettlement
            ->sum('entry_account_money');

        //已提现金额
        $withdrawaLeftamount = $modelShopWithdrawal
            ->where([
                ['status', '=', WithdrawalEnum::SUCCESS_STATUS]
            ])
            ->sum('apply_amount');

        //提现中金额
        $withdrawaLeftamountIng = $modelShopWithdrawal
            ->where([
                ['status', '=', WithdrawalEnum::HANDLE_STATUS]
            ])
            ->sum('apply_amount');

        //可提现金额       
        $modelShop = new Shop();
        $shopWallet = $modelShop->sum('wallet');

        //会员余额
        $modelUser = new User();
        $userMoney = $modelUser
            ->where([
                ['del', '=', 0]
            ])
            ->sum('user_money');


        //会员已结算分销佣金金额                   
        $userSettleDistributionAmount = $model->alias('o')
            ->join('order_goods og', 'og.order_id = o.id')
            ->join('distribution_order_goods dog', 'dog.order_goods_id = og.id')
            ->where([
                ['o.settle_id', '=', OrderEnum::SETTLE_FINISH],
                ['dog.status', '=', DistributionOrderGoodsEnum::STATUS_SUCCESS]
            ])
            ->sum('dog.money');

        //已提现佣金金额
        $userDistributionMoney = $modelWithdrawApply
            ->where([
                ['status', '=', WithdrawEnum::STATUS_SUCCESS]
            ])
            ->sum('money');

        //提现中佣金金额
        $userDistributionMoneyIng = $modelWithdrawApply
            ->where([
                ['status', '=', WithdrawEnum::STATUS_ING]
            ])
            ->sum('money');

        //可提现佣金金额
        $userDistributionMoneyWait = $modelUser
            ->where([
                ['del', '=', 0]
            ])
            ->sum('earnings');


        //总积分
        $all_integral = AccountLog::where(['change_type'=>1,'source_type'=>AccountLog::integral_change])->sum('change_amount');

        //签到送出积分
        $sign_in_integral = AccountLog::where(['source_type'=>AccountLog::sign_in_integral])->sum('change_amount');

        //使用积分
        $use_integral = AccountLog::where(['change_type'=>2,'source_type'=>AccountLog::integral_change])->sum('change_amount');

        //下单赠送积分
        $consume_award_integral = AccountLog::where(['source_type'=>AccountLog::consume_award_integral])->sum('change_amount');

        return [
            'shopAmount'                    =>  $shopAmount, //已结算交易服务费（平台收入）
            'userPoundage'                  =>  $userPoundage, //会员提现手续费（平台收入）
            'commissionAmount'              =>  $commissionAmount, //提现中佣金金额
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
            'withdrawaLeftamountIng'        =>  $withdrawaLeftamountIng, //提现中金额
            'shopWallet'                    =>  $shopWallet, //可提现金额
            'userMoney'                     =>  $userMoney, //会员余额
            'userSettleDistributionAmount'  =>  $userSettleDistributionAmount, //会员已结算分销佣金金额
            'userDistributionMoney'         =>  $userDistributionMoney, //已提现佣金金额
            'userDistributionMoneyIng'      =>  $userDistributionMoneyIng, //提现中佣金金额
            'userDistributionMoneyWait'     =>  $userDistributionMoneyWait, //可提现佣金金额
            'all_integral'                  =>  $all_integral, //总积分
            'sign_in_integral'              =>  $sign_in_integral, //签到送出积分
            'use_integral'                  =>  $use_integral, //使用积分
            'consume_award_integral'        =>  $consume_award_integral, //下单赠送积分
        ];
    }
}
