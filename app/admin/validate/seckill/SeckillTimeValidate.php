<?php
namespace app\admin\validate\seckill;

use think\Validate;
use app\common\model\seckill\SeckillTime;

class SeckillTimeValidate extends Validate
{
    protected $rule = [
        'start_time'        => 'require',
        'end_time'          => 'require|checkTime',
    ];
    protected $message = [
        'start_time.require'        => '请选择开始时间',
        'end_time.require'          => '请选择结束时间',
    ];

    public function checkTime($value,$rule,$data){
        $start_time = strtotime(date('Y-m-d'.$data['start_time']));
        $end_time = strtotime(date('Y-m-d'.$value));
        if($start_time >= $end_time){
            return '开始时间不能大于结束时间';
        }
        $where[] = ['del','=',0];
        if(isset($data['id'])){
            $where[] = ['id','<>',$data['id']];
        }
        $time_list = SeckillTime::where($where)->select()->toArray();
        foreach ($time_list as $item){
            $item_start_time = strtotime(date('Y-m-d'.$item['start_time']));
            $item_end_time = strtotime(date('Y-m-d'.$item['end_time']));
            if($start_time >= $item_start_time && $start_time < $item_end_time ){
                return '秒杀时间段冲突';
            }
            if($end_time >= $item_start_time && $end_time < $item_end_time ){
                return '秒杀时间段冲突';
            }

        }
        return true;
    }
}