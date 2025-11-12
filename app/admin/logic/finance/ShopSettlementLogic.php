<?php


namespace app\admin\logic\finance;


use app\common\basics\Logic;
use app\common\enum\ShopEnum;
use app\common\enum\PayEnum;
use app\common\enum\OrderEnum;
use app\common\model\order\Order;
use app\common\model\shop\ShopSettlement;
use app\common\model\shop\ShopSettlementRecord;
use app\common\server\ExportExcelServer;
use app\common\server\UrlServer;

class ShopSettlementLogic extends Logic
{
    /**
     * @Notes: 商家结算列表
     * @Author: 张无忌
     * @param $get
     * @return array
     */
    public static function lists($get, $is_export = false)
    {
        try {
            $where = [];
            if (!empty($get['name']) and $get['name']) {
                $where[] = ['S.name', 'like', '%'.$get['name'].'%'];
            }

            if (!empty($get['start_time']) and $get['start_time']) {
                $where[] = ['SL.create_time', '>=', strtotime($get['start_time'])];
            }

            if (!empty($get['end_time']) and $get['end_time']) {
                $where[] = ['SL.create_time', '<=', strtotime($get['start_time'])];
            }

            // 导出
            if (true === $is_export) {
                return self::settlementExport($where);
            }

            $model = new ShopSettlement();
            $lists = $model->field([
                    'SL.id,SL.shop_id,S.name,S.type,S.logo',
                    'sum(SL.deal_order_count) AS deal_order_count',
                    'sum(SL.business_money) AS business_money',
                ])->alias('SL')
                ->join('shop S', 'S.id = SL.shop_id')
                ->group('shop_id')
                ->where($where)
                ->paginate([
                    'page'      => $get['page'],
                    'list_rows' => $get['limit'],
                    'var_page'  => 'page'
                ])->toArray();

            foreach ($lists['data'] as &$item) {
                $item['type'] = ShopEnum::getShopTypeDesc($item['type']);
                $item['logo'] = UrlServer::getFileUrl($item['logo']);
            }

            return ['count'=>$lists['total'], 'lists'=>$lists['data']];
        } catch (\Exception $e) {
            return ['error'=>$e->getMessage()];
        }
    }

    /**
     * @Notes: 商家结算记录
     * @Author: 张无忌
     * @param $get
     * @return array
     */
    public static function record($get)
    {
        try {
            $model = new ShopSettlement();
            $lists = $model->field(true)
                ->where(['shop_id'=>$get['shop_id']])
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
     * @Notes: 结算统计
     * @return array
     */
    public static function statistics($shop_id = 0)
    {
        $where = [];
        if($shop_id){
            $where[] = ['shop_id', '=' , $shop_id];
        }
                   
        //营业额
        $modelOrder = new Order();

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
        $settleOrederAmountWait =  $modelOrder
                ->where([
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
     * @notes 导出商家结算Excel
     * @param array $where
     * @return array|false
     * @author 段誉
     * @date 2022/4/24 10:10
     */
    public static function settlementExport($where)
    {
        try {
            $model = new ShopSettlement();
            $lists = $model->field([
                'SL.id,SL.shop_id,S.name,S.type,S.logo',
                'sum(SL.deal_order_count) AS deal_order_count',
                'sum(SL.business_money) AS business_money',
            ])->alias('SL')
                ->join('shop S', 'S.id = SL.shop_id')
                ->group('shop_id')
                ->where($where)
                ->select()
                ->toArray();

            foreach ($lists as &$item) {
                $item['type'] = ShopEnum::getShopTypeDesc($item['type']);
            }

            $excelFields = [
                'name' => '商家名称',
                'type' => '商家类型',
                'deal_order_count' => '已结算成交订单数',
                'business_money' => '已结算营业额',
            ];

            $export = new ExportExcelServer();
            $export->setFileName('商家结算');
            $result = $export->createExcel($excelFields, $lists);

            return ['url' => $result];

        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }
    
}