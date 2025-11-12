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
use app\common\enum\LiveGoodsEnum;
use app\common\model\live\LiveGoods;

/**
 * 直播商品验证器
 * Class LiveGoodsValidate
 * @package app\admin\validate\live
 */
class LiveGoodsValidate extends Validate
{

    protected $rule = [
        'id' => 'require|checkLiveGoods',
        'status' => 'require|in:' . LiveGoodsEnum::SYS_AUDIT_STATUS_WAIT_WECHAT . ',' . LiveGoodsEnum::SYS_AUDIT_STATUS_FAIL,
    ];


    protected $message = [
        'id.require' => '参数缺失',
        'status.require' => '状态参数缺失',
        'status.in' => '状态参数异常',
    ];


    protected function sceneAudit()
    {
        return $this->only(['id', 'status']);
    }


    protected function sceneDel()
    {
        return $this->only(['id']);
    }


    protected function sceneDetail()
    {
        return $this->only(['id']);
    }
    

    /**
     * @notes 校验直播商品
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2023/2/16 11:10
     */
    protected function checkLiveGoods($value, $rule, $data)
    {
        $room = LiveGoods::where([
            'id' => $value,
            'del' => 0
        ])->findOrEmpty();

        if ($room->isEmpty()) {
            return '直播商品不存在';
        }
        return true;
    }

}