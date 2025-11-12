<?php

namespace app\admin\logic\finance;

use app\admin\logic\WechatMerchantTransferLogic;
use app\common\basics\Logic;
use app\common\model\RechargeOrder;
use app\common\model\WithdrawApply;
use app\common\enum\WithdrawEnum;
use app\common\server\ConfigServer;
use app\common\server\ExportExcelServer;
use app\common\server\UrlServer;
use app\common\model\user\User;
use app\common\logic\AccountLogLogic;
use app\common\model\AccountLog;
use app\admin\logic\WechatCorporatePaymentLogic;
use think\facade\Db;
use think\Exception;

/**
 * Class WithdrawLogic
 * @package app\admin\logic\finance
 */
class WithdrawLogic extends Logic
{
    /**
     * @notes 会员佣金提现列表
     * @param $get
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/14 10:00 上午
     */
    public static function lists($get, $is_export = false)
    {

        $where = [];
        // 会员信息
        if (!empty($get['search_key']) && !empty($get['keyword'])) {
            $keyword = $get['keyword'];
            if ($get['search_key'] == 'user_sn') {
                $where[] = ['u.sn', '=', $keyword];
            } elseif ($get['search_key'] == 'nickname') {
                $where[] = ['u.nickname', 'like', '%' . $keyword . '%'];
            }
        }

        //提现单号
        if (isset($get['withdraw_sn']) && $get['withdraw_sn'] != '') {
            $where[] = ['w.sn', '=', $get['withdraw_sn']];
        }

        //提现方式
        if (isset($get['type']) && $get['type'] != '') {
            $where[] = ['w.type', '=', $get['type']];
        }

        //提现状态
        if (isset($get['status']) && $get['status'] != '') {
            $where[] = ['status', '=', $get['status']];
        }

        if (empty($get['start_time']) && empty($get['end_time'])) {
            $where[] = ['w.create_time', '>=', strtotime(date("Y-m-d", time()))];
            $where[] = ['w.create_time', '<=', strtotime(date("Y-m-d", time())) + 86399];
        }

        // 提现时间
        if (isset($get['start_time']) && $get['start_time'] && isset($get['end_time']) && $get['end_time']) {
            $where[] = ['w.create_time', 'between', [strtotime($get['start_time']), strtotime($get['end_time'])]];
//        }else{
////            $where[] = ['w.create_time', 'between', Time::today()];
        }

        // 导出
        if (true === $is_export) {
            return self::withdrawExport($where);
        }


        $lists = WithdrawApply::alias('w')
            ->field('w.*, u.nickname,u.avatar, u.sn as user_sn, u.mobile, ul.name as user_level_name')
            ->with('user')
            ->leftJoin('user u', 'u.id = w.user_id')
            ->leftJoin('user_level ul', 'ul.id = u.level')
            ->where($where)
            ->page($get['page'], $get['limit'])
            ->order('w.id desc')
            ->select();
        $count = WithdrawApply::alias('w')
            ->field('w.*, u.nickname,u.avatar, u.sn as user_sn, u.mobile, ul.name as user_level_name')
            ->leftJoin('user u', 'u.id = w.user_id')
            ->leftJoin('user_level ul', 'ul.id = u.level')
            ->where($where)
            ->order('w.id desc')
            ->count();


        foreach ($lists as &$item) {
            if (empty($item['user'])) {
                // 用户不存在
                $user = [
                    'avatar' => '',
                    'sn' => '-',
                    'nickname' => '-',
                ];
            } else {
                $user  = $item['user'];
            }
            $item['type_text'] = WithdrawEnum::getTypeDesc($item['type']);
            $item['status_text'] = WithdrawEnum::getStatusDesc($item['status']);
            $item['avatar'] = UrlServer::getFileUrl($item['avatar']);
            $user['avatar'] = UrlServer::getFileUrl($user['avatar']);
            $item['user_level_name'] = $item['user_level_name'] ? $item['user_level_name'] : '无等级';
            $user['user_level_name'] = $item['user_level_name'];
            // 通过中间变量$user解决Indirect modification of overloaded element报错
            $item['user'] = $user;
        }
        return ['count' => $count, 'lists' => $lists];
    }

    /**
     * @notes 数据汇总
     * @return array
     * @author suny
     * @date 2021/7/14 10:01 上午
     */
    public static function summary()
    {

        $model = new WithdrawApply();
        $successWithdraw = $model->where(['status' => WithdrawEnum::STATUS_SUCCESS])->sum('money');
        $handleWithdraw = $model->where(['status' => WithdrawEnum::STATUS_ING])->sum('money');
        $totalEarnings = (new User())->where(['del' => 0])->sum('earnings');

        return ['successWithdraw' => $successWithdraw, 'handleWithdraw' => $handleWithdraw, 'totalEarnings' => $totalEarnings];
    }

    /**
     * @notes 佣金明细
     * @param $get
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/14 10:01 上午
     */
    public static function commission($get, $is_export = false)
    {

        $where = [];

        // 明细类型
        $source_type = AccountLog::earnings_change;
        if (isset($get['source_type']) && !empty($get['source_type'])) {
            $where[] = ['a.source_type', '=', $get['source_type']];
        } else {
            $where[] = ['a.source_type', 'in', $source_type];
        }

        //明细搜索
        if (!empty($get['search_key']) && !empty($get['keyword'])) {
            $keyword = $get['keyword'];
            switch ($get['search_key']) {
                case  'user_sn' :
                    $where[] = ['u.sn', '=', $keyword];
                    break;
                case  'nickname' :
                    $where[] = ['u.nickname', 'like', '%' . $keyword . '%'];
                    break;
            }
        }

        if (empty($get['start_time']) && empty($get['end_time'])) {
            $where[] = ['a.create_time', '>=', strtotime(date("Y-m-d", time()))];
            $where[] = ['a.create_time', '<=', strtotime(date("Y-m-d", time())) + 86399];
        }

        //明细时间
        if (isset($get['start_time']) && $get['start_time'] != '') {
            $where[] = ['a.create_time', '>=', strtotime($get['start_time'])];
        }

        if (isset($get['end_time']) && $get['end_time'] != '') {
            $where[] = ['a.create_time', '<=', strtotime($get['end_time'])];
        }

        // 导出
        if (true === $is_export) {
            return self::commissionExport($where);
        }

        $lists = AccountLog::alias('a')
            ->field('a.*,u.nickname,u.sn as user_sn,u.mobile,w.sn as withdraw_sn')
            ->join('user u', 'u.id = a.user_id')
            ->leftjoin('withdraw_apply w', 'w.sn = a.source_sn')
            ->where($where)
            ->page($get['page'], $get['limit'])
            ->order('a.id desc')
            ->select();

        $count = AccountLog::alias('a')
            ->field('a.*,u.nickname,u.sn as user_sn,u.mobile,w.sn as withdraw_sn')
            ->join('user u', 'u.id = a.user_id')
            ->leftjoin('withdraw_apply w', 'w.sn = a.source_sn')
            ->where($where)
            ->order('a.id desc')
            ->count();

        return ['count' => $count, 'lists' => $lists];
    }

    /**
     * @notes 账户明细
     * @param $get
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/14 10:01 上午
     */
    public static function account($get, $is_export = false)
    {

        $where = [];

        // 明细类型
        $source_type = AccountLog::money_change;
        if (isset($get['type']) && !empty($get['type'])) {
            switch ($get['type']) {
                case 'admin_add_money' :
                    $type = AccountLog::admin_add_money;
                    break;
                case 'admin_reduce_money' :
                    $type = AccountLog::admin_reduce_money;
                    break;
                case 'recharge_money' :
                    $type = AccountLog::recharge_money;
                    break;
                case 'balance_pay_order' :
                    $type = AccountLog::balance_pay_order;
                    break;
                case 'cancel_order_refund' :
                    $type = AccountLog::cancel_order_refund;
                    break;
                case 'after_sale_refund' :
                    $type = AccountLog::after_sale_refund;
                    break;
                case 'withdraw_to_balance' :
                    $type = AccountLog::withdraw_to_balance;
                    break;
                case 'user_transfer_inc_balance' :
                    $type = AccountLog::user_transfer_inc_balance;
                    break;
                case 'user_transfer_dec_balance' :
                    $type = AccountLog::user_transfer_dec_balance;
                    break;
                case 'integral_order_inc_balance' :
                    $type = AccountLog::integral_order_inc_balance;
                    break;
                case 'integral_order_dec_balance' :
                    $type = AccountLog::integral_order_dec_balance;
                    break;
            }
            $where[] = ['a.source_type', '=', $type];
        } else {
            $where[] = ['a.source_type', 'in', $source_type];
        }

        //明细搜索
        if (!empty($get['search_key']) && !empty($get['keyword'])) {
            $keyword = $get['keyword'];
            switch ($get['search_key']) {
                case  'user_sn' :
                    $where[] = ['u.sn', '=', $keyword];
                    break;
                case  'nickname' :
                    $where[] = ['u.nickname', 'like', '%' . $keyword . '%'];
                    break;
            }
        }

        if (empty($get['start_time']) && empty($get['end_time'])) {
            $where[] = ['a.create_time', '>=', strtotime(date("Y-m-d", time()))];
            $where[] = ['a.create_time', '<=', strtotime(date("Y-m-d", time())) + 86399];
        }

        //明细时间
        if (isset($get['start_time']) && $get['start_time'] != '') {
            $where[] = ['a.create_time', '>=', strtotime($get['start_time'])];
        }

        if (isset($get['end_time']) && $get['end_time'] != '') {
            $where[] = ['a.create_time', '<=', strtotime($get['end_time'])];
        }

        // 导出
        if (true === $is_export) {
            return self::accountExport($where);
        }

        $lists = AccountLog::alias('a')
            ->field('a.*,u.nickname,u.sn as user_sn,u.mobile')
            ->join('user u', 'u.id = a.user_id')
            ->where($where)
            ->page($get['page'], $get['limit'])
            ->order('a.id desc')
            ->select();

        $count = AccountLog::alias('a')
            ->field('a.*,u.nickname,u.sn as user_sn,u.mobile')
            ->join('user u', 'u.id = a.user_id')
            ->where($where)
            ->order('a.id desc')
            ->count();

        return ['count' => $count, 'lists' => $lists];
    }

    /**
     * @notes 充值明细
     * @param $get
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/14 10:01 上午
     */
    public static function recharge($get, $is_export = false)
    {

        $where = [];

        //明细搜索
        if (isset($get['search_key']) && !empty($get['search_key'])) {
            $keyword = $get['keyword'];
            switch ($get['search_key']) {
                case  'nickname' :
                    $where[] = ['u.nickname', 'like', '%' . $keyword . '%'];
                    break;
                case  'order_sn' :
                    $where[] = ['order_sn', '=', $keyword];
                    break;
                case  'user_mobile' :
                    $where[] = ['u.mobile', '=', $keyword];
                    break;
            }
        }

        //订单来源
        if (isset($get['order_source']) && $get['order_source'] != '') {
            $where[] = ['r.order_source', '=', $get['order_source']];
        }

        //订单状态
        if (isset($get['pay_status']) && $get['pay_status'] != '') {
            $where[] = ['r.pay_status', '=', $get['pay_status']];
        }

        //支付方式
        if (isset($get['pay_way']) && $get['pay_way'] != '') {
            $where[] = ['r.pay_way', '=', $get['pay_way']];
        }

        if (empty($get['start_time']) && empty($get['end_time'])) {
            $where[] = ['r.create_time', '>=', strtotime(date("Y-m-d", time()))];
            $where[] = ['r.create_time', '<=', strtotime(date("Y-m-d", time())) + 86399];
        }

        //明细开始时间
        if (isset($get['start_time']) && $get['start_time'] != '') {
            $where[] = ['r.create_time', '>=', strtotime($get['start_time'])];
        }
        //明细结束时间
        if (isset($get['end_time']) && $get['end_time'] != '') {
            $where[] = ['r.create_time', '<=', strtotime($get['end_time'])];
        }

        // 导出
        if (true === $is_export) {
            return self::rechargeExport($where);
        }

        $lists = RechargeOrder::alias('r')
            ->field('r.*,u.id,u.nickname,u.mobile')
            ->join('user u', 'u.id = r.user_id')
            ->where($where)
            ->page($get['page'], $get['limit'])
            ->order('r.id desc')
            ->select();
        foreach ($lists as $list) {
            if (!empty($list['pay_time'])) {
                $list['pay_time'] = date('Y-m-d H:i:s', $list['pay_time']);
            }
        }

        $count = RechargeOrder::alias('r')
            ->field('r.*,u.id,u.nickname,u.mobile')
            ->join('user u', 'u.id = r.user_id')
            ->where($where)
            ->order('r.id desc')
            ->count();

        return ['count' => $count, 'lists' => $lists];
    }

    /**
     * @notes 会员佣金提现详情
     * @param $id
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/14 10:01 上午
     */
    public static function detail($id)
    {

        $detail = WithdrawApply::alias('w')
            ->field('w.*,u.sn as user_sn, u.nickname, u.mobile')
            ->leftJoin('user u', 'u.id=w.user_id')
            ->where('w.id', $id)
            ->find();
        $detail['money_qr_code'] = UrlServer::getFileUrl($detail['money_qr_code'] ?? '');
        $detail['typeDesc'] = WithdrawEnum::getTypeDesc($detail['type']);
        $detail['statusDesc'] = WithdrawEnum::getStatusDesc($detail['status']);
        $detail['transfer_time'] = $detail['transfer_time'] ? date('Y-m-d H:i:s', $detail['transfer_time']) : '';
        $detail['payment_time'] = $detail['payment_time'] ? date('Y-m-d H:i:s', $detail['payment_time']) : '';
        return $detail;
    }

    /**
     * @notes 审核通过
     * @param $post
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author suny
     * @date 2021/7/14 10:02 上午
     */
    public static function confirm($post)
    {

        $id = $post['id'];
        $withdraw = WithdrawApply::where('id', $id)
            ->find();

        // 判断提现单是否为待提现状态 1
        if ($withdraw['status'] != 1) {
            return [
                'code' => 0,
                'msg' => '不是待提现申请单'
            ];
        }

        //提现到钱包余额
        if ($withdraw['type'] == WithdrawEnum::TYPE_BALANCE) {
            $user = User::find($withdraw['user_id']);
            $user->user_money = ['inc', $withdraw['left_money']];
            $user->save();
            AccountLogLogic::AccountRecord(
                $withdraw['user_id'],
                $withdraw['left_money'],
                1,
                AccountLog::withdraw_to_balance,
                '',
                $withdraw['id'],
                $withdraw['sn']
            );
            //更新提现申请单状态为提现成功
            WithdrawApply::where('id', $id)
                ->update(['status' => WithdrawEnum::STATUS_SUCCESS, 'update_time' => time(), 'description' => $post['description']]);

            return [
                'code' => 1,
                'msg' => '提现至钱包余额成功'
            ];
        }
        //提现到微信零钱
        if ($withdraw['type'] == WithdrawEnum::TYPE_WECHAT_CHANGE) {
            // 先更新审核备注
            WithdrawApply::where('id', $id)
                ->update(['update_time' => time(), 'description' => $post['description']]);

            //微信零钱接口:1-企业付款到零钱;2-商家转账到零钱
            $transfer_way = ConfigServer::get('withdraw', 'transfer_way',1);
            if ($transfer_way == 1) {
                return WechatCorporatePaymentLogic::pay($withdraw);
            }
            if ($transfer_way == 2) {
                return WechatMerchantTransferLogic::transfer($withdraw);
            }
        }

        //提现到微信收款码、支付收款码
        if ($withdraw['type'] == WithdrawEnum::TYPE_WECHAT_CODE || $withdraw['type'] == WithdrawEnum::TYPE_ALI_CODE || WithdrawEnum::TYPE_BANK) {
            // 直接标识为提现中状态
            WithdrawApply::where('id', $id)
                ->update(['status' => WithdrawEnum::STATUS_ING, 'update_time' => time(), 'description' => $post['description']]);
            return [
                'code' => 1,
                'msg' => '审核通过，提现中'
            ];
        }
    }

    /**
     * @notes 审核拒绝
     * @param $post
     * @throws \think\exception\PDOException
     * @author suny
     * @date 2021/7/14 10:03 上午
     */
    public static function refuse($post)
    {

        Db::startTrans();
        try {
            $withdraw_apply = WithdrawApply::where('id', $post['id'])->find();
            $withdraw_apply->status = WithdrawEnum::STATUS_FAIL; // 提现失败
            $withdraw_apply->description = $post['description'];
            $withdraw_apply->update_time = time();
            $withdraw_apply->save();

            //拒绝提现,回退佣金
            $user = User::find($withdraw_apply['user_id']);
            $user->earnings = ['inc', $withdraw_apply['money']];
            $user->save();

            //增加佣金变动记录
            AccountLogLogic::AccountRecord(
                $withdraw_apply['user_id'],
                $withdraw_apply['money'],
                1,
                AccountLog::withdraw_back_earnings,
                '',
                $withdraw_apply['id'],
                $withdraw_apply['sn']
            );
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
        }
    }

    /**
     * @notes 审核拒绝
     * @param $post
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author suny
     * @date 2021/7/14 10:03 上午
     */
    public static function transferFail($post)
    {

        if (empty($post['transfer_description'])) {
            return [
                'code' => 0,
                'msg' => '请填写转账说明'
            ];
        }
        // 标识提现失败
        WithdrawApply::where('id', $post['id'])->update([
            'status' => 4, // 提现失败
            'transfer_voucher' => $post['transfer_voucher'] ? $post['transfer_voucher'] : '',
            'transfer_description' => $post['transfer_description'],
            'update_time' => time()
        ]);

        $withdraw_apply = WithdrawApply::where('id', $post['id'])->find();
        // 退回佣金
        $user = User::find($withdraw_apply['user_id']);
        $user->earnings = ['inc', $withdraw_apply['money']];
        $user->save();

        //增加佣金变动记录
        AccountLogLogic::AccountRecord(
            $withdraw_apply['user_id'],
            $withdraw_apply['money'],
            1,
            AccountLog::withdraw_back_earnings,
            '',
            $withdraw_apply['id'],
            $withdraw_apply['sn']
        );
        return [
            'code' => 1,
            'msg' => '转账失败，提现金额已退回佣金账户'
        ];
    }

    /**
     * @notes 转账成功
     * @param $post
     * @return array
     * @author suny
     * @date 2021/7/14 10:03 上午
     */
    public static function transferSuccess($post)
    {

        if (empty($post['transfer_voucher'])) {
            return [
                'code' => 0,
                'msg' => '请上传转账凭证'
            ];
        }

        $post['transfer_voucher'] = UrlServer::getFileUrl($post['transfer_voucher']);

        if (empty($post['transfer_description'])) {
            return [
                'code' => 0,
                'msg' => '请填写转账说明'
            ];
        }
        // 标识提现成功
        WithdrawApply::where('id', $post['id'])->update([
            'status' => 3, // 提现成功
            'transfer_voucher' => $post['transfer_voucher'],
            'transfer_description' => $post['transfer_description'],
            'update_time' => time(),
            'transfer_time' => time()
        ]);

        return [
            'code' => 1,
            'msg' => '转账成功'
        ];
    }

    /**
     * @notes 提现失败
     * @param $id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author suny
     * @date 2021/7/14 10:03 上午
     */
    public static function withdrawFailed($id)
    {

        $withdraw_apply = WithdrawApply::where('id', $id)->find();
        $withdraw_apply->status = WithdrawEnum::STATUS_FAIL; // 提现失败
        $withdraw_apply->update_time = time();
        $withdraw_apply->save();

        //拒绝提现,回退佣金
        $user = User::find($withdraw_apply['user_id']);
        $user->earnings = ['inc', $withdraw_apply['money']];
        $user->save();

        //增加佣金变动记录
        AccountLogLogic::AccountRecord(
            $withdraw_apply['user_id'],
            $withdraw_apply['money'],
            1,
            AccountLog::withdraw_back_earnings,
            '',
            $withdraw_apply['id'],
            $withdraw_apply['sn']
        );
    }

    /**
     * @notes 搜索
     * @param $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/14 10:03 上午
     */
    public static function search($id)
    {

        $withdraw = WithdrawApply::where('id', $id)
            ->find();

        // 判断提现单是否为提现中状态 2 且 提现方式为 微信零钱 2
        if ($withdraw['status'] == 2 && $withdraw['type'] == 2) {
            //微信零钱接口:1-企业付款到零钱;2-商家转账到零钱
            $transfer_way = ConfigServer::get('withdraw', 'transfer_way',1);
            if ($transfer_way == 1) {
                return WechatCorporatePaymentLogic::search($withdraw);
            }
            if ($transfer_way == 2) {
                $result = WechatMerchantTransferLogic::details($withdraw);
                // 记录查询结果
                WithdrawApply::update(['update_time'=>time(),'pay_search_desc'=>json_encode($result, JSON_UNESCAPED_UNICODE)],['id'=>$withdraw['id']]);
                if(isset($result['detail_status'])) {
                    if ($result['detail_status'] == 'SUCCESS') {
                        // 转账成功,标记提现申请单为提现成功,记录支付信息
                        WithdrawApply::update(['status'=>3,'payment_no'=>$result['detail_id'],'payment_time'=>strtotime($result['update_time'])],['id'=>$withdraw['id']]);
                        return ['code' => 1, 'msg' => '提现成功'];
                    }
                    if ($result['detail_status'] == 'FAIL') {
                        // 转账失败
                        WithdrawApply::update(['status'=>4],['id'=>$withdraw['id']]);
                        //回退佣金
                        $user = User::find($withdraw['user_id']);
                        $user->earnings = ['inc', $withdraw['money']];
                        $user->save();

                        //增加佣金变动记录
                        AccountLogLogic::AccountRecord(
                            $withdraw['user_id'],
                            $withdraw['money'],
                            1,
                            AccountLog::withdraw_back_earnings,
                            '',
                            $withdraw['id'],
                            $withdraw['sn']
                        );
                        return ['code' => 1, 'msg' => '提现至微信零钱失败'];
                    }
                    if ($result['detail_status'] == 'PROCESSING') {
                        return ['code' => 0, 'msg' => '正在处理中'];
                    }
                }else{
                    return ['code' => 0, 'msg' => $result['message'] ?? '商家转账到零钱查询失败'];
                }
            }
        } else {
            return [
                'code' => 0,
                'msg' => '不是提现中的微信零钱申请单，无法查询'
            ];
        }
    }



    /**
     * @notes 导出Excel
     * @param array $where
     * @return array|false
     * @author 段誉
     * @date 2022/4/24 10:10
     */
    public static function rechargeExport($where)
    {
        try {
            $lists = RechargeOrder::alias('r')
                ->field('r.*,u.id,u.nickname,u.mobile')
                ->join('user u', 'u.id = r.user_id')
                ->where($where)
                ->order('r.id desc')
                ->select()->toArray();

            foreach ($lists as &$list) {
                if (!empty($list['pay_time'])) {
                    $list['pay_time'] = date('Y-m-d H:i:s', $list['pay_time']);
                }
            }

            $excelFields = [
                'order_sn' => '订单编号',
                'nickname' => '用户昵称',
                'mobile' => '用户手机号',
                'order_amount' => '充值金额',
                'give_money' => '赠送金额',
                'give_growth' => '赠送成长值',
                'pay_way' => '支付方式',
                'pay_time' => '支付时间',
                'pay_status' => '订单状态',
                'create_time' => '下单时间',
            ];

            $export = new ExportExcelServer();
            $export->setFileName('充值明细');
            $result = $export->createExcel($excelFields, $lists);

            return ['url' => $result];

        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 导出Excel
     * @param array $where
     * @return array|false
     * @author 段誉
     * @date 2022/4/24 10:10
     */
    public static function accountExport($where)
    {
        try {
            $lists = AccountLog::alias('a')
                ->field('a.*,u.nickname,u.sn as user_sn,u.mobile')
                ->join('user u', 'u.id = a.user_id')
                ->where($where)
                ->order('a.id desc')
                ->select();

            $excelFields = [
                'nickname' => '会员昵称',
                'user_sn' => '会员编号',
                'mobile' => '手机号码',
                'change_amount' => '变动金额',
                'left_amount' => '剩余金额',
                'source_type' => '明细类型',
                'source_sn' => '来源单号',
                'create_time' => '记录时间',
            ];

            $export = new ExportExcelServer();
            $export->setFileName('账户明细');
            $result = $export->createExcel($excelFields, $lists);

            return ['url' => $result];

        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 导出Excel
     * @param array $condition
     * @return array|false
     * @author 段誉
     * @date 2022/4/24 10:10
     */
    public static function commissionExport($where)
    {
        try {
            $lists = AccountLog::alias('a')
                ->field('a.*,u.nickname,u.sn as user_sn,u.mobile,w.sn as withdraw_sn')
                ->join('user u', 'u.id = a.user_id')
                ->leftjoin('withdraw_apply w', 'w.sn = a.source_sn')
                ->where($where)
                ->order('a.id desc')
                ->select();

            $excelFields = [
                'nickname' => '会员昵称',
                'user_sn' => '会员编号',
                'mobile' => '手机号码',
                'change_amount' => '变动金额',
                'left_amount' => '剩余佣金',
                'source_type' => '明细类型',
                'withdraw_sn' => '来源单号',
                'create_time' => '记录时间',
            ];

            $export = new ExportExcelServer();
            $export->setFileName('佣金明细');
            $result = $export->createExcel($excelFields, $lists);

            return ['url' => $result];

        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 导出Excel
     * @param array $condition
     * @return array|false
     * @author 段誉
     * @date 2022/4/24 10:10
     */
    public static function withdrawExport($where)
    {
        try {
            $lists = WithdrawApply::alias('w')
                ->field('w.*, u.nickname,u.avatar, u.sn as user_sn, u.mobile, ul.name as user_level_name')
                ->with('user')
                ->leftJoin('user u', 'u.id = w.user_id')
                ->leftJoin('user_level ul', 'ul.id = u.level')
                ->where($where)
                ->order('w.id desc')
                ->select();

            foreach ($lists as &$item) {
                $item['type_text'] = WithdrawEnum::getTypeDesc($item['type']);
                $item['status_text'] = WithdrawEnum::getStatusDesc($item['status']);
            }

            $excelFields = [
                'sn' => '提现单号',
                'nickname' => '会员昵称',
                'user_sn' => '会员编号',
                'mobile' => '手机号码',
                'left_money' => '提现金额',
                'type_text' => '提现方式',
                'status_text' => '提现状态',
                'remark' => '提现说明',
                'create_time' => '提现时间',
            ];

            $export = new ExportExcelServer();
            $export->setFileName('佣金提现');
            $result = $export->createExcel($excelFields, $lists);

            return ['url' => $result];

        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }
}