<?php
namespace app\api\validate;

use think\Validate;
use app\common\server\ConfigServer;
use app\common\model\RechargeTemplate;

class RechargeValidate extends Validate
{
    protected $rule = [
        'id'        => 'checkRecharge', // 充值模板id
        'money'     => 'checkRecharge',
    ];
    protected $message = [
    ];
    protected function checkRecharge($value,$rule,$data){
        $open_racharge = ConfigServer::get('recharge','open_racharge',0);
        if(!$open_racharge){
            return '充值功能已关闭，无法充值';
        }

        if(empty($value) && $data['money']){
            return '请输入充值金额';
        }

        if(isset($data['id'])){
            $remplate = RechargeTemplate::where(['id'=>$value,'del'=>0])->findOrEmpty();
            if($remplate->isEmpty()){
                return '该充值模板不存在';
            }

        }else{
            $min_money = ConfigServer::get('recharge', 'min_money',0);

            if($data['money'] < $min_money){
                return '最低充值金额为'.$min_money;
            }
        }

        return true;
    }
}