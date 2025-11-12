<?php

namespace app\api\logic;

use app\api\controller\Shop;
use app\common\enum\NoticeEnum;
use app\common\enum\PayEnum;
use app\common\logic\AfterSaleLogLogic;
use app\common\basics\Logic;
use app\common\model\after_sale\{AfterSale, AfterSaleLog};
use app\common\model\NoticeSetting;
use app\common\model\order\OrderGoods;
use app\common\model\order\Order;
use app\common\enum\OrderGoodsEnum;
use app\common\model\shop\Shop as ShopModel;
use app\common\server\AreaServer;
use app\common\server\ConfigServer;
use app\common\server\UrlServer;
use think\facade\Db;
use think\Exception;
use app\common\enum\ShopEnum;
use think\Model;

/**
 * 售后
 * Class AfterSaleLogic
 * @package app\api\logic
 */
class AfterSaleLogic extends Logic
{

    /**
     * @notes 售后退款列表
     * @param $user_id
     * @param $type
     * @param $page
     * @param $size
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author suny
     * @date 2021/7/13 6:06 下午
     */
    public static function lists($user_id, $type, $page, $size)
    {

        $where = [];
        $where[] = ['o.user_id', '=', $user_id];

        $data = $result = [];
        switch ($type) {
            case 'normal':
                $where[] = ['g.refund_status', '=', OrderGoodsEnum::REFUND_STATUS_NO];
                $where[] = ['o.order_status', 'in', [Order::STATUS_WAIT_RECEIVE, Order::STATUS_FINISH]];
                $order = new Order();
                $count = $order->alias('o')
                    ->field('o.id,o.confirm_take_time,o.order_status,o.create_time,s.id as sid,s.name as shop_name,s.type as shop_type')
                    ->join('order_goods g', 'g.order_id = o.id')
                    ->join('shop s', 'o.shop_id = s.id')
                    ->with(['order_goods' => function ($query) {

                        $query->where('refund_status', OrderGoodsEnum::REFUND_STATUS_NO);
                    }])
                    ->where($where)
                    ->group('o.id')
                    ->count();

                $lists = $order
                    ->alias('o')
                    ->field('o.id,o.confirm_take_time,o.order_status,o.order_sn,o.create_time,s.id as sid,s.name as shop_name,s.logo as shop_logo,s.type as shop_type')
                    ->join('order_goods g', 'g.order_id = o.id')
                    ->join('shop s', 'o.shop_id = s.id')
                    ->with(['order_goods' => function ($query) {

                        $query->where('refund_status', OrderGoodsEnum::REFUND_STATUS_NO);
                    }])
                    ->where($where)
                    ->group('o.id')
                    ->order('o.id desc')
                    ->page($page, $size)
                    ->select()->toArray();

                // 获取所有用户的订单售后的退款编号申请时间
                $order_ids = array_column($lists, 'id');
                $after_sale_lists = AfterSale::where(['order_id' => $order_ids])->column('sn, create_time','order_id');

                foreach ($lists as $item) {
                    $result = [
                        'order_id' => $item['id'],
                    ];

                    $order_goods = [];
                    foreach ($item['order_goods'] as $k1 => $good) {
                        $goods = [
                            'goods_id' => $good['goods_id'],
                            'item_id' => $good['item_id'],
                            'goods_name' => '',
                            'goods_num' => $good['goods_num'],
                            'goods_price' => $good['goods_price'],
                            'image' => '',
                        ];
                        $goods_data = $good;
                        $goods['spec_value_str'] = $goods_data['spec_value'];
                        $goods['goods_name'] = $goods_data['goods_name'];
                        $goods['image'] = empty($goods_data['spec_image']) ? UrlServer::getFileUrl($goods_data['image']) : UrlServer::getFileUrl($goods_data['spec_image']);

                        $order_goods[] = $goods;
                    }
                    $result['sid'] = $item['sid'];
                    $result['shop_name'] = $item['shop_name'];
                    $result['shop_type'] = ShopEnum::getShopTypeDesc($item['shop_type']);
                    $result['shop_logo'] = UrlServer::getFileUrl($item['shop_logo']);
                    $result['order_goods'] = $order_goods;
                    $result['order_sn'] = $item['order_sn'];
                    $result['create_time'] = $item['create_time'];
                    $result['after_sale']['desc'] = '';
                    $result['after_sale']['able_apply'] = 1;
                    $result['after_sale']['sn'] = '';
                    $result['after_sale']['create_time'] = '';
                    if(isset($after_sale_lists[$item['id']]) && $after_sale_lists[$item['id']]){
                        $after_sale_info = $after_sale_lists[$item['id']];
                        $result['after_sale']['sn'] = $after_sale_info['sn'];
                        $result['after_sale']['create_time'] = date('Y-m-d H:i:s', $after_sale_info['create_time']);
                    }
                    if (self::checkAfterSaleDate($item) === false) {
                        $result['after_sale']['desc'] = '该商品已超过售后期';
                        $result['after_sale']['able_apply'] = 0;
                    }
                    $time = $item['confirm_take_time'] ?? $item['create_time'];
                    $result['time'] = $time;
                    $data[] = $result;
                }
                $list = ['list' => $data, 'page' => $page, 'size' => $size, 'count' => $count, 'more' => is_more($count, $page, $size)];
                return $list;
                break;
            case 'apply':
                $where[] = ['g.refund_status', 'in', [OrderGoodsEnum::REFUND_STATUS_APPLY, OrderGoodsEnum::REFUND_STATUS_WAIT]];
                $where[] = ['o.order_status', 'in', [Order::STATUS_WAIT_RECEIVE, Order::STATUS_FINISH]];
                $where[] = ['a.del', '=', 0];
                break;
            case 'finish':
                $where[] = ['g.refund_status', '=', OrderGoodsEnum::REFUND_STATUS_SUCCESS];
                $where[] = ['a.del', '=', 0];
                $where[] = ['o.order_status', 'in', [Order::STATUS_WAIT_RECEIVE, Order::STATUS_FINISH, Order::STATUS_CLOSE]];
                break;
        }

        $field = 'g.order_id,g.goods_id,g.item_id,g.goods_num,g.goods_name,g.image,g.spec_value,g.goods_price,a.sn,a.status,a.refund_type,a.id as after_sale_id,a.create_time,s.id as sid,s.name as shop_name,s.logo as shop_logo,s.type as shop_type';

        $count = Db::name('order_goods')
            ->alias("g")
            ->field($field)
            ->join('order o', 'g.order_id = o.id')
            ->join('after_sale a', 'a.order_goods_id = g.id', 'left')
            ->join('shop s', 'o.shop_id = s.id')
            ->where($where)
            ->count();

        $lists = Db::name('order_goods')
            ->alias("g")
            ->field($field)
            ->join('order o', 'g.order_id = o.id')
            ->join('after_sale a', 'a.order_goods_id = g.id', 'left')
            ->join('shop s', 'o.shop_id = s.id')
            ->where($where)
            ->order('a.id desc')
            ->page($page, $size)
            ->select();

        foreach ($lists as $k => $item) {

            $goods_data = $item;
            $goods_name = $goods_data['goods_name'];
            $image = empty($goods_data['spec_image']) ? UrlServer::getFileUrl($goods_data['image']) : UrlServer::getFileUrl($goods_data['spec_image']);
            $result = [
                'order_id' => $item['order_id'],
                'order_goods' => [[
                    'goods_id' => $item['goods_id'],
                    'item_id' => $item['item_id'],
                    'goods_name' => $goods_name,
                    'goods_num' => $item['goods_num'],
                    'goods_price' => $item['goods_price'],
                    'image' => $image,
                    'spec_value_str' => $goods_data['spec_value'],
                    'create_time' => $goods_data['create_time'],
                ]],
                'after_sale' => [
                    'after_sale_id' => $item['after_sale_id'],
                    'status' => $item['status'],
                    'refund_type' => $item['refund_type'],
                    'status_text' => AfterSale::getStatusDesc($item['status']),
                    'type_text' => AfterSale::getRefundTypeDesc($item['refund_type']),
                    'desc' => AfterSale::getStatusDesc($item['status']),
                    'able_apply' => 1,
                    'sn' => $item['sn'],
                    'create_time' => date('Y-m-d H:i:s', $item['create_time']),
                ],
                'time' => date('Y-m-d H:i', $item['create_time']),
            ];
            $result['sid'] = $item['sid'];
            $result['shop_name'] = $item['shop_name'];
            $result['shop_type'] = ShopEnum::getShopTypeDesc($item['shop_type']);
            $result['shop_logo'] = UrlServer::getFileUrl($item['shop_logo']);
            $data[] = $result;
        }
        $list = [
            'list' => $data,
            'page' => $page,
            'size' => $size,
            'count' => $count,
            'more' => is_more($count, $page, $size)
        ];
        return $list;
    }


    /**
     * @notes 申请售后
     * @param $post
     * @param $user_id
     * @return array
     * @throws Exception
     * @throws \think\exception\PDOException
     * @author suny
     * @date 2021/7/13 6:07 下午
     * 验证(收货后多少天内才可申请售后/或已发货,未收货). 售后日志
     */
    public static function add($post, $user_id)
    {

        Db::startTrans();
        try {
            //1,增加售后记录
            $order_goods = Db::name('order_goods')
                ->alias('g')
                ->field('g.id,g.goods_num,g.total_pay_price,g.order_id,g.refund_status,g.goods_id')
                ->join('order o', 'o.id = g.order_id')
                ->where(['order_id' => $post['order_id'], 'g.item_id' => $post['item_id']])
                ->find();

            $data = [
                'sn' => createSn('after_sale', 'sn', '', 4),
                'user_id' => $user_id,
                'order_id' => $order_goods['order_id'],
                'order_goods_id' => $order_goods['id'],
                'item_id' => $post['item_id'],
                'goods_id' => $order_goods['goods_id'],
                'goods_num' => $order_goods['goods_num'],
                'refund_reason' => trim($post['reason']),
                'refund_remark' => isset($post['remark']) ? trim($post['remark']) : '',
                'refund_image' => isset($post['img']) ? $post['img'] : '',
                'refund_type' => $post['refund_type'],
                'refund_price' => $order_goods['total_pay_price'],
                'create_time' => time(),
            ];

            $after_sale_id = Db::name('after_sale')->insertGetId($data);

            //2,更改订单商品,退款状态为申请退款
            Db::name('order_goods')
                ->where('id', $order_goods['id'])
                ->update(['refund_status' => OrderGoodsEnum::REFUND_STATUS_APPLY]);

            //记录日志
            AfterSaleLogLogic::record(
                AfterSaleLog::TYPE_USER,
                AfterSaleLog::USER_APPLY_REFUND,
                $post['order_id'],
                $after_sale_id,
                $user_id,
                AfterSaleLog::USER_APPLY_REFUND
            );

            $order = Order::with('shop')->where(['id' => $post['order_id']])->find();
            if (!empty($order['shop']['mobile'])) {
                //通知商家
                event('Notice', [
                    'scene' => NoticeEnum::AFTER_SALE_NOTICE_SHOP,
                    'mobile' => $order['shop']['mobile'],
                    'params' => [
                        'user_id' => $user_id,
                        'after_sale_sn' => $data['sn']
                    ]
                ]);
            }

            Db::commit();
            return ['after_sale_id' => $after_sale_id];
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage());
        }
    }


    /**
     * @notes 售后商品信息
     * @param $item_id
     * @param $order_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/13 6:08 下午
     */
    public static function info($item_id, $order_id)
    {

        $goods = new OrderGoods();
        $where = ['order_id' => $order_id, 'item_id' => $item_id];
        $info = $goods->where($where)->find()->toArray();
        $goods['image'] = isset($info['image']) ? UrlServer::getFileUrl($info['image']) : '';
        $data = [
            'goods' => $info,
            'reason' => AfterSale::getReasonLists(),
        ];
        return $data;
    }

    /**
     * @notes 上传退货快递信息
     * @param $user_id
     * @param $post
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/13 6:08 下午
     */
    public static function express($user_id, $post)
    {

        $id = $post['id'];
        $after_sale = AfterSale::find($id);

        $after_sale->express_name = $post['express_name'];
        $after_sale->invoice_no = $post['invoice_no'];
        $after_sale->express_remark = isset($post['express_remark']) ? trim($post['express_remark']) : null;
        $after_sale->express_image = isset($post['express_image']) ? $post['express_image'] : null;
        $after_sale->status = AfterSale::STATUS_WAIT_RECEIVE_GOODS;//售后状态
        $result = $after_sale->save();

        //记录日志
        AfterSaleLogLogic::record(
            AfterSaleLog::TYPE_USER,
            AfterSaleLog::USER_SEND_EXPRESS,
            $after_sale['order_id'],
            $id,
            $user_id,
            AfterSaleLog::USER_SEND_EXPRESS
        );
        return $result;
    }


    /**
     * @notes 撤销申请
     * @param $user_id
     * @param $post
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/13 6:08 下午
     */
    public static function cancel($user_id, $post)
    {

        $id = $post['id'];
        $after_sale = AfterSale::find($id);
        $after_sale->del = 1;
        $after_sale->update_time = time();
        $after_sale->save();


        //2,更改订单商品,退款状态为申请退款
        $order_goods = OrderGoods::find(['id' => $after_sale['order_goods_id']]);
        $order_goods->refund_status = OrderGoodsEnum::REFUND_STATUS_NO;
        $order_goods->save();

        //记录日志
        AfterSaleLogLogic::record(
            AfterSaleLog::TYPE_USER,
            AfterSaleLog::USER_CANCEL_REFUND,
            $after_sale['order_id'],
            $id,
            $user_id,
            AfterSaleLog::USER_CANCEL_REFUND
        );
    }

    /**
     * @notes 售后详情
     * @param $get
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/13 6:08 下午
     */
    public static function detail($get)
    {

        $after_sale = new AfterSale();
        $field = 'a.id,a.sn,a.order_goods_id,a.refund_reason,a.refund_image,a.refund_type,a.refund_price,a.refund_remark,a.create_time,a.status,o.shop_id,s.id sid,s.name shop_name,s.type shop_type';
        $detail = $after_sale
            ->alias('a')
            ->field($field)
            ->join('order o', 'o.id = a.order_id')
            ->join('shop s', 's.id = o.shop_id')
            ->with(['order_goods'])
            ->where(['a.id' => $get['id'], 'a.del' => 0])
            ->find()
            ->toArray();
        $detail['shop_type'] = ShopEnum::getShopTypeDesc($detail['shop_type']);
        if (!$detail) {
            return [];
        }

        $detail['refund_image'] = empty($detail['refund_image']) ? '' : UrlServer::getFileUrl($detail['refund_image']);
        $detail['status_text'] = AfterSale::getStatusDesc($detail['status']);

        $detail['order_goods'] = $detail['order_goods'][0];
        $goods = $detail['order_goods'];

        $image = $goods['image'];

        $detail['order_goods']['image'] = empty($goods['spec_image']) ? $image : $goods['spec_image'];

        $detail['order_goods']['goods_name'] = $goods['goods_name'];
        $detail['order_goods']['spec_value'] = $goods['spec_value'];

        $detail['refund_type_text'] = AfterSale::getRefundTypeDesc($detail['refund_type']);

        $shop_id = $detail['shop_id'];
        $shop_info = ShopModel::where('id', $shop_id)->find();
        $refund_address = $shop_info['refund_address'];
        $shop_province = $refund_address['province_id'] ?? ''; //省份
        $shop_city = $refund_address['city_id'] ?? ''; //城市
        $shop_district = $refund_address['district_id'] ?? ''; //县区
        $shop_address = $refund_address['address'] ?? ''; //详细地址

        if (empty($shop_province) || empty($shop_city) || empty($shop_district)) {
            $arr = [];
        } else {
            $arr = [$shop_province, $shop_city, $shop_district];
        }
        $address = AreaServer::getAddress($arr, $shop_address);

        $shop = [
            'contact' => $refund_address['nickname'] ?? '',
            'mobile' => $refund_address['mobile'] ?? '',
            'address' => $address
        ];

        $detail['shop'] = $shop;
        return $detail;
    }

    /**
     * @notes 重新申请
     * @param $user_id
     * @param $post
     * @return array
     * @throws Exception
     * @throws \think\exception\PDOException
     * @author suny
     * @date 2021/7/13 6:08 下午
     */
    public static function again($user_id, $post)
    {

        Db::startTrans();
        try {
            $id = $post['id'];
            $after_sale = AfterSale::find($id);
            $after_sale->refund_type = $post['refund_type'];
            $after_sale->refund_reason = trim($post['reason']);
            $after_sale->refund_remark = isset($post['remark']) ? trim($post['remark']) : '';
            $after_sale->refund_image = isset($post['img']) ? $post['img'] : '';
            $after_sale->status = AfterSale::STATUS_APPLY_REFUND;
            $after_sale_result = $after_sale->save();

            //2,更改订单商品,退款状态为申请退款
            $order_goods = OrderGoods::find(['id' => $after_sale['order_goods_id']]);
            $order_goods->refund_status = OrderGoodsEnum::REFUND_STATUS_APPLY;
            $order_goods_result = $order_goods->save();

            //记录日志
            AfterSaleLogLogic::record(
                AfterSaleLog::TYPE_USER,
                AfterSaleLog::USER_AGAIN_REFUND,
                $after_sale['order_id'],
                $id,
                $user_id,
                AfterSaleLog::USER_AGAIN_REFUND
            );
            Db::commit();
            return ['after_sale_id' => $id];
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @notes 检查是否在售后时间内
     * @param $order
     * @return bool
     * @author suny
     * @date 2021/7/13 6:08 下午
     */
    public static function checkAfterSaleDate($order)
    {

        $now = time();
        $refund_days = ConfigServer::get('transaction', 'order_after_sale_days', 7);
        if ($refund_days == 0) {
            return true;
        }

        if ($order['order_status'] == Order::STATUS_FINISH) {
            $check_time = strtotime('+' . $refund_days . 'day', $order['confirm_take_time']);
            if ($now > $check_time) {
                return false;
            }
        }
        return true;
    }
}