<?php


namespace app\shop\logic\finance;


use app\common\basics\Logic;
use app\common\enum\ShopEnum;
use app\common\model\shop\ShopAccountLog;
use app\common\server\ExportExcelServer;
use app\common\server\UrlServer;

class ShopLogic extends Logic
{
    /**
     * @Notes: 账户明细
     * @param $get
     * @param $shop_id (商家ID)
     * @return array
     */
    public static function account($get, $shop_id, $is_export = false)
    {
        $where[] = ['shop_id', '=', (int)$shop_id];
        if (isset($get['search_key']) && $get['search_key']) {
            switch($get['search_key']){
                case 'settle':
                    $where[] = ['SAL.source_type', '=', ShopAccountLog::settlement_add_money];
                    break;
                case 'withdrawal':
                    $where[] = ['SAL.source_type', '=', ShopAccountLog::withdrawal_dec_money];
                    break;
                case 'withdrawal_stay':
                    $where[] = ['SAL.source_type', '=', ShopAccountLog::withdrawal_stay_money];
                    break;
                case 'withdrawal_error':
                    $where[] = ['SAL.source_type', '=', ShopAccountLog::withdrawal_fail_money];
                    break;
            }
        }

        if (!empty($get['start_time']) and $get['start_time']) {
            $where[] = ['SAL.create_time', '>=', strtotime($get['start_time'])];
        }

        if (!empty($get['end_time']) and $get['end_time']) {
            $where[] = ['SAL.create_time', '<=', strtotime($get['end_time'])];
        }

        if (true === $is_export) {
            return self::export($where);
        }

        $model = new ShopAccountLog();
        $lists = $model->alias('SAL')
            ->field(['SAL.*', 'S.name,S.logo,S.type'])
            ->join('shop S', 'S.id = SAL.shop_id')
            ->where($where)
            ->order('create_time','desc')
            ->paginate([
                'page'      => $get['page'],
                'list_rows' => $get['limit'],
                'var_page'  => 'page'
            ])->toArray();


        foreach ($lists['data'] as &$item) {
            $item['type'] = ShopEnum::getShopTypeDesc($item['type']);
            $item['source_type'] = ShopAccountLog::getSourceType($item['source_type']);
            $symbol = $item['change_type'] === 1 ? '+' : '-';
            $item['change_amount'] = $symbol.$item['change_amount'];
            $item['logo'] = UrlServer::getFileUrl($item['logo']);
        }

        return ['count'=>$lists['total'], 'lists'=>$lists['data']];
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
            $lists = (new ShopAccountLog())->alias('SAL')
                ->field(['SAL.*', 'S.name,S.logo,S.type'])
                ->join('shop S', 'S.id = SAL.shop_id')
                ->where($where)
                ->order('create_time','desc')
                ->select()->toArray();

            foreach ($lists as &$item) {
                $item['type'] = ShopEnum::getShopTypeDesc($item['type']);
                $item['source_type'] = ShopAccountLog::getSourceType($item['source_type']);
                $symbol = $item['change_type'] === 1 ? '+' : '-';
                $item['change_amount'] = $symbol.$item['change_amount'];
            }

            $excelFields = [
                'name' => '商家名称',
                'type' => '商家类型',
                'log_sn' => '明细流水号',
                'source_sn' => '来源单号',
                'source_type' => '明细类型',
                'change_amount' => '变动金额',
                'left_amount' => '剩余金额',
                'create_time' => '记录时间',
            ];

            $export = new ExportExcelServer();
            $export->setFileName('账户明细');
            $export->setExportNumber(['log_sn', 'source_sn']);
            $result = $export->createExcel($excelFields, $lists);

            return ['url' => $result];

        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

}