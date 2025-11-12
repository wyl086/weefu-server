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


namespace app\shop\validate;

use app\api\controller\Bargain;
use app\common\basics\Validate;
use app\common\model\bargain\Bargain as BargainModel;
use app\common\model\goods\GoodsItem;

/**
 * 砍价活动 数据校验
 * Class BargainValidate
 * @Author 张无忌
 * @package app\admin\validate
 */
class BargainValidate extends Validate
{
    protected $rule = [
        'id' => 'require|checkStatus',
        'goods_id' => 'require|number',
        'time_limit' => 'require',
        'activity_start_time' => 'require',
        'activity_end_time' => 'require|endTime',
        'payment_where' => 'require|in:1,2',
        'knife_type' => 'require|in:1,2',
        'status' => 'require|in:0,1',
        'floor_price' => 'require|checkFloorPrice'
    ];

    protected $message = [
        'id' => 'ID不可为空',
        'id.number' => 'ID必须为数字',
        'goods_id.require' => '未选择砍价商品',
        'goods_id.number' => '选择砍价商品异常',
        'time_limit.require' => '请填写砍价活动有效期',
        'time_limit.number' => '砍价活动有效期必须为数字',
        'activity_start_time.require' => '请选择活动开始时间',
        'activity_end_time.require' => '请选择活动结束时间',
        'payment_where.require' => '请选择购买方式',
        'payment_where.number' => '选择的购买方式异常',
        'knife_type.require' => '请选择砍价金额方式',
        'knife_type.number' => '选择的砍价方式异常',
        'status.require' => '请选择砍价活动状态',
        'status.in' => '砍价状态选择异常',
    ];
    protected $scene = [
        'add' => ['goods_id', 'time_limit', 'activity_start_time', 'activity_end_time', 'payment_where', 'knife_type', 'status', 'floor_price'],
        'edit' => ['id', 'goods_id', 'time_limit', 'activity_start_time', 'activity_end_time', 'payment_where', 'knife_type', 'status', 'floor_price'],
    ];

    public function checkFloorPrice($value, $rule, $data)
    {
        foreach ($value as $item) {
            foreach ($item as $item_id => $floor_price) {
                $goods_price = GoodsItem::where(['id' => $item_id])->value('price');
                if($floor_price >= $goods_price){
                    return '活动底价必须低于商品价格';
                }else{
                    return true;
                }
            }
        }
    }

    /***
     * 验证审核状态，如果是审核通过时进行编辑无需再次审核，如果是审核拒绝时编辑需要再次审核。
     * @param $value
     * @param $rule
     * @param $data
     */
    public function checkStatus($value, $rule, $data)
    {

        $bargain = BargainModel::where('id', $value)->find();
        if ($bargain['status'] == 1) {
            return '该活动正在进行中，无法编辑！';
        } else {
            return true;
        }
    }

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
}