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

namespace app\shop\logic\express_assistant;


use app\api\controller\Team;
use app\common\basics\Logic;
use app\common\enum\NoticeEnum;
use app\common\enum\OrderEnum;
use app\common\enum\OrderLogEnum;
use app\common\enum\PayEnum;
use app\common\enum\TeamEnum;
use app\common\logic\OrderLogLogic;
use app\common\model\Delivery;
use app\common\model\goods\GoodsItem;
use app\common\model\order\Order;
use app\common\model\face_sheet\FaceSheetSender;
use app\common\model\face_sheet\FaceSheetTemplate;
use app\common\model\Express;
use app\common\model\order\OrderGoods;
use app\common\model\team\TeamJoin;
use app\common\model\user\User;
use app\common\server\AreaServer;
use app\common\server\printing\Kuaidi100;
use app\common\server\UrlServer;
use think\facade\Db;

/**
 * 电子面单订单打印
 * Class FaceSheetOrderLogic
 * @package app\shop\logic\express_assistant
 */
class FaceSheetOrderLogic extends Logic
{

    /**
     * @notes 获取待发货订单列表
     * @param $get
     * @param $shop_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2023/2/13 17:32
     */
    public static function lists($get, $shop_id)
    {
        $where = [
            ['O.order_status', '=', OrderEnum::ORDER_STATUS_DELIVERY],
            ['O.pay_status', '=', PayEnum::ISPAID],
            ['O.delivery_type', '=', OrderEnum::DELIVERY_TYPE_EXPRESS],
            ['O.shop_id', '=', $shop_id],
        ];

        if (!empty($get['keyword_type']) and !empty($get['keyword'])) {
            switch ($get['keyword_type']) {
                case 'order_sn':
                    $where[] = ['O.order_sn', '=', $get['keyword']];
                    break;
                case 'user_sn':
                    $where[] = ['U.sn', 'like', '%' . $get['keyword'] . '%'];
                    break;
                case 'nickname':
                    $where[] = ['U.nickname', 'like', '%' . $get['keyword'] . '%'];
                    break;
                case 'consignee':
                    $where[] = ['O.consignee', 'like', '%' . $get['keyword'] . '%'];
                    break;
                case 'consignee_mobile':
                    $where[] = ['O.mobile', '=', $get['keyword']];
                    break;
            }
        }

        if (!empty($get['goods_name']) and $get['goods_name']) {
            $where[] = ['OG.goods_name', 'like', '%' . $get['goods_name'] . '%'];
        }

        if (!empty($get['start_time']) and $get['start_time']) {
            $where[] = ['O.create_time', '>=', strtotime($get['start_time'])];
        }

        if (!empty($get['end_time']) and $get['end_time']) {
            $where[] = ['O.create_time', '<=', strtotime($get['end_time'])];
        }

        // 排除未拼团成功订单
        $exclude = TeamJoin::where('status', '<>', TeamEnum::TEAM_STATUS_SUCCESS)->column('order_id');
        $exclude = array_unique($exclude);
        
        $count = Order::where($where)->alias('O')
            ->join('user U', 'U.id = O.user_id')
            ->join('orderGoods OG', 'OG.order_id = O.id')
            ->leftJoin('team_join TJ', "TJ.order_id = O.id and TJ.status = " . TeamEnum::TEAM_STATUS_SUCCESS)
            ->whereNotIn('O.id', $exclude)
            ->count('O.id');

        $lists = Order::alias('O')
            ->field(
                'O.id,O.user_id,O.order_type,O.order_sn,O.order_status,O.total_num,
                O.order_amount,O.delivery_type,O.consignee,O.mobile,O.province,O.city,O.district,
                O.address,O.create_time,TJ.status as team_join_status'
            )
            ->where($where)
            ->whereNotIn('O.id', $exclude)
            ->join('user U', 'U.id = O.user_id')
            ->join('orderGoods OG', 'OG.order_id = O.id')
            ->leftJoin('team_join TJ', "TJ.order_id = O.id and TJ.status = " . TeamEnum::TEAM_STATUS_SUCCESS)
            ->order(['O.id' => 'desc'])
            ->page($get['page'], $get['limit'])
            ->select();

        foreach ($lists as &$item) {
            $user = User::field('nickname,sn')->where(['id' => $item['user_id']])->find();
            $item['user'] = $user['nickname'];

            $item['order_type'] = Order::getOrderType($item['order_type']);
            $item['order_status'] = Order::getOrderStatus($item['order_status']);
            $item['delivery_type'] = Order::getDeliveryType($item['delivery_type']);
            $item['address'] = AreaServer::getAddress([$item['province'], $item['city'], $item['district']], $item['address']);

            $orderGoods = OrderGoods::where(['order_id' => $item['id']])->select();

            $orderGoodsData = [];
            foreach ($orderGoods as $og) {
                $orderGoodsData[] = [
                    'id' => $og['id'],
                    'goods_price' => $og['goods_price'],
                    'goods_num' => $og['goods_num'],
                    'goods_name' => $og['goods_name'],
                    'spec_value_str' => $og['spec_value'],
                    'image' => UrlServer::getFileUrl($og['image']),
                ];
            }
            $item['orderGoods'] = $orderGoodsData;
        }

        return ['count' => $count, 'lists' => $lists];
    }


    /**
     * @notes 打印
     * @param $params
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2023/2/13 18:46
     */
    public static function print($params)
    {
        $template = FaceSheetTemplate::findOrEmpty($params['template_id'])->toArray();
        $sender = FaceSheetSender::findOrEmpty($params['sender_id'])->toArray();
        $express = Express::findOrEmpty($template['express_id'])->toArray();
        $order = Order::with('orderGoods')
            ->where('shop_id', $params['shop_id'])
            ->where('id', $params['order_id'])
            ->findOrEmpty()
            ->toArray();

        $result = self::singlePrint($order, $template, $sender, $express, $params['admin_id'], $params['shop_id']);
        if ($result !== true) {
            // 打印电子面单出错,中断打印
            self::$error = '订单' . $order['order_sn'] . '打印出错：' . $result;
            return false;
        }

        return true;
    }


    /**
     * @notes 打印
     * @param $order
     * @param $template
     * @param $sender
     * @param $express
     * @param $admin_id
     * @return bool|string
     * @author 段誉
     * @date 2023/2/13 18:46
     */
    public static function singlePrint($order, $template, $sender, $express, $admin_id, $shop_id)
    {
        Db::startTrans();
        try {
            $goodsName = '';
            $totalWeight = 0;
            foreach($order['orderGoods'] as $item) {
                if (empty($item['weight'])) {
                    $item['weight'] = GoodsItem::where('id', $item['item_id'])->value('weight');
                }
                $totalWeight += $item['weight'];
                $goodsName .= $item['goods_name'] . ' (' . $item['spec_value'] . $item['goods_num'] . '件)\n';
            }

            // 打印电子面单
            $result = (new Kuaidi100($shop_id))->print([
                'order'         => $order,
                'template'      => $template,
                'sender'        => $sender,
                'express'       => $express,
                'total_weight'   => round($totalWeight, 2),
                'remark'        => $goodsName,
            ]);

            // 添加发货记录
            self::orderDelivery($result, $order, $express, $admin_id);

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }


    /**
     * @notes 发货
     * @param $print
     * @param $order
     * @param $express
     * @param $admin_id
     * @author 段誉
     * @date 2023/2/13 18:44
     */
    public static function orderDelivery($print, $order, $express, $admin_id)
    {
        //添加发货单
        $delivery_id = (new Delivery())->insertGetId([
            'order_id' => $order['id'],
            'order_sn' => $order['order_sn'],
            'user_id' => $order['user_id'],
            'admin_id' => $admin_id,
            'consignee' => $order['consignee'],
            'mobile' => $order['mobile'],
            'province' => $order['province'],
            'city' => $order['city'],
            'district' => $order['district'],
            'address' => $order['address'],
            'invoice_no' => $print['data']['kuaidinum'],
            'send_type' => 1,
            'shipping_id' => $express['id'],
            'shipping_name' => $express['name'],
            'shipping_status' => 1,
            'create_time' => time(),
        ]);

        //更新订单下商品的发货状态
        Order::where('id', $order['id'])->update([
            'order_status' => Order::STATUS_WAIT_RECEIVE,
            'delivery_id' => $delivery_id,
            'shipping_status' => 1,
            'update_time'     => time(),
            'shipping_time' => time(),
        ]);

        //订单日志
        OrderLogLogic::record(
            OrderLogEnum::TYPE_SHOP,
            OrderLogEnum::SHOP_DELIVERY_ORDER,
            $order['id'],
            $admin_id,
            OrderLogEnum::SHOP_DELIVERY_ORDER
        );

        //通知用户发货
        if (!empty($order['mobile'])) {
            event('Notice', [
                'scene' => NoticeEnum::ORDER_DELIVERY_NOTICE,
                'mobile' => $order['mobile'],
                'params' => [
                    'order_id' => $order['id'],
                    'user_id' => $order['user_id'],
                    'shipping_name' => $express['name'],
                    'invoice_no' => $print['data']['kuaidinum'],
                ]
            ]);
        }
    }
}