<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\common\model\live;

use app\common\basics\Models;
use app\common\enum\LiveRoomEnum;
use app\common\model\shop\Shop;


/**
 * 直播间
 * Class LiveRoom
 * @package app\common\model\live
 */
class LiveRoom extends Models
{

    /**
     * @notes 关联商家
     * @return \think\model\relation\HasOne
     * @author 段誉
     * @date 2023/2/16 12:10
     */
    public function shop()
    {
        return $this->hasOne(Shop::class, 'id', 'shop_id')
            ->field(['id','logo','type','name'])->append(['type_desc']);
    }


    /**
     * @notes 直播时间描述
     * @param $value
     * @param $data
     * @return string
     * @author 段誉
     * @date 2023/2/16 9:39
     */
    public function getLiveTimeTextAttr($value, $data)
    {
        $start = !empty($data['start_time']) ? date('m月d日 H:i', $data['start_time']) : '';
        $end = !empty($data['end_time']) ? date('m月d日 H:i', $data['end_time']) : '';
        return $start . '~' . $end;
    }


    /**
     * @notes 审核状态描述
     * @param $value
     * @param $data
     * @return string|string[]
     * @author 段誉
     * @date 2023/2/16 9:45
     */
    public function getAuditStatusTextAttr($value, $data)
    {
        return LiveRoomEnum::getAuditStatusDesc($data['audit_status']);
    }


    /**
     * @notes 直播状态描述
     * @param $value
     * @param $data
     * @return string|string[]
     * @author 段誉
     * @date 2023/2/16 9:48
     */
    public function getLiveStatusTextAttr($value, $data)
    {
        return LiveRoomEnum::getLiveStatusDesc($data['live_status']);
    }

}