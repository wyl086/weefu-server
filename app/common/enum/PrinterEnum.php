<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | author: multshopTeam
// +----------------------------------------------------------------------
namespace app\common\enum;
/**
 * 小票打印机类型
 * Class PrinterEnum
 * @package app\common\enum
 */
class PrinterEnum {
    const LYL_PRINTER = 'YLYP';

    /**
     * @notes 获取小票打印机列表
     * @param bool $from
     * @return array|mixed|string
     * @author cjhao
     * @date 2021/9/30 10:23
     */
    public static function getPrinterDesc($from = true)
    {
        $desc = [
            self::LYL_PRINTER => '易联云',
        ];
        if(true === $from){
            return $desc;
        }
        return $desc[$from] ?? '';

    }

}