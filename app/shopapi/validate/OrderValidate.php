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

namespace app\shopapi\validate;


use app\common\basics\Validate;
use app\common\enum\OrderEnum;
use app\common\model\Delivery;
use app\common\model\order\Order;
use app\common\model\team\Team;
use app\common\model\team\TeamJoin;

/**
 * 商家移动端订单管理验证器
 * Class OrderValidate
 * @package app\shopapi\validate
 */
class OrderValidate extends Validate
{
    protected $rule = [
        'id' => 'require|checkId',
        'consignee' => 'require',
        'province' => 'require',
        'city' => 'require',
        'district' => 'require',
        'address' => 'require',
        'mobile' => 'require|mobile',
        'send_type' => 'require|in:1,2',
        'shipping_id' => 'requireIf:send_type,1',
        'invoice_no' => 'requireIf:send_type,1',
    ];

    protected $message = [
        'id.require' => '参数缺失',
        'consignee.require' => '请填写收件人',
        'province.require' => '地址参数缺失',
        'city.require' => '地址参数缺失',
        'district.require' => '地址参数缺失',
        'address.require' => '请填写详细',
        'mobile.require' => '请填写手机号码',
        'mobile.mobile' => '请填写正确的手机号码',
        'send_type.require' => '请选择快递方式',
        'send_type.in' => '快递方式参数错误',
        'shipping_id.requireIf' => '请选择快递公司',
        'invoice_no.requireIf' => '请填写快递单号',
    ];

    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    public function sceneCancel()
    {
        return $this->only(['id'])
            ->append('id','checkCancel');
    }

    public function sceneDel()
    {
        return $this->only(['id'])
            ->append('id','checkDel');
    }

    public function sceneEditAddress()
    {
        return $this->only(['id','consignee','province','city','district','address','mobile']);
    }

    public function sceneDelivery()
    {
        return $this->only(['id','send_type','shipping_id','invoice_no'])
            ->append('id','checkDelivery');
    }

    public function sceneConfirm()
    {
        return $this->only(['id'])
            ->append('id','checkConfirm');
    }

    public function sceneLogistics()
    {
        return $this->only(['id'])
            ->append('id','checkLogistics');
    }

    public function sceneGetAddress()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 检验订单是否存在
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2021/11/10 3:25 下午
     */
    public function checkId($value,$rule,$data)
    {
        $result = Order::where(['id'=>$value,'shop_id'=>$data['shop_id'],'del'=>0,'delete'=>0])->findOrEmpty();
        if ($result->isEmpty()) {
            return '订单不存在';
        }
        return true;
    }

    /**
     * @notes 检验订单是否可以取消
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2021/11/10 4:38 下午
     */
    public function checkCancel($value,$rule,$data)
    {
        $result = Order::where(['id'=>$value,'shop_id'=>$data['shop_id']])->findOrEmpty();
        if ($result['order_status'] > OrderEnum::ORDER_STATUS_GOODS) {
            return '订单已发货，不能取消';
        }
        return true;
    }

    /**
     * @notes 检验订单能否删除
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2021/11/10 5:21 下午
     */
    public function checkDel($value,$rule,$data)
    {
        $result = Order::where(['id'=>$value,'shop_id'=>$data['shop_id']])->findOrEmpty();
        if ($result['order_status'] != OrderEnum::ORDER_STATUS_DOWN) {
            return '此订单不可删除';
        }
        return true;
    }

    /**
     * @notes 检验订单能否发货
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2021/11/11 10:25 上午
     */
    public function checkDelivery($value,$rule,$data)
    {
        $result = Order::where(['id'=>$value])->findOrEmpty();

        if ($result['shipping_status'] == 1) {
            return '订单已发货';
        }
        if ($result['order_type'] == OrderEnum::TEAM_ORDER) {
            $join = TeamJoin::where(['order_id' => $result['id']])->findOrEmpty();
            if ($join['status'] != Team::STATUS_SUCCESS) {
                return '已支付的拼团订单需要等待拼团成功后才能发货';
            }
        }
        return true;
    }

    /**
     * @notes 检验订单能否确认收货
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2021/11/11 11:22 上午
     */
    public function checkConfirm($value,$rule,$data)
    {
        $result = Order::where(['id'=>$value])->findOrEmpty();

        if ($result['shipping_status'] != 1 || $result['order_status'] != OrderEnum::ORDER_STATUS_GOODS) {
            return '此订单不允许确认收货';
        }
        return true;
    }

    /**
     * @notes 检验订单是否有物流信息
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2021/11/11 11:34 上午
     */
    public function checkLogistics($value,$rule,$data)
    {
        $order = Order::where(['id'=>$value])->findOrEmpty();
        $delivery = Delivery::where('order_id',$value)->findOrEmpty();

        if (($order['order_status'] != OrderEnum::ORDER_STATUS_GOODS && $order['order_status'] != OrderEnum::ORDER_STATUS_COMPLETE) || $delivery['send_type'] == 2) {
            return '暂无物流信息';
        }

        return true;
    }
}