<?php

namespace app\api\validate;

use app\common\basics\Validate;
use app\common\server\ConfigServer;
use app\common\model\user\User;

/**
 * Class WithdrawValidate
 * @package app\api\validate
 */
class WithdrawValidate extends Validate
{
    protected $rule = [
        'id' => 'require', //参数缺失
        'type' => 'require|in:1,2,3,4,5',//提现类型
        'money' => 'require|checkMoney',//提现佣金
        'account' => 'requireIf:type,3|requireIf:type,4|requireIf:type,5',//账户类型
        'real_name' => 'requireIf:type,3|requireIf:type,4|requireIf:type,5|chs',//真实姓名
        'money_qr_code' => 'requireIf:type,3|requireIf:type,4',//收款码
        'bank' => 'requireIf:type,5', // 提现银行
        'subbank' => 'requireIf:type,5', // 银行支行
    ];

    protected $message = [
        'id.require' => '参数缺失',
        'type.require' => '参数错误',
        'type.in' => '提现类型错误',
        'money.require' => '参数错误',
        'account.requireIf' => '请填写账号',
        'real_name.requireIf' => '请填写真实姓名',
        'real_name.chs' => '请填写真实姓名',
        'money_qr_code.requireIf' => '请上传收款码',
        'bank.requireIf' => '请填写提现银行',
        'subbank.requireIf' => '请填写银行支行',
    ];

    /**
     * @notes 申请提现
     * @return WithdrawValidate
     * @author suny
     * @date 2021/7/13 6:30 下午
     */
    public function sceneApply()
    {

        return $this->only(['type', 'money', 'account', 'real_name', 'money_qr_code', 'bank', 'subbank']);
    }

    /**
     * @notes 申请详情
     * @return WithdrawValidate
     * @author suny
     * @date 2021/7/13 6:30 下午
     */
    public function sceneInfo()
    {

        return $this->only(['id']);
    }

    /**
     * @notes 提现佣金验证
     * @param $value
     * @param $rule
     * @param array $data
     * @return bool|string
     * @author suny
     * @date 2021/7/13 6:30 下午
     */
    protected function checkMoney($value, $rule, $data = [])
    {

        $able_withdraw = User::where('id', $data['user_id'])->value('earnings');
        if ($value > $able_withdraw) {
            return '可提现金额不足';
        }

        //1.最低提现金额
        $min_withdraw = ConfigServer::get('withdraw', 'min_withdraw', 0);
        if ($value < $min_withdraw) {
            return '最低提现' . $min_withdraw . '元';
        }

        //2,最高提现金额
        $max_withdraw = ConfigServer::get('withdraw', 'max_withdraw', 0);
        if ($value > $max_withdraw) {
            return '最高提现' . $max_withdraw . '元';
        }
        return true;
    }
}