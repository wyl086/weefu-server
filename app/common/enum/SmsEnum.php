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


class SmsEnum
{
    /**
     * 短信发送状态
     */
    const SEND_ING = 0;
    const SEND_SUCCESS = 1;
    const SEND_FAIL = 2;

    public static function getSendStatusDesc($from)
    {
        $desc = [
            self::SEND_ING => '发送中',
            self::SEND_SUCCESS => '发送成功',
            self::SEND_FAIL => '发送失败',
        ];
        if ($from === true) {
            return $desc;
        }
        return $desc[$from] ?? '';
    }
}