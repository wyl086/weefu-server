<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\api\logic;


use app\common\basics\Logic;
use app\common\model\NoticeSetting;


class SubscribeLogic extends Logic
{
    public static function lists($scene)
    {
        $where = [
            ['mnp_notice', '<>', ''],
            ['type', '=', 1]
        ];
        $lists = NoticeSetting::where($where)->field('mnp_notice')->limit(3)->select()->toArray();

        $template_id = [];
        foreach ($lists as $item) {
            if (isset($item['mnp_notice']['status']) && $item['mnp_notice']['status'] != 1) {
                continue;
            }
            $template_id[] = $item['mnp_notice']['template_id'] ?? '';
        }
        return $template_id;
    }
}