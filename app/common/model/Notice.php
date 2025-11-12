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

/**
 * 通知
 * Class Notice
 * @package app\common\model
 */
class Notice extends Models
{
    protected $append = [
        'scene_desc'
    ];

    //场景描述
    public function getSceneDescAttr($value, $data)
    {
        return NoticeEnum::getSceneDesc($data['scene']);
    }







}