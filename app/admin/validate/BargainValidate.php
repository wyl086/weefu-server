<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\admin\validate;

use app\common\basics\Validate;

/**
 * 砍价活动 数据校验
 * Class BargainValidate
 * @Author 张无忌
 * @package app\admin\validate
 */
class BargainValidate extends Validate
{
    protected $rule = [
        'id'                  => 'require|number',
        'description'         => [ 'requireCallback' => 'checkRequireDescription' ],
        'goods_id'            => 'require|number',
        'time_limit'          => 'require',
        'activity_start_time' => 'require',
        'activity_end_time'   => 'require|endTime',
        'payment_where'       => 'require|in:1,2',
        'knife_type'          => 'require|in:1,2',
        'status'              => 'require|in:0,1'
    ];

    protected $message = [
        'id'         => 'ID不可为空',
        'description'         => '请填写审核备注',
        'id.number'  => 'ID必须为数字',
        'goods_id.require'   => '未选择砍价商品',
        'goods_id.number'    => '选择砍价商品异常',
        'time_limit.require' => '请填写砍价活动有效期',
        'time_limit.number'  => '砍价活动有效期必须为数字',
        'activity_start_time.require' => '请选择活动开始时间',
        'activity_end_time.require'   => '请选择活动结束时间',
        'payment_where.require' => '请选择购买方式',
        'payment_where.number'  => '选择的购买方式异常',
        'knife_type.require'    => '请选择砍价金额方式',
        'knife_type.number'     => '选择的砍价方式异常',
        'status.require'        => '请选择砍价活动状态',
        'status.in'             => '砍价状态选择异常',
    ];

    protected $scene = [
        'add' => ['goods_id','time_limit','activity_start_time','activity_end_time','payment_where','knife_type','status'],
        'edit' => ['id','goods_id','time_limit','activity_start_time','activity_end_time','payment_where','knife_type','status'],
        'audit' => ['id','description'],
        'violation' => ['id','description'],
    ];

    /**
     * @notes 验证起始时间
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author suny
     * @date 2021/7/14 10:11 上午
     */
    public function endTime($value, $rule, $data)
    {
        if (strtotime($value) <= time()) {
            return '结束时间不能少于当前时间';
        }
        if (strtotime($value) <= strtotime($data['activity_start_time'])) {
            return '结束时间不能少于或等于开始时间';
        }

        return true;
    }
    
    function checkRequireDescription($value, $data)
    {
        if ($this->currentScene == 'audit' && $data['review_status'] == 1) {
            return false;
        }
        
        return true;
    }
}