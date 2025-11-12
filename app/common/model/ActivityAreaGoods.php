<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\common\model;
use think\Model;

class ActivityAreaGoods extends Model{

    //审核状态
    const AUDIT_STATUS_WAIT = 0;
    const AUDIT_STATUS_PASS = 1;
    const AUDIT_STATUS_REFUSE = 2;

    /**
     * Notes:获取活动专区商品的审核状态
     * @param bool $from
     * @return array|mixed|string
     * @author: cjhao 2021/4/16 11:09
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

}