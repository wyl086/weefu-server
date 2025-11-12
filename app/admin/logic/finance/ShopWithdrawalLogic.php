<?php


namespace app\admin\logic\finance;


use app\common\basics\Logic;
use app\common\enum\ShopEnum;
use app\common\enum\ShopWithdrawEnum;
use app\common\enum\WithdrawalEnum;
use app\common\model\shop\Shop;
use app\common\model\shop\ShopAccountLog;
use app\common\model\shop\ShopAlipay;
use app\common\model\shop\ShopBank;
use app\common\model\shop\ShopWithdrawal;
use app\common\server\ExportExcelServer;
use app\common\server\UrlServer;
use app\common\server\YansongdaAliPayTransferServer;

class ShopWithdrawalLogic extends Logic
{
    /**
     * @Notes: 申请提现记录列表
     * @Author: 张无忌
     * @param $get
     * @return array
     */
    public static function lists($get, $is_export = false)
    {
        try {
            $where[] = ['status', '=', $get['type'] ?? 0];

            if (!empty($get['start_time']) and $get['start_time']) {
                $where[] = ['create_time', '>=', strtotime($get['start_time'])];
            }

            if (!empty($get['end_time']) and $get['end_time']) {
                $where[] = ['create_time', '<=', strtotime($get['start_time'])];
            }

            // 导出
            if (true === $is_export) {
                return self::withdrawalExport($where);
            }

            $model = new ShopWithdrawal();
            $lists = $model->field(true)
                ->where($where)
                ->with(['shop'])
                ->order('id desc')
                ->paginate([
                    'page'      => $get['page'],
                    'list_rows' => $get['limit'],
                    'var_page' => 'page'
                ])->toArray();

            foreach ($lists['data'] as &$item) {
                $item['status_text'] = WithdrawalEnum::getStatusDesc($item['status']);
            }

            return ['count'=>$lists['total'], 'lists'=>$lists['data']];
        } catch (\Exception $e) {
            static::$error = $e->getMessage();
            return ['error'=>$e->getMessage()];
        }
    }

    /**
     * @Notes: 统计
     * @Author: 张无忌
     * @return array
     */
    public static function statistics()
    {
        $model = new ShopWithdrawal();
        $apply   = $model->where(['status'=>WithdrawalEnum::APPLY_STATUS])->count();
        $handle  = $model->where(['status'=>WithdrawalEnum::HANDLE_STATUS])->count();
        $success = $model->where(['status'=>WithdrawalEnum::SUCCESS_STATUS])->count();
        $error   = $model->where(['status'=>WithdrawalEnum::ERROR_STATUS])->count();

        return ['apply'=>$apply, 'handle'=>$handle, 'success'=>$success, 'error'=>$error];
    }

    /**
     * @Notes: 数据汇总
     * @Author: 张无忌
     */
    public static function summary()
    {
        $model = new ShopWithdrawal();
        $successWithdrawn = $model->where(['status'=>WithdrawalEnum::SUCCESS_STATUS])->sum('apply_amount');
        $handleWithdrawn = $model->where(['status'=>WithdrawalEnum::HANDLE_STATUS])->sum('apply_amount');
        $totalWallet = (new Shop())->where(['del'=>0])->sum('wallet');

        return ['successWithdrawn'=>$successWithdrawn, 'handleWithdrawn'=>$handleWithdrawn, 'totalWallet'=>$totalWallet];
    }

    /**
     * @Notes: 提现详细
     * @Author: 张无忌
     * @param $id
     * @return array
     */
    public static function detail($id)
    {
        $withdrawal = (new ShopWithdrawal())->findOrEmpty($id)->toArray();
        $shop       = (new Shop())->with(['category'])->findOrEmpty($withdrawal['shop_id'])->toArray();
        $bank       = (new ShopBank())->findOrEmpty($withdrawal['bank_id'])->toArray();
        $alipay     = (new ShopAlipay())->findOrEmpty($withdrawal['alipay_id'])->toArray();

        $shop['type'] = ShopEnum::getShopTypeDesc($shop['type']);
        $withdrawal['status_text'] = WithdrawalEnum::getStatusDesc($withdrawal['status']);
        $withdrawal['type_text'] = ShopWithdrawEnum::getTypeText($withdrawal['type']);
    
        return [ 'withdrawal' => $withdrawal, 'shop' => $shop, 'bank' => $bank, 'alipay' => $alipay ];
    }

    /**
     * @Notes: 审核提现
     * @Author: 张无忌
     * @param $post
     * @return bool
     */
    public static function examine($post)
    {
        try {
            if ($post['is_examine']) {
                // 同意提现
                ShopWithdrawal::update([
                    'explain'     => $post['explain'] ?? '',
                    'status'      => WithdrawalEnum::HANDLE_STATUS,
                    'update_time' => time()
                ], ['id'=>$post['id']]);

            } else {
                // 拒绝提现
                $withdrawal = (new ShopWithdrawal())->findOrEmpty($post['id'])->toArray();
                ShopWithdrawal::update([
                    'explain'     => $post['explain'] ?? '',
                    'status'      => WithdrawalEnum::ERROR_STATUS,
                    'update_time' => time()
                ], ['id'=>$post['id']]);

                Shop::update([
                    'wallet'      => ['inc', $withdrawal['apply_amount']],
                    'update_time' => time()
                ], ['id'=>$withdrawal['shop_id']]);

                (new ShopAccountLog())->where([
                    'source_id' => $withdrawal['id'],
                    'source_sn' => $withdrawal['sn']
                ])->update([
                    'change_type' => 1,
                    'left_amount' => ['inc', $withdrawal['apply_amount']],
                    'source_type' => ShopAccountLog::withdrawal_fail_money
                ]);
            }

            return true;
        } catch (\Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @Notes: 审核提现转账
     * @Author: 张无忌
     * @param $post
     * @return bool
     */
    public static function transfer($post)
    {
        try {
            if ($post['is_examine']) {
                // 转账成功
                ShopWithdrawal::update([
                    'transfer_content' => $post['transfer_content'] ?? '',
                    'status'           => WithdrawalEnum::SUCCESS_STATUS,
                    'transfer_voucher' => $post['image'] ?? '',
                    'transfer_time'    => time(),
                    'update_time'      => time()
                ], ['id'=>(int)$post['id']]);

                $withdrawal = (new ShopWithdrawal())->findOrEmpty($post['id'])->toArray();
                (new ShopAccountLog())->where([
                    'source_id' => $withdrawal['id'],
                    'source_sn' => $withdrawal['sn']
                ])->update([
                    'change_type' => 2,
                    'source_type' => ShopAccountLog::withdrawal_dec_money
                ]);

            } else {
                // 转账失败
                $withdrawal = (new ShopWithdrawal())->findOrEmpty($post['id'])->toArray();
                ShopWithdrawal::update([
                    'transfer_content' => $post['transfer_content'] ?? '',
                    'status'           => WithdrawalEnum::ERROR_STATUS,
                    'transfer_voucher' => $post['image'] ?? '',
                    'transfer_time'    => time(),
                    'update_time'      => time()
                ], ['id'=>$post['id']]);

                Shop::update([
                    'wallet'      => ['inc', $withdrawal['apply_amount']],
                    'update_time' => time()
                ], ['id'=>$withdrawal['shop_id']]);

                (new ShopAccountLog())->where([
                    'source_id' => $withdrawal['id'],
                    'source_sn' => $withdrawal['sn']
                ])->update([
                    'change_type' => 1,
                    'left_amount' => ['inc', $withdrawal['apply_amount']],
                    'source_type' => ShopAccountLog::withdrawal_fail_money
                ]);
            }

            return true;
        } catch (\Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }
    
    static function transfer_online($post) : bool
    {
        try {
            
            $detail = ShopWithdrawal::with([ 'alipay' ])->findOrEmpty($post['id']);
            $result = (new YansongdaAliPayTransferServer())->shopWithdrawTransfer($detail);
            if (true === $result) {
                // 转账成功
                ShopWithdrawal::update([
                    'explain'          => '',
                    'status'           => WithdrawalEnum::SUCCESS_STATUS,
                    'transfer_voucher' => '',
                    'transfer_time'    => time(),
                    'update_time'      => time()
                ], [ 'id' => (int) $post['id'] ]);
    
                $withdrawal = (new ShopWithdrawal())->findOrEmpty($post['id'])->toArray();
                (new ShopAccountLog())->where([
                    'source_id' => $withdrawal['id'],
                    'source_sn' => $withdrawal['sn']
                ])->update([
                    'change_type' => 2,
                    'source_type' => ShopAccountLog::withdrawal_dec_money
                ]);
            } else {
                static::$error = (string) $result;
    
                // 转账失败
                $withdrawal = (new ShopWithdrawal())->findOrEmpty($post['id'])->toArray();
                ShopWithdrawal::update([
                    'explain'          => '支付宝转账失败',
                    'status'           => WithdrawalEnum::ERROR_STATUS,
                    'transfer_voucher' => '',
                    'transfer_time'    => time(),
                    'update_time'      => time()
                ], [ 'id' => $post['id'] ]);
    
                Shop::update([
                    'wallet'      => ['inc', $withdrawal['apply_amount']],
                    'update_time' => time()
                ], [ 'id' => $withdrawal['shop_id'] ]);
    
                (new ShopAccountLog())->where([
                    'source_id' => $withdrawal['id'],
                    'source_sn' => $withdrawal['sn']
                ])->update([
                    'change_type' => 1,
                    'left_amount' => ['inc', $withdrawal['apply_amount']],
                    'source_type' => ShopAccountLog::withdrawal_fail_money
                ]);
            }
            
            return true;
        } catch (\Throwable $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @Notes: 账户明细
     * @Author: 张无忌
     * @param $get
     * @return array
     */
    public static function account($get, $is_export = false)
    {
        $where = [];
        if (isset($get['shop_name']) && $get['shop_name']) { 
            $where[] = ['S.name', 'like', '%' . $get['shop_name'] . '%'];
        }
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

        // 导出
        if (true === $is_export) {
            return self::accountExport($where);
        }

        $model = new ShopAccountLog();
        $lists = $model->alias('SAL')
                    ->field(['SAL.*', 'S.name,S.logo,S.type'])
                    ->join('shop S', 'S.id = SAL.shop_id')
                    ->order('SAL.id desc')
                    ->where($where)
                    ->paginate([
                        'page'      => $get['page'],
                        'list_rows' => $get['limit'],
                        'var_page'  => 'page'
                    ])->toArray();


        foreach ($lists['data'] as &$item) {
            $item['logo'] = empty($item['logo']) ? '' : UrlServer::getFileUrl($item['logo']);
            $item['type'] = ShopEnum::getShopTypeDesc($item['type']);
            $item['source_type'] = ShopAccountLog::getSourceType($item['source_type']);
            $item['change_amount'] = $item['change_type'] == 1 ? '+'.$item['change_amount'] : '-'.$item['change_amount'];
            $item['logo'] = !empty($item['logo']) ? UrlServer::getFileUrl($item['logo']) : "";
        }

        return ['count'=>$lists['total'], 'lists'=>$lists['data']];
    }

    /**
     * @notes 导出商家明细Excel
     * @param array $where
     * @return array|false
     * @author 段誉
     * @date 2022/4/24 10:10
     */
    public static function withdrawalExport($where)
    {
        try {
            $model = new ShopWithdrawal();
            $lists = $model->field(true)
                ->where($where)
                ->with(['shop'])
                ->select()->toArray();

            foreach ($lists as &$item) {
                $item['status_text'] = WithdrawalEnum::getStatusDesc($item['status']);
                $item['shop_name'] = $item['shop']['name'];
                $item['shop_type'] = ShopEnum::getShopTypeDesc($item['shop']['type']);
            }

            $excelFields = [
                'shop_name' => '商家名称',
                'shop_type' => '商家类型',
                'sn' => '提现单号',
                'apply_amount' => '提现金额',
                'poundage_amount' => '提现手续费',
                'left_amount' => '到账金额',
                'status_text' => '提现状态',
                'create_time' => '提现时间',
            ];

            $export = new ExportExcelServer();
            $export->setFileName('商家提现');
            $result = $export->createExcel($excelFields, $lists);

            return ['url' => $result];

        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 导出商家账户明细Excel
     * @param array $where
     * @return array|false
     * @author 段誉
     * @date 2022/4/24 10:10
     */
    public static function accountExport($where)
    {
        try {
            $model = new ShopAccountLog();
            $lists = $model->alias('SAL')
                ->field(['SAL.*', 'S.name,S.type'])
                ->join('shop S', 'S.id = SAL.shop_id')
                ->order('SAL.id desc')
                ->where($where)
                ->select()->toArray();

            foreach ($lists as &$item) {
                $item['type'] = ShopEnum::getShopTypeDesc($item['type']);
                $item['source_type'] = ShopAccountLog::getSourceType($item['source_type']);
                $item['change_amount'] = $item['change_type'] == 1 ? '+'.$item['change_amount'] : '-'.$item['change_amount'];
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
            $export->setFileName('商家账户明细');
            $export->setExportNumber(['log_sn', 'source_sn']);
            $result = $export->createExcel($excelFields, $lists);

            return ['url' => $result];

        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }
}