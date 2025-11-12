<?php


namespace app\shop\validate\order;


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
        'order_id' => 'require|checkId',
    ];

    protected $message = [
        'order_id.require' => '参数缺失',
    ];

    public function sceneVerification()
    {
        return $this->only(['order_id'])
            ->append('order_id', 'checkVerification');
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
        $result = Order::where('id', $value)->findOrEmpty();
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


}