<?php


namespace app\shop\logic\finance;


use app\common\basics\Logic;
use app\common\enum\OrderEnum;
use app\common\enum\PayEnum;
use app\common\model\AfterSale;
use app\common\model\order\Order;
use app\common\model\shop\Shop;
use app\common\model\shop\ShopAccountLog;
use app\common\model\shop\ShopSettlement;
use app\common\model\shop\ShopSettlementRecord;
use app\common\server\ConfigServer;
use app\common\server\ExportExcelServer;
use think\Exception;
use think\facade\Db;

class SettlementLogic extends Logic
{
    /**
     * @Notes: 结算列表
     * @Author: 张无忌
     * @param $get
     * @param $shop_id
     * @return array
     */
    public static function lists($get, $shop_id, $is_export = false)
    {
        try {
            $where[] = ['shop_id', '=', $shop_id];
            if (!empty($get['start_time']) and $get['start_time'])
                $where[] = ['start_time', '>=', strtotime($get['start_time'])];
            if (!empty($get['end_time']) and $get['end_time'])
                $where[] = ['end_time', '>=', strtotime($get['end_time'])];

            // 导出Excel
            if (true === $is_export) {
                return self::export($where);
            }

            $model = new ShopSettlement();
            $lists = $model->field(true)
                ->where($where)
                ->order('id', 'desc')
                ->paginate([
                    'page'      => $get['page'],
                    'list_rows' => $get['limit'],
                    'var_page' => 'page'
                ])
                ->toArray();

            return ['count'=>$lists['total'], 'lists'=>$lists['data']];
        } catch (\Exception $e) {
            return ['error'=>$e->getMessage()];
        }
    }

    /**
     * @Notes: 结算详细
     * @Author: 张无忌
     * @param $get
     * @return array
     */
    public static function detail($get)
    {
        try {
            $where[] = ['settle_id', '=', (int)$get['settle_id']];
            if (!empty($get['order_sn']) and $get['order_sn'])
                $where[] = ['order_sn', 'like', '%'.$get['order_sn'].'%'];

            $model = new ShopSettlementRecord();
            $lists = $model->field(true)
                ->where($where)
                ->order('id', 'asc')
                ->paginate([
                    'page'      => $get['page'],
                    'list_rows' => $get['limit'],
                    'var_page' => 'page'
                ])
                ->toArray();

            return ['count'=>$lists['total'], 'lists'=>$lists['data']];
        } catch (\Exception $e) {
            return ['error'=>$e->getMessage()];
        }
    }

    /**
     * @Notes: 提交结算
     * @Author: 张无忌
     * @param $shop_id
     * @return bool
     */
    public static function add($shop_id)
    {
        Db::startTrans();
        try {
            // 1、获取售后时长时间搓
            $time = time();
            $afterSaleTime = ConfigServer::get('transaction', 'order_after_sale_days', 7);
            $afterSaleTime = intval($afterSaleTime * 24 * 60 * 60);

             // 2、查询所有可结算订单
            $orders = (new Order())->field([
                'id,order_sn,order_status,pay_status,refund_status,is_cancel',
                'order_amount,refund_amount,distribution_money,confirm_take_time'
                ])
                ->whereRaw("confirm_take_time+$afterSaleTime < $time")
                ->where([
                    ['shop_id', '=', $shop_id],
                    ['settle_id', '=', 0],
                    ['order_status', '=', OrderEnum::ORDER_STATUS_COMPLETE],
                    ['pay_status', '=', OrderEnum::PAY_STATUS_PAID],
                    ['confirm_take_time', '>', 0]
                ])->select()->toArray();

            if (!$orders) throw new Exception('暂无可结算订单金额');

            // 3、检测订单是否再售后
            $afterSale = (new AfterSale())->whereIn('order_id', array_column($orders, 'id'))
                ->whereIn('status', [
                    AfterSale::STATUS_APPLY_REFUND,
                    AfterSale::STATUS_WAIT_RETURN_GOODS,
                    AfterSale::STATUS_WAIT_RECEIVE_GOODS,
                    AfterSale::STATUS_WAIT_REFUND
                ])->where('del', 0)->select()->toArray();

            $afterSaleOrderIds = array_column($afterSale, 'order_id');
            $afterSaleOrderIds = array_unique($afterSaleOrderIds);
            if (!empty($afterSaleOrderIds)) {
                $orderIds = array_column($orders, 'id');
                if (count($orderIds) <= count($afterSaleOrderIds)) {
                    throw new Exception('暂无可结算订单金额!');
                }
            }

            // 4、生成结算批次记录
            $settleSn = createSn('shop_settlement', 'settle_sn', 'JS');
            $settle = ShopSettlement::create(['shop_id'=>$shop_id, 'settle_sn'=>$settleSn, 'create_time'=>$time,]);
            $data = self::handleSettlementByOrder($orders, $settle, $time);

            // 5、回调更新结算批次信息
            ShopSettlement::update([
                'deal_order_count'    => $data['dealOrderCount'],
                'business_money'      => $data['businessMoney'] ,
                'refund_order_money'  => $data['refundOrderMoney'],
                'after_sales_money'   => $data['afterSalesMoney'],
                'distribution_money'  => $data['distributionMoney'],
                'entry_account_money' => $data['entryAccountMoney'],
                'trade_service_fee'   => $data['totalTradeServiceFee'],
                'trade_service_ratio' => $data['trade_service_ratio'],
            ], ['id'=>$settle['id']]);

            // 6、记录商家结算流水记录
            $logType = ShopAccountLog::settlement_add_money;
            ShopAccountLog::incData($shop_id, $logType, $data['entryAccountMoney'], -1, [
                'source_id' => $settle['id'],
                'source_sn' => $settleSn,
                'remark'    => '商家对账结算'
            ]);

            // 7、把钱记录到商家钱包
            Shop::update(['wallet'=>['inc', $data['entryAccountMoney']]], ['id'=>$shop_id]);

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            static::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @Notes: 对订单进行结算
     * 实际入账金额 = (订单实付金额 - 分销金额 - 退款金额) * 交易服务费比例
     * @Author: 张无忌
     * @param $orders (订单列表数据)
     * @param $settle (结算单信息)
     * @param $time (当前时间戳)
     * @return array
     * @throws \Exception
     */
    private static function handleSettlementByOrder($orders, $settle, $time)
    {
        // 1、获取交易服务费比例
        $tradeServiceRatio = (new Shop())->where(['id'=>$settle['shop_id']])->value('trade_service_fee') ?? 0;

        // 2、结算数据汇总统计
        $data = [
            'dealOrderCount'       => 0, //总已结算成交订单数
            'businessMoney'        => 0, //总已结算营业额
            'refundOrderMoney'     => 0, //总退款订单金额
            'afterSalesMoney'      => 0, //总售后退款金额
            'distributionMoney'    => 0, //总已结算分销佣金金额
            'entryAccountMoney'    => 0, //总已结算入账金额
            'totalTradeServiceFee' => 0, //总交易服务费费用
            'trade_service_ratio'  => $tradeServiceRatio //交易服务费比例(%)
        ];

        // 3、处理结算每个订单
        $settle_record = [];
        $orders_update = [];
        foreach ($orders as $order) {
            $afterSale = (new AfterSale())->where(['order_id'=>$order['id']])
                ->whereIn('status', [
                    AfterSale::STATUS_APPLY_REFUND,
                    AfterSale::STATUS_WAIT_RETURN_GOODS,
                    AfterSale::STATUS_WAIT_RECEIVE_GOODS,
                    AfterSale::STATUS_WAIT_REFUND
                ])->where('del',0)->findOrEmpty();

            if (!$afterSale->isEmpty()) {
                continue;
            }

            $data['dealOrderCount']    += 1;
            $data['businessMoney']     += $order['order_amount'];
            $data['refundOrderMoney']  += $order['is_cancel'] ? $order['refund_amount'] : 0;
            $data['afterSalesMoney']   += $order['is_cancel'] ? 0 : $order['refund_amount'];
            $data['distributionMoney'] += $order['distribution_money'];

            $orderMoney      = $order['order_amount'] - $order['distribution_money'] - ($order['refund_amount'] ?: 0);
            $orderMoney      = $orderMoney < 0 ? 0 : $orderMoney;
            $tradeServiceFee = $orderMoney * ($tradeServiceRatio / 100); // 本单交易服务费
            $actualGetMoney  = $orderMoney - $tradeServiceFee;   // 实际到账金额
            $entryAmount     = $actualGetMoney;
            $data['entryAccountMoney']    += $actualGetMoney;
            $data['totalTradeServiceFee'] += $tradeServiceFee;

            $settle_record[] = [
                'settle_id'     => $settle['id'],
                'order_id'      => $order['id'],
                'settle_sn'     => $settle['settle_sn'],
                'order_sn'      => $order['order_sn'],
                'order_amount'  => $order['order_amount'],
                'refund_amount'        => $order['is_cancel'] ? ($order['refund_amount'] ? : 0) : 0,
                'after_sales_amount'   => $order['is_cancel'] ? 0 : ($order['refund_amount'] ? : 0),
                'distribution_amount'  => $order['distribution_money'],
                'entry_account_amount' => $entryAmount,
                'trade_service_fee'    => $tradeServiceFee,
                'trade_service_ratio'  => $tradeServiceRatio,
                'order_complete_time'  => $order['confirm_take_time'],
                'create_time'          => $time
            ];

            $orders_update[] = [
                'id'            => $order['id'],
                'settle_id'     => $settle['id'],
                'settle_amount' => $entryAmount,
                'update_time'   => $time
            ];
        }

        // 4、保存更新
        if (!empty($settle_record)) {
            (new ShopSettlementRecord())->saveAll($settle_record);
            (new Order())->saveAll($orders_update);
        }
        // 5、返回汇总数据
        return $data;
    }

    
    /**
     * @Notes: 结算统计
     * @return array
     */
    public static function statistics($shop_id = 0)
    {
        $where[] = ['shop_id', '=' , $shop_id];
                   
        $modelOrder = new Order();;

        //已结算成交订单数
        $modelShopSettlement = new ShopSettlement();
        $settleOrederNum = $modelShopSettlement
                ->where($where)
                ->sum('deal_order_count');

        //已结算营业额
        $settleOrederAmount = $modelShopSettlement
                ->where($where)
                ->sum('business_money');

        //待结算营业额
        $settleOrederAmountWait = $modelOrder
                ->where([
                    ['shop_id', '=' , $shop_id],
                    ['pay_status', '>', PayEnum::UNPAID],
                    ['settle_id', '=', OrderEnum::SETTLE_WAIT]
                ])
                ->sum('order_amount');
        
        //已结算分销佣金金额
        $settleDistributionAmount = $modelShopSettlement
                ->where($where)
                ->sum('distribution_money');
                        
        //已结算入账金额
        $settleWithdrawalAmount = $modelShopSettlement
                ->where($where)
                ->sum('entry_account_money');  

        //已结算交易服务费
        $settlePoundageAmount = $modelShopSettlement
                ->where($where)
                ->sum('trade_service_fee');     

        return [
            'settleOrederNum'               =>  $settleOrederNum, //已结算成交订单数
            'settleOrederAmount'            =>  $settleOrederAmount, //已结算营业额
            'settleOrederAmountWait'        =>  $settleOrederAmountWait, //待结算营业额
            'settleDistributionAmount'      =>  $settleDistributionAmount, //已结算分销佣金金额
            'settleWithdrawalAmount'        =>  $settleWithdrawalAmount, //已结算入账金额
            'settlePoundageAmount'          =>  $settlePoundageAmount, //已结算交易服务费
        ];
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
            $model = new ShopSettlement();
            $lists = $model->field(true)
                ->where($where)
                ->order('id', 'desc')
                ->select()
                ->toArray();

            $excelFields = [
                'settle_sn' => '结算批次号',
                'deal_order_count' => '已结算成交订单数',
                'business_money' => '已结算营业额',
                'refund_order_money' => '退款订单金额',
                'after_sales_money' => '售后退款金额',
                'distribution_money' => '已结算分销佣金金额',
                'entry_account_money' => '已结算入账金额',
                'create_time' => '结算时间',
            ];

            $export = new ExportExcelServer();
            $export->setFileName('财务结算');
            $export->setExportNumber(['settle_sn']);
            $result = $export->createExcel($excelFields, $lists);

            return ['url' => $result];

        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }
}