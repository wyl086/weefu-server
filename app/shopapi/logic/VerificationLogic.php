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

namespace app\shopapi\logic;


use app\common\basics\Logic;
use app\common\enum\OrderEnum;
use app\common\enum\OrderLogEnum;
use app\common\enum\PayEnum;
use app\common\enum\VerificationEnum;
use app\common\logic\OrderLogLogic;
use app\common\model\order\Order;
use app\common\model\order\Verification;
use think\facade\Db;


/**
 * 自提核销逻辑
 * Class VerificationLogic
 * @package app\shopapi\logic
 */
class VerificationLogic extends Logic
{

    /**
     * @notes 核销订单列表
     * @param $params
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/11/2 17:40
     */
    public static function lists($params, $pageNo, $pageSize, $shopId)
    {
        $where[] = ['delivery_type', '=', OrderEnum::DELIVERY_TYPE_SELF];
        $where[] = ['pay_status', '=', PayEnum::ISPAID];
        $where[] = ['shop_id', '=', $shopId];

        $verificationStatus = 0;
        if (isset($params['status'])) {
            $verificationStatus = $params['status'];
        }
        $where[] = ['verification_status', '=', $verificationStatus];

        $lists = Order::field('id,address,verification_status,consignee,verification_status')
            ->with(['order_goods' => function ($query) {
                $query->field('order_id,image,goods_name,goods_num,spec_value');
            }])
            ->where($where)
            ->append(['verification_status_text'])
            ->order(['id' => 'desc'])
            ->select()
            ->toArray();

        $count = Order::where($where)->count();

        return  [
            'list' => $lists,
            'page' => $pageNo,
            'size' => $pageSize,
            'count' => $count,
            'more' => is_more($count, $pageNo, $pageSize)
        ];
    }


    /**
     * @notes 订单详情
     * @param $params
     * @param $shopId
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/11/2 18:08
     */
    public static function detail($params, $shopId)
    {
        $detail = Order::where('pickup_code',$params['pickup_code'])
            ->where('shop_id', $shopId)
            ->with([
                'user',
                'order_goods' => function ($query) {
                    $query->field('order_id,image,goods_name,goods_num,spec_value');
                }
            ])
            ->append(['verification_status_text'])
            ->field('id,address,verification_status,consignee,verification_status,consignee,mobile')
            ->find()
            ->toArray();
        
        $detail['show_verification_nickname']   = $detail['consignee'] ? : ($detail['user']['nickname'] ?? '');
        $detail['show_verification_mobile']     = $detail['mobile'] ? : ($detail['user']['mobile'] ?? '');
        
        if ($detail['show_verification_mobile']) {
            $detail['show_verification_mobile'] = substr_replace($detail['show_verification_mobile'], '****', 3, 4);
        }

        return $detail;
    }


    /**
     * @notes 核销订单
     * @param $params
     * @param $adminInfo
     * @return bool
     * @author 段誉
     * @date 2022/11/2 17:11
     */
    public static function verification($params, $adminInfo)
    {
        Db::startTrans();
        try {
            $order = Order::find($params['id']);

            //添加核销记录
            Verification::create([
                'order_id' => $order['id'],
                'shop_id' => $order['shop_id'],
                'handle_id' => $adminInfo['id'],
                'verification_scene' => VerificationEnum::TYPE_SHOP,
                'snapshot' => json_encode([
                    'sn' => $adminInfo['account'],
                    'name' => $adminInfo['name']
                ]),
            ]);

            //更新订单状态
            $order->order_status = OrderEnum::ORDER_STATUS_COMPLETE;
            $order->verification_status = OrderEnum::WRITTEN_OFF;
            $order->confirm_take_time = time();
            $order->save();

            //订单日志
            OrderLogLogic::record(
                OrderLogEnum::TYPE_SHOP,
                OrderLogEnum::SHOP_VERIFICATION,
                $order['id'],
                $adminInfo['id'],
                OrderLogEnum::SHOP_VERIFICATION
            );

            Db::commit();
            return true;

        } catch (\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }

}