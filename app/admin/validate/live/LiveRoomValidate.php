<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | multshop团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshopTeam
// +----------------------------------------------------------------------
namespace app\admin\validate\live;

use app\common\basics\Validate;
use app\common\enum\LiveRoomEnum;
use app\common\model\live\LiveRoom;

/**
 * 直播间验证器
 * Class LiveRoomValidate
 * @package app\admin\validate\live
 */
class LiveRoomValidate extends Validate
{

    protected $rule = [
        'id' => 'require|checkLiveRoom',
        'status' => 'require|in:' . LiveRoomEnum::AUDIT_STATUS_SUCCESS . ',' . LiveRoomEnum::AUDIT_STATUS_FAIL,
        'sort' => 'require|integer|egt:0',
    ];


    protected $message = [
        'id.require' => '参数缺失',
        'status.require' => '审核参数缺失',
        'status.in' => '审核参数异常',
        'sort.require' => '请填写推荐值',
        'sort.integer' => '推荐值需为整数',
        'sort.egt' => '推荐值需大于或等于0',
    ];


    protected function sceneAudit()
    {
        return $this->only(['id', 'status']);
    }

    protected function sceneRecommend()
    {
        return $this->only(['id', 'sort']);
    }


    /**
     * @notes 校验直播间
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2023/2/16 11:10
     */
    protected function checkLiveRoom($value, $rule, $data)
    {
        $room = LiveRoom::where([
            'id' => $value,
            'del' => 0
        ])->findOrEmpty();

        if ($room->isEmpty()) {
            return '直播间信息不存在';
        }
        return true;
    }

}