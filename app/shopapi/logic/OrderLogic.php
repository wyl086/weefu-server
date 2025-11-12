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
use app\common\enum\NoticeEnum;
use app\common\enum\OrderEnum;
use app\common\enum\OrderLogEnum;
use app\common\enum\PayEnum;
use app\common\enum\TeamEnum;
use app\common\logic\GoodsVirtualLogic;
use app\common\logic\OrderLogLogic;
use app\common\logic\OrderRefundLogic;
use app\common\model\Delivery;
use app\common\model\DevRegion;
use app\common\model\distribution\DistributionOrderGoods;
use app\common\model\Express;
use app\common\model\order\Order;
use app\common\model\team\TeamFound;
use app\common\model\team\TeamJoin;
use think\facade\Db;

/**
 * 商家移动端订单管理逻辑层
 * Class OrderLogic
 * @package app\shopapi\logic
 */
class OrderLogic extends Logic
{
    /**
     * @notes 订单列表
     * @param $get
     * @param $page_no
     * @param $page_size
     * @param $shop_id
     * @return array
     * @author ljj
     * @date 2021/11/10 3:13 下午
     */
    public static function lists($get, $page_no, $page_size, $shop_id)
    {
        $get['type'] = $get['type'] ?? 'all';
        $where[] = ['o.shop_id', '=', $shop_id];
        $where[] = ['o.del', '=', 0];
        $where[] = ['o.delete', '=', 0];
        //订单状态
        if (isset($get['type']) && !empty($get['type'])) {
            switch ($get['type']) {
                case 'pay':
                    $where[] = ['o.order_status', '=', 0];
                    break;
                case 'delivery':
                    $where[] = ['o.order_status', '=', 1];
                    break;
                case 'receiving':
                    $where[] = ['o.order_status', '=', 2];
                    break;
                case 'finish':
                    $where[] = ['o.order_status', '=', 3];
                    break;
                case 'close':
                    $where[] = ['o.order_status', '=', 4];
                    break;
            }
        }
        //订单商品名称
        if (isset($get['goods_name']) && !empty($get['goods_name'])) {
            $where[] = ['og.goods_name', 'like', '%'.$get['goods_name'].'%'];
        }

        $count = Order::alias('o')
            ->join('order_goods og', 'og.order_id = o.id')
            ->where($where)
            ->group('o.id')
            ->count();

        $lists = Order::alias('o')
            ->join('order_goods og', 'og.order_id = o.id')
            ->where($where)
            ->with(['order_goods'])
            ->field('o.id,o.order_sn,o.order_status,o.pay_status,o.shipping_status,o.order_amount,o.create_time,o.delivery_type, o.verification_status')
            ->append(['shop_cancel_btn','edit_address_btn','to_ship_btn','take_btn','delivery_btn','del_btn','content_btn', 'to_verification_btn'])
            ->page($page_no, $page_size)
            ->order('o.id desc')
            ->group('o.id')
            ->select()
            ->toArray();

        foreach ($lists as &$item) {
            // 订单状态描述
            if ($item['order_status'] == OrderEnum::ORDER_STATUS_DELIVERY
                && $item['delivery_type'] == OrderEnum::DELIVERY_TYPE_SELF
                && $item['pay_status'] == PayEnum::ISPAID) {
                $item['order_status_text'] = '待取货';
            } else {
                $item['order_status_text'] = OrderEnum::getOrderStatus($item['order_status']);
            }
        }

        return [
            'list' => $lists,
            'page' => $page_no,
            'size' => $page_size,
            'count' => $count,
            'more' => is_more($count, $page_no, $page_size)
        ];
    }

    /**
     * @notes 订单详情
     * @param $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/11/10 4:15 下午
     */
    public static function detail($id)
    {
        $result = Order::where('id',$id)
            ->field('id,user_id,order_sn,order_type,order_source,order_status,pay_status,pay_time,shipping_status,pay_way,order_amount,goods_price,shipping_price,discount_amount,member_amount,create_time,consignee,mobile,province,city,district,address,user_remark,delivery_type,delivery_content')
            ->with(['order_goods','user', 'invoice'])
            ->append(['shop_cancel_btn','edit_address_btn','to_ship_btn','take_btn','delivery_btn','del_btn','order_status_text','pay_way_text','delivery_address','order_type_text','order_source_text','pay_status_text'])
            ->find()
            ->toArray();

        $result['pay_time'] = $result['pay_time'] ? date('Y-m-d H:i:s', $result['pay_time']) : '-';

        // 虚拟商品 发货内容
        if ($result['delivery_type'] != OrderEnum::DELIVERY_TYPE_VIRTUAL || $result['shipping_status'] != OrderEnum::SHIPPING_FINISH) {
            $result['delivery_content'] = '';
        }

        return $result;
    }

    /**
     * @notes 取消订单
     * @param $id
     * @param $admin_id
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/11/10 5:06 下午
     */
    public static function cancel($id,$admin_id)
    {
        $order = Order::where(['id' => $id], ['orderGoods'])->find()->toArray();
        Db::startTrans();
        try {
            // 如果是拼团订单
            if ($order['order_type'] == OrderEnum::TEAM_ORDER) {
                $time = time();
                $team_id = (new TeamJoin())->where(['order_id' => $order['id']])->value('team_id');
                $teamJoin = (new TeamJoin())->alias('TJ')
                    ->field(['TJ.*,O.order_sn,O.order_status,O.pay_status,O.refund_status,O.order_amount'])
                    ->where(['team_id' => $team_id])
                    ->join('order O', 'O.id=TJ.order_id')
                    ->select()->toArray();

                TeamFound::update(['status' => TeamEnum::TEAM_STATUS_FAIL, 'team_end_time' => $time], ['id' => $team_id]);
                foreach ($teamJoin as $item) {
                    TeamJoin::update(['status' => TeamEnum::TEAM_STATUS_FAIL, 'update_time' => $time], ['id' => $item['id']]);
                    OrderRefundLogic::cancelOrder($item['order_id'], OrderLogEnum::TYPE_USER);  //取消订单

                    if ($item['pay_status'] == PayEnum::ISPAID) {
                        $order = (new Order())->findOrEmpty($item['order_id'])->toArray();
                        OrderRefundLogic::cancelOrderRefundUpdate($order); //更新订单状态
                        OrderRefundLogic::refund($order, $order['order_amount'], $order['order_amount']); //订单退款
                    }
                }


            } else {

                //取消订单
                OrderRefundLogic::cancelOrder($id, OrderLogEnum::TYPE_SHOP, $admin_id);
                //已支付的订单,取消,退款
                if ($order['pay_status'] == PayEnum::ISPAID) {
                    //更新订单状态
                    OrderRefundLogic::cancelOrderRefundUpdate($order);
                    //订单退款
                    OrderRefundLogic::refund($order, $order['order_amount'], $order['order_amount']);
                }
            }

            // 查找对应的分销订单置为失效状态
            if ($order['pay_status'] == PayEnum::ISPAID) {
                DistributionOrderGoods::where('order_id', $id)->update(['status' => 3]);
            }

            Db::commit();
            return true;

        } catch (\Exception $e) {
            Db::rollback();
            //增加退款失败记录
            if ($order['pay_status'] == PayEnum::ISPAID) {
                OrderRefundLogic::addErrorRefund($order, $e->getMessage());
            }
            return $e->getMessage();
        }
    }

    /**
     * @notes 删除订单
     * @param $id
     * @param $admin_id
     * @author ljj
     * @date 2021/11/10 5:37 下午
     */
    public static function del($id,$admin_id)
    {
        Order::update(['delete'=>1,'update_time'=>time()], ['id'=>$id]);

        //订单日志
        OrderLogLogic::record(
            OrderLogEnum::TYPE_SHOP,
            OrderLogEnum::SHOP_DEL_ORDER,
            $id,
            $admin_id,
            OrderLogEnum::SHOP_DEL_ORDER
        );
    }

    /**
     * @notes 修改地址
     * @param $post
     * @return bool
     * @author ljj
     * @date 2021/11/10 6:35 下午
     */
    public static function editAddress($post)
    {
        Order::update(
            [
                'consignee'=>$post['consignee'],
                'province'=>$post['province'],
                'city'=>$post['city'],
                'district'=>$post['district'],
                'address'=>$post['address'],
                'mobile'=>$post['mobile'],
                'update_time' => time(),
            ], ['id'=>$post['id']]);

        return true;
    }

    /**
     * @notes 获取地址详情
     * @param $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/11/13 11:41 上午
     */
    public static function getAddress($id)
    {
        $result = Order::where('id',$id)
            ->field('consignee,province,city,district,address,mobile')
            ->find()
            ->toArray();
        $result['region'] = DevRegion::where('id', 'in', $result['province'].','.$result['city'].','.$result['district'])->order('id','asc')->column('name');

        return $result;
    }

    /**
     * @notes 发货
     * @param $post
     * @param $admin_id
     * @return bool|string
     * @author ljj
     * @date 2021/11/11 10:27 上午
     */
    public static function delivery($post, $admin_id)
    {
        Db::startTrans();
        try {
            $order = Order::where(['id' => $post['id']], ['order_goods'])->find();

            if ($order['shipping_status'] == 1) {
                return true;
            }


            //添加发货单
            $delivery_data = [
                'order_id' => $post['id'],
                'order_sn' => $order['order_sn'],
                'user_id' => $order['user_id'],
                'admin_id' => $admin_id,
                'consignee' => $order['consignee'],
                'mobile' => $order['mobile'],
                'province' => $order['province'],
                'city' => $order['city'],
                'district' => $order['district'],
                'address' => $order['address'],
                'invoice_no' => $post['invoice_no'] ?? '',
                'send_type' => $post['send_type'],
                'create_time' => time(),
            ];
            //配送方式->快递配送
            if ($post['send_type'] == 1) {
                $shipping = Express::where('id', $post['shipping_id'])->find();
                $delivery_data['shipping_id'] = $post['shipping_id'];
                $delivery_data['shipping_name'] = $shipping['name'];
                $delivery_data['shipping_status'] = 1;
            }
            $delivery = Delivery::create($delivery_data);


            //更新订单下商品的发货状态
            $order->update_time = time();
            $order->shipping_time = time();
            $order->shipping_status = 1;
            $order->order_status = Order::STATUS_WAIT_RECEIVE;
            $order->delivery_id = $delivery->id;
            $order->save();

            //订单日志
            OrderLogLogic::record(
                OrderLogEnum::TYPE_SHOP,
                OrderLogEnum::SHOP_DELIVERY_ORDER,
                $post['id'],
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
                        'shipping_name' => $delivery_data['shipping_name'] ?? '无需快递',
                        'invoice_no' => $post['invoice_no'] ?? '',
                    ]
                ]);
            }

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * @notes 获取物流公司列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/11/13 11:46 上午
     */
    public static function getExpress()
    {
        return Express::where('del',0)->field('id,name')->select()->toArray();
    }

    /**
     * @notes 确认收货
     * @param $id
     * @param $admin_id
     * @author ljj
     * @date 2021/11/11 10:55 上午
     */
    public static function confirm($id, $admin_id)
    {
        Order::update(
            [
                'order_status' => Order::STATUS_FINISH,
                'confirm_take_time' => time(),
                'update_time' => time(),
            ], ['id'=>$id]);

        //订单日志
        OrderLogLogic::record(
            OrderLogEnum::TYPE_SHOP,
            OrderLogEnum::SHOP_CONFIRM_ORDER,
            $id,
            $admin_id,
            OrderLogEnum::SHOP_CONFIRM_ORDER
        );
    }

    /**
     * @notes 查看物流
     * @param $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2021/11/11 11:34 上午
     */
    public static function logistics($id)
    {
        return Delivery::where('order_id',$id)
            ->field('shipping_name,invoice_no')
            ->find()
            ->toArray();
    }


    /**
     * @notes 虚拟发货
     * @param $post
     * @param $admin_id
     * @return bool
     * @author 段誉
     * @date 2022/4/20 17:56
     */
    public static function virtualDelivery($post, $admin_id)
    {
        try {
            $order_id = $post['order_id'];
            $order = Order::with(['order_goods'])->where(['del' => 0, 'id' => $order_id])->find();

            // 更新发货订单信息
            $result = GoodsVirtualLogic::shopSelfDelivery($order_id,  $post['delivery_content']);
            if (true !== $result) {
                throw new \Exception($result);
            }

            //订单日志
            OrderLogLogic::record(
                OrderLogEnum::TYPE_SHOP,
                OrderLogEnum::SHOP_DELIVERY_ORDER,
                $order_id,
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
                        'shipping_name' => '无需快递',
                        'invoice_no' => '',
                    ]
                ]);
            }

            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }
}