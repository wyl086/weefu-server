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

class NoticeSetting extends Models
{
    protected $name = 'dev_notice_setting';

    protected $json = ['variable', 'system_notice', 'sms_notice', 'oa_notice', 'mnp_notice'];

    protected $jsonAssoc = true;

    public function getSceneAttr($value, $data)
    {
        return NoticeEnum::getSceneDesc($value);
    }
}