<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\admin\validate\activity_area;

use think\facade\Db;
use app\common\basics\Validate;


class ActivityGoods extends Validate
{
    protected $rule = [
        'review_status' => 'require',
        'description' => 'require',
    ];
    protected $message = [
        'review_status.require' => '请选择审核状态',
        'description.require' => '请填写审核说明',

    ];
    protected $scene = [
        'audit' => ['review_status', 'description'],
        'violation' => ['description'],
    ];
}