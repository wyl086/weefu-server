<?php


namespace app\common\model\shop;


use app\common\basics\Models;
use think\Exception;
use think\facade\Db;

/**
 * 商户账号流水模型
 * Class ShopAccountLog
 * @package app\common\model\shop
 */
class ShopAccountLog extends Models
{
    const settlement_add_money  = 100; //商家结算入账
    const withdrawal_stay_money = 101; //商家提现中
    const withdrawal_dec_money  = 102; //商家提现扣减
    const withdrawal_fail_money = 103; //商家提现失败

    /**
     * @Notes: 增加资金
     * @Author: 张无忌
     * @param $shopId
     * @param $sourceType (来源类型,参考上面定义)
     * @param $changeAmount (增加的金额)
     * @param int $left_amount (增加后的金额, 如果=-1,则自动去计算)
     * @param array $data (其他信息)
     * @throws Exception
     */
    public static function incData($shopId, $sourceType, $changeAmount, $left_amount=-1, $data=[])
    {
        try {
            if ($left_amount === -1) {
                $left_amount = 0;
                $wallet = (new Shop())->where(['id'=>$shopId])->value('wallet') ?? 0;
                $left_amount += ($wallet + $changeAmount);
            } elseif ($left_amount === 0)  {
                $left_amount = 0;
                $wallet = (new Shop())->where(['id'=>$shopId])->value('wallet') ?? 0;
                $left_amount += $wallet;
            }

            self::create([
                'log_sn'        => createSn('shop_account_log', 'log_sn'),
                'shop_id'       => $shopId,
                'source_type'   => $sourceType,
                'change_amount' => $changeAmount,
                'left_amount'   => $left_amount,
                'source_id'     => $data['source_id'] ?? 0,
                'source_sn'     => $data['source_sn'] ?? '',
                'remark'        => $data['remark'] ?? '',
                'extra'         => $data['extra'] ?? '',
                'change_type'   => 1,
                'create_time'   => time()
            ]);

        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }


    /**
     * @Notes: 减少资金
     * @Author: 张无忌
     * @param $shopId
     * @param $sourceType (来源类型,参考上面定义)
     * @param $changeAmount (减少的金额)
     * @param int $left_amount (增加后的金额, 如果=-1,则自动去计算)
     * @param array $data (其他信息)
     * @throws Exception
     */
    public static function decData($shopId, $sourceType, $changeAmount, $left_amount=-1, $data=[])
    {
        try {
            if ($left_amount === -1) {
                $left_amount = 0;
                $wallet = (new Shop())->where(['id'=>$shopId])->value('wallet') ?? 0;
                $left_amount += ($wallet - $changeAmount);
            } elseif ($left_amount === 0)  {
                $left_amount = 0;
                $wallet = (new Shop())->where(['id'=>$shopId])->value('wallet') ?? 0;
                $left_amount += $wallet;
            }

            self::create([
                'log_sn'        => createSn('shop_account_log', 'log_sn'),
                'shop_id'       => $shopId,
                'source_type'   => $sourceType,
                'change_amount' => $changeAmount,
                'left_amount'   => $left_amount,
                'source_id'     => $data['source_id'] ?? 0,
                'source_sn'     => $data['source_sn'] ?? '',
                'remark'        => $data['remark'] ?? '',
                'extra'         => $data['extra'] ?? '',
                'change_type'   => 2,
                'create_time'   => time()
            ]);

        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @Notes: 来源类型
     * @param bool $status
     * @return array|mixed|string
     */
    public static function getSourceType($status = true)
    {
        $desc = [
            ShopAccountLog::settlement_add_money  => '结算入账',
            ShopAccountLog::withdrawal_dec_money  => '商家提现',
            ShopAccountLog::withdrawal_stay_money => '商家提现中',
            ShopAccountLog::withdrawal_fail_money => '商家提现失败',
        ];

        if ($status === true) {
            return $desc;
        }

        return $desc[$status] ?? '未知';
    }

}