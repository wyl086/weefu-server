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
namespace app\shop\validate\activity_area;
use think\facade\Db;
use app\common\model\activity_area\ActivityAreaGoods;
use app\common\basics\Validate;


class ActivityGoodsValidate extends Validate{
    protected $rule = [
        'activity_id'    => 'require',
        'goods_id'     => 'require|checkGoods',
    ];
    protected $message = [
        'activity_id.require'       => '请选择活动专区',
        'goods_id.require'       => '请先添加商品',
    ];
    protected $scene = [
        'add' => ['activity_id','goods_id'],
    ];

    protected function checkGoods($value,$rule,$data){
        $activity_goods = ActivityAreaGoods::where(['activity_area_id'=>$data['activity_id'],'goods_id'=>$value[0],'del'=>0])
                        ->find();
        if($activity_goods){
            return '该商品已在该活动专区中，请勿重复添加';
        }
        return true;
    }
}