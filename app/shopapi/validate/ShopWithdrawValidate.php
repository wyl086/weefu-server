<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 开源版本可自由商用，可去除界面版权logo
// | 商业版本务必购买商业授权，以免引起法律纠纷
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | gitee下载：https://gitee.com/multshop_gitee
// | github下载：https://github.com/multshop-github
// | 访问官网：https://www.multshop.cn
// | 访问社区：https://home.multshop.cn
// | 访问手册：http://doc.multshop.cn
// | 微信公众号：multshop技术社区
// | multshop团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshopTeam
// +----------------------------------------------------------------------


namespace app\shopapi\validate;

use app\common\basics\Validate;
use app\common\model\shop\Shop;
use app\common\model\shop\ShopBank;
use app\common\server\ConfigServer;

/**
 * 商家提现验证
 * Class ShopWithdrawValidate
 * @package app\shopapi\validate
 */
class ShopWithdrawValidate extends Validate{
    protected $rule = [
        'money'             => 'require|gt:0|checkMoney',
        'bank_id'           => 'require|checkBank',
    ];

    protected $message = [
        'money.require'         => '请输入提现金额',
        'money.gt'              => '提现金额必须大于零',
        'bank_id.require'       => '请现在提现账户',
    ];

    //验证提现金额是否满足条件
    protected function checkMoney($value,$rule,$data){
        $min_withdrawal_money = ConfigServer::get('shop_withdrawal', 'min_withdrawal_money', 0);
        $max_withdrawal_money = ConfigServer::get('shop_withdrawal', 'max_withdrawal_money', 0);

        $wallet = Shop::where(['id'=>$data['shop_id']])
            ->value("wallet");

        if($wallet < $value){
            return '当前账户仅剩：'.$wallet.'元';
        }
        if($min_withdrawal_money > $value){
            return '最低提现金额：'.$min_withdrawal_money.'元';
        }
        if($max_withdrawal_money < $value){
            return '最高提现金额：'.$max_withdrawal_money.'元';
        }
        return true;
    }

    //验证提现账户是否存在
    protected function checkBank($value,$rule,$data){
        if(ShopBank::where(['id'=>$value,'shop_id'=>$data['shop_id'],'del'=>0])->find()){
            return true;
        }
        return '账户不存在，请重新选择';
    }

}