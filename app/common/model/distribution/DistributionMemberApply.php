<?php
namespace app\common\model\distribution;

use app\common\basics\Models;

class DistributionMemberApply extends Models
{
    /**
     * 分销会员申请状态
     */
    const STATUS_WAIT_AUDIT    = 0; //待审核
    const STATUS_AUDIT_SUCCESS = 1; //审核通过
    const STATUS_AUDIT_ERROR   = 2; //审核拒绝


    /**
     * @Notes: 关联用户模型
     * @Author: 张无忌
     */
    public function user()
    {
        return $this->hasOne('app\common\model\user\User', 'id', 'user_id');
    }


    /**
     * @Notes: 分销会员申请状态
     * @param bool $status
     * @return array|mixed|string
     */
    public static function getApplyStatus($status = true)
    {
        $desc = [
            self::STATUS_WAIT_AUDIT    => '待审核',
            self::STATUS_AUDIT_SUCCESS => '审核通过',
            self::STATUS_AUDIT_ERROR   => '审核拒绝',
        ];
        if ($status === true) {
            return $desc;
        }
        return $desc[$status] ?? '未知';
    }
}