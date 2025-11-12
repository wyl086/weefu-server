<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\admin\validate\integral;


use app\common\basics\Validate;
use app\common\enum\IntegralGoodsEnum;
use app\common\enum\IntegralOrderEnum;
use app\common\model\integral\IntegralOrder;

class IntegralOrderValidate extends Validate
{
    protected $rule = [
        'id' => 'require',
    ];

    protected $message = [
        'id.require' => '参数错误',
    ];

    public function sceneDeliveryHandle()
    {
        return $this->only(['id'])
            ->append('id','checkDeliveryHandle');
    }

    public function sceneConfirm()
    {
        return $this->only(['id'])
            ->append('id','checkConfirm');
    }

    public function sceneCancel()
    {
        return $this->only(['id'])
            ->append('id','checkCancel');
    }


    /**
     * @notes 检验订单能否发货
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2022/3/3 12:06 下午
     */
    public function checkDeliveryHandle($value,$rule,$data)
    {
        $result = IntegralOrder::where(['id'=>$value,'del'=>0])->findOrEmpty()->toArray();
        if (!$result) {
            return '订单不存在';
        }
        if ($result['delivery_way'] == 0) {
            return '订单无需快递';
        }
        if (!isset($data['shipping_id']) || $data['shipping_id'] == '') {
            return '请选择快递';
        }
        if (!isset($data['invoice_no']) || $data['invoice_no'] == '') {
            return '请输入快递单号';
        }
        if ($result['shipping_status'] == 1) {
            return '订单已发货';
        }
        if ($result['order_status'] != IntegralOrderEnum::ORDER_STATUS_DELIVERY) {
            return '订单状态不正确，无法发货';
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
     * @date 2022/3/3 3:37 下午
     */
    public function checkConfirm($value,$rule,$data)
    {
        $result = IntegralOrder::where(['id'=>$value,'del'=>0])->findOrEmpty()->toArray();
        if (!$result) {
            return '订单不存在';
        }
        if ($result['order_status'] != IntegralOrderEnum::ORDER_STATUS_GOODS) {
            return '订单状态不正确，无法确认收货';
        }
        return true;
    }




    /**
     * @notes 取消订单验证
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2022/3/3 17:58
     */
    public function checkCancel($value, $rule, $data)
    {
        $order = IntegralOrder::findOrEmpty($value);
        $goods_snap = $order['goods_snap'];

        if ($order->isEmpty()) {
            return '订单不存在';
        }

        // 商品类型为红包的不可取消
        if ($goods_snap['type'] == IntegralGoodsEnum::TYPE_BALANCE) {
            return '类型为红包的订单不可取消';
        }

        if ($order['order_status'] >= IntegralOrderEnum::ORDER_STATUS_GOODS) {
            return '已发货订单不可取消';
        }

        return true;
    }

}