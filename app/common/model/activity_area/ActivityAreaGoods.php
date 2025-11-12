<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\common\model\activity_area;

use app\common\basics\Models;

class ActivityAreaGoods extends Models{

    //审核状态
    const AUDIT_STATUS_WAIT = 0;
    const AUDIT_STATUS_PASS = 1;
    const AUDIT_STATUS_REFUSE = 2;

    /**
     * @notes 获取活动专区商品的审核状态
     * @param bool $from
     * @return string|string[]
     * @author suny
     * @date 2021/7/13 6:37 下午
     */
    public static function getAuditStatus($from = true){
        $desc = [
            self::AUDIT_STATUS_WAIT     => '待审核',
            self::AUDIT_STATUS_PASS     => '审核通过',
            self::AUDIT_STATUS_REFUSE   => '审核拒绝',
        ];
        if(true === $from){
            return $desc;
        }
        return $desc[$from] ?? '';
    }

    /**
     * @notes 审核状态
     * @param $value
     * @param $data
     * @return string|string[]
     * @author suny
     * @date 2021/7/13 6:37 下午
     */
    public static function getAuditStatusAttr($value, $data){
        return self::getAuditStatus($data['audit_status']);
    }

}