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

use app\common\basics\Models;
use app\common\enum\NoticeEnum;
use app\common\enum\SmsEnum;

class SmsLog extends Models
{
    protected $dateFormat = false;
    protected $append = [
        'scene_name'
    ];

    //创建时间
    public function getCreateTimeAttr($value, $data)
    {
        return !empty($value) ? date('Y-m-d H:i:s', $value) : $value;
    }

    //发送时间
    public function getSendTimeAttr($value, $data)
    {
        return !empty($value) ? date('Y-m-d H:i:s', $value) : $value;
    }

    //发送状态
    public function getSendStatusAttr($value, $data)
    {
        return SmsEnum::getSendStatusDesc($value);
    }

    //场景名称
    public function getSceneNameAttr($value, $data)
    {
        return NoticeEnum::getSceneDesc($data['message_key']) ?? '短信通知';
    }

    //发送结果
    public function getResultsAttr($value, $data)
    {
        return $value;
    }

}