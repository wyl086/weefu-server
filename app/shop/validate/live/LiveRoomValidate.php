<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 开源版本可自由商用，可去除界面版权logo
// | 商业版本务必购买商业授权，以免引起法律纠纷
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | gitee下载：https://gitee.com/multshop_gitee
// | github下载：https://github.com/multshop-github
// | 访问官网：https://www.multshop.cn
// | 访问社区：https://home.multshop.cn
// | 访问手册：http://doc.multshop.cn
// | 微信公众号：multshop技术社区
// | multshop团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshopTeam
// +----------------------------------------------------------------------
namespace app\shop\validate\live;

use app\common\basics\Validate;
use app\common\model\live\LiveRoom;

/**
 * 直播间验证器
 * Class LiveRoomValidate
 * @package app\shop\validate\live
 */
class LiveRoomValidate extends Validate
{

    protected $rule = [
        'id' => 'require|checkLiveRoom',
        'type' => 'require|in:0',
        'name' => 'require|length:3,17',
        'start_time' => 'require|checkStartTime',
        'end_time' => 'require|checkEndTime',
        'anchor_name' => 'require|length:2,15',
        'anchor_wechat' => 'require',
        'cover_img' => 'require',
        'share_img' => 'require',
        'feeds_img' => 'require',
    ];

    protected $message = [
        'id.require' => '参数缺失',
        'type.require' => '请选择直播类型',
        'type.in' => '直播类型错误',
        'name.require' => '请输入直播间名字',
        'name.length' => '直播间名字长度在3~17个汉字',
        'start_time.require' => '请选择直播开始时间',
        'end_time.require' => '请选择直播结束时间',
        'anchor_name.require' => '请输入主播名称',
        'anchor_name.length' => '主播名称长度在2~15个汉字',
        'anchor_wechat.require' => '请输入主播微信号',
        'cover_img' => '请直播间背景墙',
        'share_img' => '请分享卡片封面',
        'feeds_img' => '请直播卡片封面',
    ];

    protected function sceneAdd()
    {
        return $this->remove(['id' => 'require']);
    }

    protected function sceneDel()
    {
        return $this->only(['id']);
    }


    /**
     * @notes 校验开始时间
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2023/2/15 15:50
     */
    protected function checkStartTime($value, $rule, $data)
    {
        $now = time();
        $start = strtotime($value);
        if (($start - $now) <= 600) {
            return '开播时间需要在当前时间的10分钟后';
        }
        if (($start - $now) >= (180 * 86400)) {
            return '开始时间不能在6个月后';
        }
        return true;
    }


    /**
     * @notes 校验结束时间
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2023/2/15 15:51
     */
    protected function checkEndTime($value, $rule, $data)
    {
        $end = strtotime($value);
        $start = strtotime($data['start_time']);
        if (($end - $start) <= (30 * 60)) {
            return '开播时间和结束时间间隔不得短于30分钟';
        }
        if ($end - $start >= 86400) {
            return '开播时间和结束时间间隔不得超过24小时';
        }
        return true;
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
            'shop_id' => $data['shop_id'],
            'del' => 0
        ])->findOrEmpty();

        if ($room->isEmpty()) {
            return '直播间信息不存在';
        }
        return true;
    }

}