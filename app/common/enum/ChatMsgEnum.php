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


class ChatMsgEnum
{

    const TYPE_TEXT = 1; // 文本
    const TYPE_IMG = 2; // 图片
    const TYPE_GOODS = 3; // 商品


    public static function getMsgType($type = true)
    {
        $desc = [
            self::TYPE_TEXT => '文本',
            self::TYPE_IMG => '图片',
            self::TYPE_GOODS => '商品',
        ];
        if ($type === true) {
            return $desc;
        }
        return $desc[$type] ?? '';
    }

}