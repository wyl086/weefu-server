<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | gitee下载：https://gitee.com/multshop_gitee
// | github下载：https://github.com/multshop-github
// | 访问官网：https://www.multshop.cn
// | 访问社区：https://home.multshop.cn
// | 访问手册：http://doc.multshop.cn
// | 微信公众号：multshop技术社区
// | multshop系列产品在gitee、github等公开渠道开源版本可免费商用，未经许可不能去除前后端官方版权标识
// |  multshop系列产品收费版本务必购买商业授权，购买去版权授权后，方可去除前后端官方版权标识
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\shop\validate\coupon;

use think\Validate;

class CouponValidate extends Validate{
    protected $rule = [
        'name'              => 'require|max:16',                        //优惠券名称
        'send_time_start'   => 'require|checkSendTime',                 //优惠券发放开始时间
        'send_time_end'     => 'require',                               //优惠券发放结束时间
        'money'             => 'require|gt:0',                          //优惠券面额
        'send_total_type'   => 'require|in:1,2',                        //发送总量类型：1-不限制；2-限制张数
        'send_total'        => 'requireIf:send_total_type,2|gt:0',      //发送总量类型为2时：该字段为限制的张数
        'condition_type'    => 'require|in:1,2',                        //使用条件类型：1-无门槛；2-订单满足金额
        'condition_money'   => 'requireIf:condition_type,2|gt:0',       //使用条件类型为2时：该字段为订单满足金额可使用
        'use_time_type'     => 'require|in:1,2,3',                      //用券时间类型：1-固定时间；2-领券当天起；3-领券次日起
        'use_time_start'    => 'requireIf:use_time_type,1|checkUseTime',//用券时间类型为1时：该字段为使用开始时间
        'use_time_end'      => 'requireIf:use_time_type,1',             //用券时间类型为1时：该字段为使用结束时间
        'use_time'          => 'requireIf:use_time_type,2',             //用券时间类型为2时：该字段为多少天内可用
        'tomorrow_use_time' => 'requireIf:use_time_type,3',             //用券时间类型为3时：该字段为多少天内可用
        'get_type'          => 'require|in:1,2',                        //领取类型：1-直接领取；2-商家赠送
        'get_num_type'      => 'require|in:1,2,3',                      //领取次数类型：1-不限制领取次数；2-限制次数；3-每天限制数量
        'get_num'           => 'requireIf:get_num_type,2',              //领取次数类型为：2时：该字段为领取限制的数量
        'use_goods_type'    => 'require|in:1,2,3',                      //适用商品类型:1-全部商品；2-部分商品可用；3-部分商品不可用
    ];
    protected $message = [
        'name.require'                  => '请输入优惠券名称',
        'name.max'                      => '优惠券名称长度最多16个字符',
        'send_time_start.require'       => '请选择优惠券开始发放时间',
        'send_time_end.require'         => '请选择优惠券结束发放时间',
        'money.require'                 => '请输入优惠券面额',
        'money.gt'                      => '优惠券面额必须大于零',
        'send_total_type.require'       => '请选择发放总量',
        'send_total_type.in'            => '发放总量类型错误',
        'send_total.requireIf'          => '请填写限制张数',
        'send_total.gt'                 => '限制张数必须大于0',
        'condition_type.require'        => '请选择使用门槛',
        'condition_type.in'             => '使用门槛类型错误',
        'condition_money.gt'            => '使用门槛金额不可小于0',
        'use_time_type.require'         => '请选择用券时间',
        'use_time_start.requireIf'      => '请选择优惠券使用时间',
        'use_time_end.requireIf'        => '请选择优惠券使用时间',
        'use_time.requireIf'            => '请选择优惠券使用时间',
        'tomorrow_use_time.requireIf'   => '请选择优惠券使用时间',
        'get_type.require'              => '请选择领取方式',
        'get_type.in'                   => '领取方式类型错误',
        'get_num_type.require'          => '请选择领取次数',
        'get_num_type.in'               => '领取次数类型错误',
        'get_num.requireIf'             => '请输入领取次数',
        'day_get_num.requireIf'         => '请输入领取次数',
        'use_goods_type.require'        => '请选择适用商品',
        'use_goods_type.in'             => '适用商品类型错误',
    ];

    public function checkUseTime($value,$rule,$data){
        if($value && strtotime($value) == strtotime($data['use_time_end'])){
            return '用券时间开始时间和结束时间不能相同';
        }
        return true;
    }

    public function checkSendTime($value,$rule,$data){
        if($value && strtotime($value) == strtotime($data['send_time_end'])){
            return '发放开始时间和结束时间不能相同';
        }

        if (strtotime($data['send_time_end']) < strtotime($value)) {
            return '发放结束时间不能小于发放开始时间';
        }

        return true;
    }

}

