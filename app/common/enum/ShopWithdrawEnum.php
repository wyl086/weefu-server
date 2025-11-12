<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\common\enum;

class ShopWithdrawEnum
{
    const TYPE_BANK     = 0;
    const TYPE_ALIPAY   = 10;
    
    const TYPE_ARR = [
        self::TYPE_BANK,
        self::TYPE_ALIPAY,
    ];
    
    const TYPE_TEXT_ARR = [
        self::TYPE_BANK     => '银行卡',
        self::TYPE_ALIPAY   => '支付宝',
    ];
    
    const TYPE_TEXT_ARR2 = [
        [
            self::TYPE_BANK,
            '银行卡'
        ],
        [
            self::TYPE_ALIPAY,
            '支付宝'
        ],
    ];
    
    static function getTypeText($from = true){
        $desc = static::TYPE_TEXT_ARR;
        
        if(true === $from){
            return $desc;
        }
        
        return $desc[$from] ?? '';
    }

    static function type_text_arr3(array $types) : array
    {
        $result = [];
    
        foreach (static::TYPE_TEXT_ARR2 as $item) {
            if (in_array($item[0], $types)) {
                $result[] = $item;
            }
        }
        
        return $result;
    }
}