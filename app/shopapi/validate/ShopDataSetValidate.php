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


namespace app\shopapi\validate;
use app\common\basics\Validate;
use app\common\enum\ShopEnum;

/**
 * 商家信息验证
 * Class ShopInfoValidate
 * @package app\shopapi\validate
 */
class ShopDataSetValidate extends Validate
{

    protected $rule = [
        'dataset'           => 'require|checkData',
    ];
    protected $message = [
        'dataset.require'   => '请输入信息',
    ];


    protected $allow_fields = [
        'nickname',                                 //联系人
        'mobile'            => 'checkMobile',       //联系电话
        'intro',                                    //简介
        'is_run'            => 'checkRund',         //营业状态
        'province_id',                              //省id
        'city_id',                                  //城市id
        'district_id',                              //区id
        'address',                                  //详情地址
        'refund_address'    => 'checkRefund',       //退款地址
        'weekdays'          => 'checkWeekDay',      //营业时间
        'run_start_time'    => 'checkTime',         //每天营业开始时间
        'run_end_time'      => 'checkTime',          //每天营业结束时间
        'open_invoice',                             //发票开关 0- 关闭 1-开启
        'spec_invoice',                             //是否支持专票 0-不支持 1-支持
    ];

    protected function checkData($dataset,$rule,$data){
        foreach ($dataset as $field => $value){

            $allow_fields = array_keys($this->allow_fields);
            if(!in_array($field,$allow_fields)){
                return '该信息不允许修改';
            }

            $func = $this->allow_fields[$field] ?? '';
            if($func){
                $result = call_user_func([ShopDataSetValidate::class,$func],$data);
                if(true !== $result){
                    return $result;
                }
            }
        }
        return true;
    }

    //验证手机号码是否正确
    protected function checkMobile($data){
        $mobile = $data['mobile'];
        $check = $this->checkRule($mobile, 'mobile');
        if(false === $check){
            return '手机号格式错误';
        }
        return true;
    }

    //验证营业状态是否正确
    protected function checkRund($data){
       if(!in_array($data['is_run'],[ShopEnum::SHOP_RUN_CLOSE,ShopEnum::SHOP_RUN_OPEN])){
            return '营业状态错误';
       }
       return true;
    }

    //验证营业天是否正确
    protected function checkWeekDay($data){
        foreach ($data['weekdays'] as $day){
            if(!in_array($day,[0,1,2,3,4,5,6])){
                return '工作日错误';
            }
        }
        return true;
    }

    //验证时间是否正确
    protected function checkTime($data){
        if(empty($data['run_start_time']) || empty($data['run_end_time'])){
            return '请选择营业时间';
        }
        if($data['run_start_time'] >= $data['run_end_time']){
            return '营业时间错误';
        }
        return true;
    }

    //验证退款信息
    protected function checkRefund($data){
        $refund_address = $data['refund_address'];
        if(empty($refund_address)){
            return '请输入退款地址';
        }

        foreach ($refund_address as $index => $value){
            switch ($index){
                case "nickname":
                    if(empty($value)){
                        return '请输入退款联系人';
                    }
                    break;
                case "mobile":
                    $check = $this->checkRule($value, 'mobile');
                    if(false === $check){
                        return '手机号格式错误';
                    }
                    break;
                case "province_id":
                    if(empty($value)){
                        return '请选择省份';
                    }
                    break;
                case "city_id":
                    if(empty($value)){
                        return '请选择城市';
                    }
                    break;
                case "district_id":
                    if(empty($value)){
                        return '请选择地区';
                    }
                    break;
                case "address":
                    if(empty($value)){
                        return '请输入详情地址';
                    }
                    break;
            }
        }
        return true;
    }


}