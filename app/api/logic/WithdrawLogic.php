<?php

namespace app\api\logic;

use app\common\basics\Logic;
use app\common\logic\AccountLogLogic;
use app\common\model\{AccountLog, Withdraw, WithdrawApply};
use app\common\model\user\User;
use app\common\enum\WithdrawEnum;
use app\common\server\ConfigServer;
use think\facade\Db;
use think\Exception;

/***
 * Class WithdrawLogic 会员提现
 * @package app\api\logic
 */
class WithdrawLogic extends Logic
{

    /**
     * @notes 基础配置
     * @param $user_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/13 6:26 下午
     */
    public static function config($user_id)
    {

        $user = User::where('id', $user_id)->find();
        $config = [
            'able_withdraw' => $user['earnings'] ? $user['earnings'] : 0,
            'min_withdraw' => ConfigServer::get('withdraw', 'min_withdraw', 0),//最低提现金额;
            'max_withdraw' => ConfigServer::get('withdraw', 'max_withdraw', 0),//最高提现金额;
            'poundage_percent' => ConfigServer::get('withdraw', 'poundage', 0),//提现手续费;
        ];

        $types = ConfigServer::get('withdraw', 'type', [1]); //提现方式,若未设置默认为提现方式为钱包
        // 封装提现方式
        $config['type'] = [];
        if ($types) {
            foreach ($types as $value) {
                $config['type'][] = [
                    'name' => WithdrawEnum::getTypeDesc($value),
                    'value' => $value
                ];
            }
        }
        return $config;
    }

    /**
     * @notes 申请提现
     * @param $user_id
     * @param $post
     * @return int|string
     * @throws Exception
     * @throws \think\exception\PDOException
     * @author suny
     * @date 2021/7/13 6:26 下午
     */
    public static function apply($user_id, $post)
    {

        Db::startTrans();
        try {
            //提现手续费
            $poundage = 0;
            if ($post['type'] != WithdrawEnum::TYPE_BALANCE) {
                $poundage_config = ConfigServer::get('withdraw', 'poundage', 0);
                $poundage = $post['money'] * $poundage_config / 100;
            }

            $data = [
                'sn' => createSn('withdraw_apply', 'sn'),
                'batch_no' => createSn('withdraw_apply', 'batch_no','SJZZ'),
                'user_id' => $user_id,
                'type' => $post['type'],
                'account' => $post['account'] ?? '',
                'real_name' => $post['real_name'] ?? '',
                'money' => $post['money'],
                'left_money' => $post['money'] - $poundage,
                'money_qr_code' => $post['money_qr_code'] ?? '',
                'remark' => $post['remark'] ?? '',
                'bank' => $post['bank'] ?? '',
                'subbank' => $post['subbank'] ?? '',
                'poundage' => $poundage,
                'status' => 1, // 待提现
                'create_time' => time(),
            ];
            $withdraw_id = WithdrawApply::insertGetId($data);

            //提交申请后,扣减用户的佣金
            $user = User::find($user_id);
            $user->earnings = ['dec', $post['money']];
            $user->save();
            //增加佣金变动记录
            AccountLogLogic::AccountRecord(
                $user_id,
                $post['money'],
                2,
                AccountLog::withdraw_dec_earnings,
                '',
                $withdraw_id,
                $data['sn']
            );

            Db::commit();
            return $withdraw_id;
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @notes 提现记录
     * @param $user_id
     * @param $get
     * @param $page
     * @param $size
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/13 6:26 下午
     */
    public static function records($user_id, $get, $page, $size)
    {

        $count = WithdrawApply
            ::where(['user_id' => $user_id])
            ->count();

        $lists = WithdrawApply::where(['user_id' => $user_id])
            ->order('create_time desc')
            ->select();

        foreach ($lists as &$item) {
            $item['desc'] = '提现至' . WithdrawEnum::getTypeDesc($item['type']);
            $item['status_text'] = WithdrawEnum::getStatusDesc($item['status']);
        }

        $data = [
            'list' => $lists,
            'page' => $page,
            'size' => $size,
            'count' => $count,
            'more' => is_more($count, $page, $size)
        ];
        return $data;
    }

    /**
     * @notes 提现详情
     * @param $id
     * @param $user_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/13 6:26 下午
     */
    public static function info($id, $user_id)
    {

        $info = WithdrawApply::field('status, sn, create_time, type, money, left_money, poundage')
            ->where(['id' => $id, 'user_id' => $user_id])
            ->find()->toArray();

        if (!$info) {
            return [];
        }
        $info['typeDesc'] = WithdrawEnum::getTypeDesc($info['type']);
        $info['statusDesc'] = WithdrawEnum::getStatusDesc($info['status']);
        return $info;
    }
}