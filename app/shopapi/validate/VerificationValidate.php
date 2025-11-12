<?php


namespace app\shopapi\validate;


use app\common\basics\Validate;
use app\common\enum\OrderEnum;
use app\common\enum\PayEnum;
use app\common\enum\TeamEnum;
use app\common\model\order\Order;
use app\common\model\team\TeamJoin;

/**
 * 自提核销验证
 * Class VerificationValidate
 * @package app\shop\validate\order
 */
class VerificationValidate extends Validate
{
    protected $rule = [
        'id' => 'require|checkId',
        'pickup_code' => 'require|checkPickupCode',
    ];

    protected $message = [
        'id.require' => '参数缺失',
        'pickup_code.require' => '请填写核销码',
    ];


    /**
     * @notes 订单详情
     * @return VerificationValidate
     * @author 段誉
     * @date 2022/11/2 18:18
     */
    public function sceneDetail()
    {
        return $this->only(['pickup_code']);
    }


    /**
     * @notes 确认核销
     * @return VerificationValidate
     * @author 段誉
     * @date 2022/11/2 18:18
     */
    public function sceneConfirm()
    {
        return $this->only(['id'])
            ->append('id', 'checkVerification');
    }


    /**
     * @notes 校验订单
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2022/11/2 14:41
     */
    public function checkId($value, $rule, $data)
    {
        $result = Order::where('id', $value)->findOrEmpty();
        if ($result->isEmpty()) {
            return '订单不存在';
        }
        return true;
    }


    /**
     * @notes 校验订单是否可以提货核销
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2022/11/2 14:42
     */
    public function checkVerification($value, $rule, $data)
    {
        $result = Order::where('id', $value)
            ->where('shop_id', $data['shop_id'])
            ->findOrEmpty();
        if ($result['pay_status'] != PayEnum::ISPAID) {
            return '订单未支付，不允许核销';
        }
        if ($result['delivery_type'] != OrderEnum::DELIVERY_TYPE_SELF) {
            return '非自提订单，不允许核销';
        }
        if ($result['verification_status'] == OrderEnum::WRITTEN_OFF) {
            return '订单已核销';
        }
        if ($result['order_type'] == OrderEnum::TEAM_ORDER) {
            $teamcheck = TeamJoin::where(['order_id' => $value, 'status' => TeamEnum::TEAM_STATUS_SUCCESS])->findOrEmpty();
            if ($teamcheck->isEmpty()) {
                return '拼团成功后才能核销';
            }
        }
        return true;
    }


    /**
     * @notes 收货码核销
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/11/2 18:04
     */
    public function checkPickupCode($value,$rule,$data)
    {
        $result = Order::where(['pickup_code'=>$value, 'shop_id' => $data['shop_id']])->find();
        if (empty($result)) {
            return '提货码不正确';
        }
        if ($result['order_status'] != OrderEnum::ORDER_STATUS_DELIVERY) {
            return '订单不允许核销';
        }
        if ($result['delivery_type'] != OrderEnum::DELIVERY_TYPE_SELF) {
            return '不是自提订单，不允许核销';
        }
        if ($result['verification_status'] == OrderEnum::WRITTEN_OFF) {
            return '订单已核销';
        }
        if ($result['order_type'] == OrderEnum::TEAM_ORDER){
            $teamcheck = TeamJoin::where(['order_id' => $result['id'], 'status' => TeamEnum::TEAM_STATUS_SUCCESS])->findOrEmpty();
            if ($teamcheck->isEmpty()) {
                return '拼团成功后才能核销';
            }
        }
        return true;
    }


}