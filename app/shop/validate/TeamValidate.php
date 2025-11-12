<?php


namespace app\shop\validate;


use app\common\basics\Validate;

class TeamValidate extends Validate
{
    protected $rule = [
        'id'                  => 'require',
        'goods_id'            => 'require',
        'people_num'          => 'require',
        'effective_time'      => 'require',
        'activity_start_time' => 'require',
        'activity_end_time'   => 'require',
    ];

    protected $message = [
        'id.require'                  => '缺少id字段',
        'goods_id.require'            => '请选择商品',
        'people_num.require'          => '请填写成团人数',
        'effective_time.require'      => '请填写团失效时长',
        'activity_start_time.require' => '请选择团开始时间',
        'activity_end_time.require'   => '请选择团结束时间',
    ];

    protected $scene = [
        'id'   => ['id'],
        'add'  => ['goods_id', 'people_num', 'effective_time', 'activity_start_time', 'activity_end_time'],
        'edit' => ['id', 'goods_id', 'people_num', 'effective_time', 'activity_start_time', 'activity_end_time'],
    ];
}