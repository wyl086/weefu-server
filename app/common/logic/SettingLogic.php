<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\common\logic;

use app\common\basics\Logic;
use app\common\enum\ShopWithdrawEnum;
use app\common\server\ConfigServer;

class SettingLogic extends Logic
{
    static function getShopWithdraw()
    {
        $detail['withdrawal_type']              = ConfigServer::get('shop_withdrawal', 'withdrawal_type', [ ShopWithdrawEnum::TYPE_BANK ]);
    
        foreach ($detail['withdrawal_type'] as &$withdrawal_type) {
            $withdrawal_type = intval($withdrawal_type);
        }
        
        $detail['min_withdrawal_money']         = ConfigServer::get('shop_withdrawal', 'min_withdrawal_money', 0);
        $detail['max_withdrawal_money']         = ConfigServer::get('shop_withdrawal', 'max_withdrawal_money', 0);
        $detail['withdrawal_service_charge']    = ConfigServer::get('shop_withdrawal', 'withdrawal_service_charge', 0);
        
        return $detail;
    }
    
    static function setShopWithdraw($data)
    {
        ConfigServer::set('shop_withdrawal', 'withdrawal_type', array_values($data['withdrawal_type'] ?? []));
        ConfigServer::set('shop_withdrawal', 'min_withdrawal_money', $data['min_withdrawal_money'] ?? 0);
        ConfigServer::set('shop_withdrawal', 'max_withdrawal_money', $data['max_withdrawal_money'] ?? 0);
        ConfigServer::set('shop_withdrawal', 'withdrawal_service_charge', $data['withdrawal_service_charge'] ?? 0);
    }
}