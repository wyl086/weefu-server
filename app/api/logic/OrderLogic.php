<?php

namespace app\api\logic;

use app\common\basics\Logic;
use app\common\enum\ClientEnum;
use app\common\enum\FootprintEnum;
use app\common\enum\FreightEnum;
use app\common\enum\GoodsEnum;
use app\common\enum\OrderEnum;
use app\common\enum\OrderGoodsEnum;
use app\common\enum\OrderInvoiceEnum;
use app\common\enum\OrderLogEnum;
use app\common\enum\PayEnum;
use app\common\enum\ShopEnum;
use app\common\enum\TeamEnum;
use app\common\logic\OrderLogLogic;
use app\common\logic\OrderRefundLogic;
use app\common\model\Cart;
use app\common\model\dev\DevRegion;
use app\common\model\Freight;
use app\common\model\goods\Goods;
use app\common\model\Delivery;
use app\common\model\goods\GoodsItem;
use app\common\model\integral\IntegralOrder;
use app\common\model\order\Order;
use app\common\model\order\OrderGoods;
use app\common\model\order\OrderLog;
use app\common\model\order\OrderRefund;
use app\common\model\order\OrderTrade;
use app\common\model\Pay;
use app\common\model\RechargeOrder;
use app\common\model\shop\Shop;
use app\common\model\coupon\Coupon;
use app\common\model\coupon\CouponList;
use app\common\model\team\TeamFound;
use app\common\model\team\TeamJoin;
use app\common\model\user\User;
use app\common\model\user\UserLevel;
use app\common\server\UrlServer;
use app\common\model\seckill\SeckillGoods;
use app\common\model\bargain\BargainLaunch;
use app\common\model\user\UserAddress;
use app\common\server\AreaServer;
use app\common\server\ConfigServer;
use app\common\server\JsonServer;
use expressage\Kd100;
use expressage\Kdniao;
use think\Exception;
use think\facade\Db;
use think\facade\Env;

/**
 * Class OrderLogic
 * @package app\api\logic
 */
class OrderLogic extends Logic
{
    public static $order_type = OrderEnum::NORMAL_ORDER;

    /**
     * @notes 下单
     * @param $post
     * @return array|false
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\PDOException
     * @author suny
     * @date 2021/7/13 6:19 下午
     */
    public static function add($post)
    {
        // 商品类型 0普通 1虚拟
        $goods_type = input('goods_type/d', GoodsEnum::TYPE_ACTUAL);
        
        // 初始化支付方式
        $post['pay_way'] = 0;
        if (!empty($post['goods'])) {
            $goods = is_array($post['goods']) ?  $post['goods'] : json_decode($post['goods'], true);
        } else {
            $where = [
                ['id', 'in', $post['cart_id']]
            ];
            $goods = Cart::where($where)
                ->field(['goods_id', 'item_id', 'shop_id', 'goods_num as num'])
                ->select()->toArray();
        }

        foreach ($goods as &$good) {
            $good['delivery_type'] = !isset($good['delivery_type']) ? GoodsEnum::DELIVERY_EXPRESS : $good['delivery_type'];
        }
        $post['goods'] = $goods;

        Db::startTrans();
        try {
            // 砍价订单验证
            if (isset($post['bargain_launch_id']) and $post['bargain_launch_id'] > 0) {
                self::$order_type = OrderEnum::BARGAIN_ORDER;
                $bargainLaunchModel = new BargainLaunch();
                $launch = $bargainLaunchModel->where(['id' => (int)$post['bargain_launch_id']])->find();
                if (!$launch) {
                    throw new Exception('砍价异常');
                }
                if ($launch['status'] == 2) {
                    throw new Exception('砍价失败,禁止下单');
                }
                if ($launch['payment_limit_time'] < time() and $launch['payment_limit_time'] > 0) {
                    throw new Exception('下单失败,超出限制时间');
                }
                if ($launch['order_id'] > 0) {
                    throw new Exception('您已下单了, 请勿重复操作');
                }
            }

            // 校验商品
            self::checkGoods($goods);

            $address = UserAddress::where('id', $post['address_id'] ?? 0)
                ->field('contact,telephone,province_id,city_id,district_id,address')
                ->find();
            if (empty($address) && $goods_type == GoodsEnum::TYPE_ACTUAL && $post['delivery_type'] == GoodsEnum::DELIVERY_EXPRESS) {
                throw new Exception('请选择地址');
            }

            // 校验发票信息 返回以店铺id为键,原发票参数为值的数组
            $invoice = OrderInvoiceLogic::checkOrderInvoice($post);
            if (false === $invoice) {
                throw new Exception(OrderInvoiceLogic::getError());
            }

            $order_trade_add = self::addOrderTrade($post, $address);

            $shop_goods = [];
            $order_goods_datas_insert = [];
            $order_log_datas_insert = [];
            foreach ($post['goods'] as $key => $value) { //按店铺区分商品
                $res = self::checkShop($value); //判断商家营业状态
                if ($res !== true) {
                    throw new Exception($res);
                }
                $shop_goods[$value['shop_id']][] = $value;
            }

            foreach ($shop_goods as $key => $value) {
                foreach ($value as $val) {
                    $seckill_goods_price = GoodsItem::isSeckill($val['item_id']);
                    if ($seckill_goods_price != 0) {//是秒杀商品
                        $sales_sum_res = self::setSeckillSaleSum($val['item_id'], $val['num']);
                        if ($sales_sum_res !== true) {
                            throw new Exception('秒杀商品销量设置失败');
                        }
                    }
                }

                $order_add = self::addOrder($order_trade_add, $value, $post, $key, $address);

                // 增加发票
                OrderInvoiceLogic::insertOrderInvoice($key, $post['user_id'], $order_add, $invoice);

                $order_log_add_data = self::getOrderLogData($key, $post['user_id'], $key);
                $order_log_datas_insert[] = $order_log_add_data;
                $order_goods_data = self::getOrderGoodsData($order_add, $value,$post['user_id']);
                $order_goods_datas_insert = array_merge($order_goods_datas_insert, $order_goods_data);
            }

            // 订单日志
            OrderLog::insertAll($order_log_datas_insert);

            // 订单商品
            OrderGoods::insertAll($order_goods_datas_insert);

            //商品库存减少
            self::subGoodsStock($post['goods']);

            //购物车删除
            if (isset($post['cart_id']) && $post['cart_id'] != 0) {
                self::delCart($post['cart_id']);
            }

            // 砍价订单处理
            if (isset($post['bargain_launch_id']) and $post['bargain_launch_id'] > 0) {
                $bargainLaunchModel = new BargainLaunch();
                $bargainLaunchModel->where(['id' => (int)$post['bargain_launch_id']])
                    ->update(['order_id' => $order_add, 'status' => 1]);
            }

            // 检测包邮活动
            $orders = Db::name('order')->where('trade_id', $order_trade_add)->select()->toArray();
            foreach($orders as $item) {
                // 虚拟商品直接包邮
                $is_free = true;
                if ($goods_type == GoodsEnum::TYPE_ACTUAL) {
                    $is_free = self::isFreeShipping($item['shop_id'], $item['goods_price'] - $item['discount_amount'], [
                        'province_id'   => $item['province'],
                        'city_id'       => $item['city'],
                        'district_id'   => $item['district'],
                    ]);
                }
    
                // 符合包邮条件，去邮费
                if($is_free) {
                    Db::name('order')->where('id', $item['id'])->update([
                        'order_amount'      => $item['order_amount'] - $item['shipping_price'],
                        'total_amount'      => $item['total_amount'] - $item['shipping_price'],
                        'shipping_price'    => 0,
                    ]);
                }
            }
            // 更新主订单金额
            $newOrderAmount = Db::name('order')->where('trade_id', $order_trade_add)->sum('order_amount');
            $newTotalAmount = Db::name('order')->where('trade_id', $order_trade_add)->sum('total_amount');
            Db::name('order_trade')->where('id', $order_trade_add)->update([
                'order_amount' => $newOrderAmount,
                'total_amount' => $newTotalAmount,
            ]);

            Db::commit();
            return ['trade_id' => $order_trade_add, 'order_id' => $order_add, 'type' => 'trade'];
        } catch (Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 结算页数据
     * @param $post
     * @return array|false
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/13 6:19 下午
     */
    public static function settlement($post)
    {
        // 商品类型 0普通 1虚拟
        $goods_type = input('goods_type/d', GoodsEnum::TYPE_ACTUAL);
        
        if (!empty($post['goods'])) {
            $goods = json_decode($post['goods'], true);
            $post['goods'] = $goods;
        } else {
            $where = [[
                'id', 'in', $post['cart_id']]
            ];
            $post['goods'] = $goods = Cart::where($where)
                ->field(['goods_id', 'item_id', 'shop_id', 'goods_num as num'])
                ->select()->toArray();
        }

        // 检查店铺营业状态
        foreach($post['goods'] as $good) {
            $shop = Shop::field('name,expire_time,is_run,is_freeze')->where(['del' => 0, 'id' => $good['shop_id']])->findOrEmpty();
            if($shop->isEmpty()) {
                self::$error = '部分商品所属店铺不存在';
                return false;
            }
            // 获取原始数据(不经获取器)
            $shop = $shop->getData();
            if(!empty($shop['expire_time']) && ($shop['expire_time'] <= time())) {
                self::$error = '部分商品所属店铺已到期';
                return false;
            }
            if($shop['is_freeze']) {
                self::$error = '部分商品所属店铺已被冻结';
                return false;
            }
            if(!$shop['is_run']) {
                self::$error = '部分商品所属店铺暂停营业中';
                return false;
            }
            if ($good['num'] <= 0) {
                self::$error = '商品数量不能小于0';
                return false;
            }
        }
        $Goods = new Goods();
        $GoodsItem = new GoodsItem();
        $Shop = new Shop();
        if (isset($post['address_id']) && !empty($post['address_id'])) {
            $where = [
                'id' => $post['address_id'],
                'del' => 0,
            ];
            $address = UserAddress::where($where)
                ->field('id,contact,telephone,province_id,city_id,district_id,address')
                ->find()->toArray();
        } else {
            $address = UserAddress::where(['user_id' => $post['user_id'], 'is_default' => 1,'del' => 0])
                ->field('id,contact,telephone,province_id,city_id,district_id,address')
                ->find();
        }
        if (!empty($address) && $goods_type == GoodsEnum::TYPE_ACTUAL ) {
            $address['province'] = AreaServer::getAddress($address['province_id']);
            $address['city'] = AreaServer::getAddress($address['city_id']);
            $address['district'] = AreaServer::getAddress($address['district_id']);
        } else {
            $address = [];
        }

        // 校验发票信息 返回以店铺id为键,原发票参数为值的数组
        $invoice = OrderInvoiceLogic::checkOrderInvoice($post);
        if (false === $invoice) {
            self::$error = OrderInvoiceLogic::getError();
            return false;
        }
    
        $shop = [];

        foreach ($goods as &$good) {
            $goods_item = $GoodsItem->alias('gi')
                ->join('goods g', 'g.id = gi.goods_id')
                ->where(['gi.id' => $good['item_id'], 'gi.goods_id' => $good['goods_id']])
                ->field('gi.price,gi.spec_value_str,gi.image,g.image,g.name as goods_name, g.type as goods_type, g.delivery_type as delivery_types')
                ->find()
                ->toArray();

            $good['type'] = $goods_item['goods_type'];
            $good['name'] = $goods_item['goods_name'];
            $good['price'] = $goods_item['price'];
            $good['original_price'] = $goods_item['price'];
            $good['spec_value'] = $goods_item['spec_value_str'];
            $good['image'] = empty($goods_item['image']) ? $goods_item['image'] : $goods_item['image'];
            $good['image'] = UrlServer::getFileUrl($good['image']);
            $good['delivery_types'] = $goods_item['delivery_types'] ? explode(',', $goods_item['delivery_types']) : [];
            $good['delivery_type']  = $good['delivery_type'] ?? GoodsEnum::DELIVERY_EXPRESS;
            // 配送列表
            if ($good['type'] == GoodsEnum::TYPE_VIRTUAL) {
                // 虚拟商品, 配送方式为 虚拟发货
                $shop[$good['shop_id']]['delivery_types'][]    = GoodsEnum::DELIVERY_VIRTUAL;
            } else {
                // 快递和自提
                foreach ($good['delivery_types'] as $ko => $vo) {
                    $shop[$good['shop_id']]['delivery_types'][] = (int) $vo;
                }
            }
            unset($good['delivery_types']);
        }
    
        foreach ($shop as $shop_id => $shops_info) {
            $shop[$shop_id]['delivery_types_arr']  = GoodsEnum::getDeliveryLists($shops_info['delivery_types']);
        }
    
        foreach ($goods as $key => &$goods_info) {
            if (! in_array($goods_info['delivery_type'], $shop[$goods_info['shop_id']]['delivery_types'])) {
                $goods_info['delivery_type'] = $shop[$goods_info['shop_id']]['delivery_types_arr'][0]['delivery_type'];
            }
            $shop[$goods_info['shop_id']]['delivery_type']      = $goods_info['delivery_type'];
            $shop[$goods_info['shop_id']]['delivery_type_text'] = GoodsEnum::getDeliveryTypeDesc($goods_info['delivery_type']);
        }
        
        foreach ($goods as $key => $value) { //按店铺区分商品
            $shop_data = $Shop->where('id', $value['shop_id'])->find();
            $shop[$value['shop_id']]['shop_id'] = $value['shop_id'];
            $shop[$value['shop_id']]['shop_name'] = $shop_data['name'];
            $shop[$value['shop_id']]['open_invoice'] = $shop_data['open_invoice']; // 发票开关
            $shop[$value['shop_id']]['spec_invoice'] = $shop_data['spec_invoice']; // 是否支持专票
            
            // 经营信息
            $shop[$value['shop_id']]['run_start_time']  = $shop_data['run_start_time'] ? date('H:i:s', $shop_data['run_start_time']) : '';
            $shop[$value['shop_id']]['run_end_time']    = $shop_data['run_end_time'] ? date('H:i:s', $shop_data['run_end_time']) : '';
            $shop[$value['shop_id']]['weekdays']        = $shop_data['weekdays'];
            // 店铺地址
            $shop[$value['shop_id']]['province']    = DevRegion::getAreaName($shop_data['province_id']);
            $shop[$value['shop_id']]['city']        = DevRegion::getAreaName($shop_data['city_id']);
            $shop[$value['shop_id']]['district']    = DevRegion::getAreaName($shop_data['district_id']);
            $shop[$value['shop_id']]['address']     = $shop_data['address'];
            
            $array = $post;
            $array['shop_id'] = $value['shop_id'];
            $shop_coupon_list = CouponLogic::getShopCouponList($array);
            $shop[$value['shop_id']]['coupon_list'] = $shop_coupon_list['suit'];
            //是否为秒杀
            $value['is_seckill'] = 0;
            $seckill_goods_price = GoodsItem::isSeckill($value['item_id']);
            if ($seckill_goods_price != 0) {
                $value['price'] = $seckill_goods_price;
                $value['is_seckill'] = 1;
                self::$order_type = OrderEnum::SECKILL_ORDER;  //秒杀订单
            }

            // 如果是砍价的商品，则替换信息
            if (isset($post['bargain_launch_id']) and $post['bargain_launch_id'] > 0) {
                $bargainLaunchModel = new BargainLaunch();
                $launch = $bargainLaunchModel->field(true)
                    ->where(['id' => (int)$post['bargain_launch_id']])
                    ->find();

                $bargainImage = $launch['goods_snap']['image'] == '' ? $launch['goods_snap']['goods_iamge'] : $launch['goods_snap']['image'];
                $value['goods_name'] = $launch['goods_snap']['name'];
                $value['image_str'] = UrlServer::getFileUrl($bargainImage);
                $value['price'] = $launch['current_price'];
                $value['spec_value_str'] = $launch['goods_snap']['spec_value_str'];
                self::$order_type = OrderEnum::BARGAIN_ORDER;//砍价订单
            }

            $shop[$value['shop_id']]['goods'][] = $value;

            $shop[$value['shop_id']]['shipping_price'] = 0;
            if ($shop[$value['shop_id']]['delivery_type'] == GoodsEnum::DELIVERY_EXPRESS) {
                $shop[$value['shop_id']]['shipping_price'] = self::calculateFreight($shop[$value['shop_id']]['goods'], $address);
            }

            $user = User::where('id', $post['user_id'])->find();
            $discount = UserLevel::where(['id' => $user['level'],'del' => 0])->value('discount');

            // 普通订单才参与会员价
            if (self::$order_type == OrderEnum::NORMAL_ORDER) {
                if($discount == 0){
                    $discount = 10;
                }
                $shop[$value['shop_id']]['total_amount'] = round(self::calculateGoodsPrice($shop[$value['shop_id']]['goods'],$discount) + $shop[$value['shop_id']]['shipping_price'], 2);
            } else {
                $shop[$value['shop_id']]['total_amount'] = round(round($value['price'] * $value['num'], 2) + $shop[$value['shop_id']]['shipping_price'], 2);
            }

            //优惠券
            $discount_amount = 0;
            if (isset($post['coupon_id']) && !empty($post['coupon_id'])) {
                $result = self::checkCoupon($post['coupon_id'], $value['shop_id']);
                if ($result) {
                    $discount_amount = self::getDiscountAmount($post['coupon_id'], $value['shop_id'])['money'] ?? 0;
                }
            }
            $shop[$value['shop_id']]['discount_amount'] = $discount_amount;
            if ($shop[$value['shop_id']]['total_amount'] > $discount_amount) {
                $shop[$value['shop_id']]['total_amount'] = round($shop[$value['shop_id']]['total_amount'] - $discount_amount, 2);
            } else { //优惠金额大于当前商品总价，总价为0
                $shop[$value['shop_id']]['discount_amount'] = $shop[$value['shop_id']]['total_amount'];
                $shop[$value['shop_id']]['total_amount'] = 0;
            }

            $num = 0;
            foreach ($shop[$value['shop_id']]['goods'] as &$item) {
                $is_member = Goods::where('id',$item['goods_id'])->value('is_member');
                $price_sum = round($item['price'] * $item['num'],2);

                // 商品参与会员价 并且订单是 普通订单
                if ($is_member && self::$order_type == OrderEnum::NORMAL_ORDER) {
                    $item['is_member'] = 1;
                } else {
                    $item['is_member'] = 0;
                }

                if($item['is_member']) {
                    $member_amount = max(round($item['price']*$discount/10,2), 0.01);
                    $item['member_amount'] = $member_amount;
                    $price_sum = round($member_amount * $item['num'],2);
                }
                $item['sum_price'] = $price_sum;
                $num += $item['num'];
            }
            $shop[$value['shop_id']]['total_num'] = $num;
        }
    
        foreach ($shop as $ko => $shop_info) {
            unset($shop[$ko]['delivery_types']);
        }

        $shop = array_values($shop);
        $total_amount = array_sum(array_column($shop, 'total_amount'));
        $orders['address'] = $address;
        $orders['shop'] = $shop;
        $orders['order_type'] = self::$order_type;
        $orders['total_amount'] = $total_amount;
        $orders['pay_way_text'] = "微信支付";
        $orders['pay_way'] = PayEnum::WECHAT_PAY;

        // 检验是否参与包邮活动，若参与去除邮费
        $orders = self::checkFreeShipping($orders);
        // 重新算总价
        $orders['total_amount'] = array_sum(array_column($orders['shop'], 'total_amount'));
        $orders['total_amount'] = round($orders['total_amount'], 2);

        $orders['invoice'] = array_values($invoice);

        return $orders;
    }


    /**
     * @notes 校验是否符合包邮条件
     * @param $orders
     * @author Tab
     * @date 2021/8/31 15:11
     */
    public static function checkFreeShipping($orders)
    {
        if (empty($orders['address'])) {
            return $orders;
        }
        $address = $orders['address'];
        foreach($orders['shop'] as &$item) {
            if (self::isFreeShipping($item['shop_id'], ($item['total_amount'] - $item['shipping_price']), $address)) {
                // 原总价格已算上邮费需减掉
                $item['total_amount'] = $item['total_amount'] - $item['shipping_price'];
                // 符合包邮条件，去邮费
                $item['shipping_price'] = 0;
            }
        }
        return $orders;
    }

    /**
     * @notes 是否满足包邮条件
     * @param $shopId
     * @param $orderAmount
     * @param $address
     * @return bool
     * @author Tab
     * @date 2021/8/31 15:42
     */
    public static function isFreeShipping($shopId, $orderAmount, $address)
    {
        $config = Db::name('free_shipping_config')->where([
            'shop_id' => $shopId,
            'del' => 0
        ])->findOrEmpty();
        if (empty($config) || $config['status'] == 0) {
            // 未设置 或 未开启包邮活动
            return false;
        }
        // 校验区级设置
        $district = Db::name('free_shipping_region')->where([
            'shop_id' => $shopId,
            'del' => 0
        ])->whereFindInSet('region', $address['district_id'])->findOrEmpty();
        if (!empty($district) && $orderAmount >= $district['order_amount']) {
            // 符合包邮条件
            return true;
        }
        if (!empty($district) && $orderAmount < $district['order_amount']) {
            // 不符合条件,不再校验市级
            return false;
        }
        // 校验市级设置
        $city = Db::name('free_shipping_region')->where([
            'shop_id' => $shopId,
            'del' => 0
        ])->whereFindInSet('region', $address['city_id'])->findOrEmpty();
        if (!empty($city) && $orderAmount >= $city['order_amount']) {
            // 符合包邮条件
            return true;
        }
        if (!empty($city) && $orderAmount < $city['order_amount']) {
            // 不符合条件,不再校验省级
            return false;
        }

        // 校验省级设置
        $province = Db::name('free_shipping_region')->where([
            'shop_id' => $shopId,
            'del' => 0
        ])->whereFindInSet('region', $address['province_id'])->findOrEmpty();
        if (!empty($province) && $orderAmount >= $province['order_amount']) {
            // 符合包邮条件
            return true;
        }
        if (!empty($province) && $orderAmount < $province['order_amount']) {
            // 不符合条件,不再校验全国设置
            return false;
        }

        // 校验全国设置
        $all = Db::name('free_shipping_region')->where([
            'shop_id' => $shopId,
            'del' => 0,
            'region' => 'all'
        ])->findOrEmpty();
        if (!empty($all) && $orderAmount >= $all['order_amount']) {
            // 符合包邮条件
            return true;
        }
        return false;
    }

    /**
     * @notes 获取优惠金额
     * @param $coupon_id
     * @return int|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/13 6:19 下午
     */
    public static function getDiscountAmount($coupon_id, $shopId = null)
    {
        $result = [
            'money' => 0,
            'shop_id' => $shopId,
            'coupon_list_id' => 0,
        ];

        if (!isset($coupon_id) || empty($coupon_id)) {
            return $result;
        }

        //优惠金额
        foreach ($coupon_id as $item) {
            $Coupon_list = CouponList::where('id', $item)->find();
            if (is_null($shopId)) {
                $where = ['id' => $Coupon_list['coupon_id'], 'del' => 0];
            } else {
                $where = ['id' => $Coupon_list['coupon_id'], 'del' => 0, 'shop_id' => $shopId];
            }
            $coupon = Coupon::where($where)
                ->find();
            if (!empty($coupon)) {
                $result['money'] = $coupon['money'];
                $result['shop_id'] = $shopId;
                $result['coupon_list_id'] = $item;
            }
        }
        return $result;
    }

    /**
     * @notes shop优惠券
     * @param $coupon_ids
     * @param $shop_id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/13 6:20 下午
     */
    public static function checkCoupon($coupon_ids, $shop_id)
    {

        $coupons = CouponList::where([['id', 'in', $coupon_ids]])->select()->toArray();

        if ($coupons) {
            foreach ($coupons as $item) {
                $coupon_id = $item['coupon_id'];
                $where = [
                    'id' => $coupon_id
                ];
                $result = Coupon::where($where)->value('shop_id');
                if ($shop_id == $result) {
                    return true;
                }
            }
        } else {
            return false;
        }
    }

    /**
     * @notes 检查商品库存
     * @param $goods
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/13 6:20 下午
     */
    public static function checkGoods($goods)
    {
        if (!is_array($goods)) {
            throw new Exception('商品数据格式不正确');
        }

        // 商品信息
        $item_ids = array_column($goods, 'item_id');
        $field = 'i.id as item_id,g.id as goods_id,g.name,g.del,g.status,i.stock,
        g.delivery_type as goods_delivery,s.delivery_type as shop_delivery';
        $goodsData = (new Goods())->alias('g')
            ->join('goods_item i','i.goods_id = g.id')
            ->join('shop s','s.id = g.shop_id')
            ->field($field)
            ->whereIn('i.id', $item_ids)
            ->select()->toArray();
        $goodsData = array_column($goodsData, null, 'item_id');

        $shopData = Shop::whereIn('id', array_column($goods, 'shop_id'))->column('id,name', 'id');

        foreach ($goods as $key => $item) {
            if (!isset($goodsData[$item['item_id']])) {
                continue;
            }

            $goodsInfo = $goodsData[$item['item_id']];
            $goodsName = text_out_hidden($goodsInfo['name'], 8);
            $shopInfo = $shopData[$item['shop_id']] ?? [];
            $shopName = $shopInfo['name'] ?? '商家';

            if ($goodsInfo['del'] || !$goodsInfo['status']) {
                throw new Exception($goodsName . '商品不存在/未上架');
            }

            if ($item['num'] <= 0) {
                throw new Exception('请选择商品' . $goodsName . '数量');
            }

            if ($goodsInfo['stock'] < $item['num']) {
                throw new Exception($goodsName . '商品库存不足');
            }

            // 校验配送方式
            if ($item['delivery_type'] == GoodsEnum::DELIVERY_EXPRESS) {
                // 快递发货-商家设置
                if (!in_array(ShopEnum::DELIVERY_EXPRESS, explode(',', $goodsInfo['shop_delivery']))) {
                    throw new Exception( '商家(' . $shopName . ')未开启快递配送');
                }
                // 商品设置
                if (!in_array(GoodsEnum::DELIVERY_EXPRESS, explode(',', $goodsInfo['goods_delivery']))) {
                    throw new Exception('商品('. $goodsName . ')暂不支持快递配送');
                }
            }

            if ($item['delivery_type'] == GoodsEnum::DELIVERY_SELF) {
                // 线下自提-商家设置
                if (!in_array(ShopEnum::DELIVERY_SELF, explode(',', $goodsInfo['shop_delivery']))) {
                    throw new Exception( '商家(' . $shopName . ')未开启线下自提');
                }
                // 商品设置
                if (!in_array(GoodsEnum::DELIVERY_SELF, explode(',', $goodsInfo['goods_delivery']))) {
                    throw new Exception( '商品('. $goodsName .')暂不支持线下自提');
                }
            }
        }
        return true;
    }


    /**
     * @notes 计算商品总价格
     * @param $goods
     * @return false|float|int
     * @author suny
     * @date 2021/7/13 6:20 下午
     */
    public static function calculateGoodsPrice($goods,$discount)
    {
        if (!is_array($goods)) {
            return false;
        }
        $GoodsItem = new GoodsItem();
        $all_goods_price = 0;
        foreach ($goods as $key => $value) {
            $goods_price = $GoodsItem->sumGoodsPrice($value['goods_id'], $value['item_id'], $value['num'],$discount);
            $all_goods_price = round($goods_price + $all_goods_price, 2);
        }
        return $all_goods_price;
    }

    /**
     * @notes 计算会员折扣金额
     * @param $goods
     * @param $discount
     * @return false|float|int
     * @author suny
     * @date 2021/9/7 4:42 下午
     */
    public static function memberGoodsPrice($goods,$discount)
    {

        if (!is_array($goods)) {
            return false;
        }
        $GoodsItem = new GoodsItem();
        $all_goods_price = 0;
        foreach ($goods as $key => $value) {
            $goods_price = $GoodsItem->sumMemberPrice($value['goods_id'], $value['item_id'], $value['num'],$discount);
            $all_goods_price = round($goods_price + $all_goods_price, 2);
        }
        return $all_goods_price;
    }

    /**
     * @notes 根据goods计算商品总运费
     * @param $goodsList
     * @param $address
     * @return false|int|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/13 6:20 下午
     */
    public static function calculateFreight($goodsList, $address)
    {
        if (!is_array($goodsList)) {
            return 0;
        }
    
    
        $templateList = [];
        
        $freight    = 0;
        
        if (empty($address)) {
            return $freight;
        }
        
        foreach ($goodsList as $key => $goods) {
            // 不是快递配送时不计算
            if ($goods['delivery_type'] != GoodsEnum::DELIVERY_EXPRESS) {
                continue;
            }

            $express = (new Goods())->getExpressType($goods['goods_id']);
            switch ($express['express_type']) {
                // 统一运费
                case 2:
                    $price      = $express['express_money'] * $goods['num'];
                    $freight    = round(($freight + $price), 2);
                    break;
                // 运费模板
                case 3:
                    $templateList[$express['express_template_id']][] = $goods;
                    break;
                default:
                    break;
            }
        }
        
        foreach ($templateList as $templateId => $templateGoods) {
        
            $total_weight = 0;
            $total_volume = 0;
            $total_num  = 0;
            
            foreach ($templateGoods as $goodsInfo) {
                $goods_item = (new GoodsItem())->where('id', $goodsInfo['item_id'])->field('stock,volume,weight')->find()->toArray();
                $total_weight   = bcadd(($goods_item['weight'] ? : 0) * $goodsInfo['num'], $total_weight, 3);
                $total_volume   = bcadd(($goods_item['volume'] ? : 0) * $goodsInfo['num'], $total_volume, 3);
                $total_num      += $goodsInfo['num'];
            }
        
            $freightInfo = Freight::findOrEmpty($templateId);
        
            switch ($freightInfo['charge_way']) {
                // 重量
                case FreightEnum::CHARGE_WAY_WEIGHT:
                    $nums = $total_weight;
                    break;
                // 体积
                case FreightEnum::CHARGE_WAY_VOLUME:
                    $nums = $total_volume;
                    break;
                // 件数
                case FreightEnum::CHARGE_WAY_PIECE:
                    $nums = $total_num;
                    break;
                default:
                    continue 2;
            }
        
            $price      = (new Freight())->sumFreight($address, $templateId, $nums);
        
            $freight    = round(($freight + $price), 2);
        }
        
        return $freight;
    }

    /**
     * @notes 添加父订单
     * @param $post
     * @param $address
     * @return false|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author suny
     * @date 2021/7/13 6:20 下午
     */
    public static function addOrderTrade($post, $address)
    {

        $OrderTrade = new OrderTrade();
        $order_amount = 0;
        $total_amount = 0;
        //计算商品总价格
        $user = User::where('id', $post['user_id'])->find();
        $discount = UserLevel::where('id', $user['level'])->value('discount');
        if($discount == 0 || self::$order_type != OrderEnum::NORMAL_ORDER){
            $discount = 10;
        }
        $all_goods_price = self::calculateGoodsPrice($post['goods'],$discount);

        //计算商品运费
        $all_freight = self::calculateFreight($post['goods'], $address);
        $total_amount = $all_goods_price + $all_freight;

        //计算优惠券优惠的金额
        $discount_amount = 0;
        if (isset($post['coupon_id'])) {
            foreach ($post['goods'] as $value) {
                $discount_amount += self::getDiscountAmount($post['coupon_id'], $value['shop_id'])['money'] ?? 0;
            }
        }

        if ($total_amount > $discount_amount) {
            $total_amount = round(($total_amount - $discount_amount), 2);
        } else {
            //优惠金额大于当前商品总价，总价为0
            $discount_amount = $total_amount;
            $total_amount = 0;
        }


        // 砍价订单
        if (isset($post['bargain_launch_id']) and $post['bargain_launch_id'] > 0) {
            foreach ($post['goods'] as $goods) {
                $bargainLaunchModel = new BargainLaunch();
                $launch = $bargainLaunchModel->field(true)
                    ->where(['id' => (int)$post['bargain_launch_id']])
                    ->find();
                $total_amount = round($launch['current_price'] * $goods['num'], 2);
            }
        }

        // 记录访问足迹
        event('Footprint', [
            'type' => FootprintEnum::PLACE_ORDER,
            'user_id' => $post['user_id'],
            'total_money' => $total_amount
        ]);
        $trade_order_data = [];
        $trade_order_data['t_sn'] = createSn('order_trade', 't_sn');

        // 拿shop_id,连接成字符串存入order_trade表shop_id中
        $shop_id = '';
        foreach ($post['goods'] as $key => $value) {
            $shop_id .= ',' . $value['shop_id'];
        }
        $shop_id = substr($shop_id, 1);
        $trade_order_data['shop_id'] = $shop_id;
        $trade_order_data['user_id'] = $post['user_id'];
        $trade_order_data['goods_price'] = $all_goods_price;
        $trade_order_data['order_amount'] = $total_amount;
        $trade_order_data['total_amount'] = $total_amount;
        $trade_order_data['discount_amount'] = $discount_amount;
        $trade_order_data['create_time'] = time();
        $order_trade_create = $OrderTrade->create($trade_order_data);
        if (false === $order_trade_create) {
            return false;
        }
        return $order_trade_create->id;
    }

    /**
     * @notes 添加子订单
     * @param $order_id
     * @param $goods
     * @param $post
     * @param $shop_id
     * @param $address
     * @return false|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author suny
     * @date 2021/7/13 6:21 下午
     */
    public static function addOrder($order_id, $goods, $post, $shop_id, $address)
    {
        $Order = new Order();

        $remarks = isset($post['remark']) ? json_decode($post['remark'], true) : '';
        if ($remarks != '') {
            foreach ($remarks as $key => $value) {
                $user_remark[$value['shop_id']] = $value['remark'];
            }
            if (array_key_exists($shop_id, $user_remark)) {
                $remark = $user_remark[$shop_id];
            } else $remark = '';
        } else {
            $remark = $remarks;
        }
        $user = User::where('id', $post['user_id'])->find();
        $discount = UserLevel::where('id', $user['level'])->value('discount');
        if($discount == 0 || self::$order_type != OrderEnum::NORMAL_ORDER){
            $discount = 10;
        }
        $goods_price = self::calculateGoodsPrice($goods,$discount); //商品价格
        $member_amount = self::memberGoodsPrice($goods,$discount); //会员优惠价格
        $shipping_price = self::calculateFreight($goods, $address);

        //计算优惠券优惠的金额
        $coupon_list_id = 0;
        $discount_amount = 0;
        if (isset($post['coupon_id'])) {
            $discount = self::getDiscountAmount($post['coupon_id'], $shop_id);
            $discount_amount = $discount['money'];
            $coupon_list_id = $discount['coupon_list_id'];
        }
    
        $order_amount = $goods_price + $shipping_price;
    
        // 优惠金额大于实际支付的时候
        $discount_amount    = min($order_amount, $discount_amount);
    
        $order_amount   = $order_amount - $discount_amount;
        $total_amount   = $order_amount;

        // 砍价订单
        if (isset($post['bargain_launch_id']) and $post['bargain_launch_id'] > 0) {
            foreach ($post['goods'] as $post_goods) {
                $bargainLaunchModel = new BargainLaunch();
                $launch = $bargainLaunchModel->field(true)
                    ->where(['id' => (int)$post['bargain_launch_id']])
                    ->find();
                $order_amount = $total_amount = round($launch['current_price'] * $post_goods['num'], 2);
            }
        }

        $order_data = [];
        $order_data['trade_id'] = $order_id;
        $order_data['shop_id'] = $shop_id;
        $order_data['user_id'] = $post['user_id'];
        $order_data['order_sn'] = createSn('order_trade', 't_sn');
        $order_data['order_type'] = self::$order_type;
        $order_data['order_source'] = $post['client'];
        $order_data['order_status'] = OrderEnum::ORDER_STATUS_NO_PAID;
        $order_data['pay_status'] = OrderEnum::PAY_STATUS_NO_PAID;
        $order_data['pay_way'] = $post['pay_way'];
        $order_data['delivery_type'] = OrderEnum::getChangeDeliveryTypeItem($goods[0]['delivery_type'] ?? 0);
        $order_data['aftersale_status'] = OrderEnum::AFTERSALE_STATUS_NO_SALE;
        $order_data['consignee']        = $address['contact'] ?? '';
        $order_data['province']         = $address['province_id'] ?? 0;
        $order_data['city']             = $address['city_id'] ?? 0;
        $order_data['district']         = $address['district_id'] ?? 0;
        $order_data['address']          = $address['address'] ?? '';
        $order_data['mobile']           = $address['telephone'] ?? '';
        $order_data['goods_price'] = $goods_price;
        $order_data['shipping_price'] = $shipping_price;
        $order_data['order_amount'] = $order_amount;
        $order_data['discount_amount'] = $discount_amount;
        $order_data['member_amount'] = $member_amount;
        $order_data['total_amount'] = $total_amount;
        $order_data['total_num'] = array_sum(array_column($goods, 'num'));
        $order_data['user_remark'] = $remark;
        $order_data['distribution_money'] = 0;
        $order_data['coupon_list_id'] = $coupon_list_id;
        $order_data['create_time'] = time();

        // 线下自提
        if ($goods[0]['delivery_type'] == GoodsEnum::DELIVERY_SELF) {
            $order_data['pickup_code'] = create_rand_number('order', 'pickup_code', 6);
        }

        $order_create = $Order->create($order_data);

        if (false === $order_create) {
            return false;
        }

        if (!empty($coupon_list_id)) {
            self::editCoupon($coupon_list_id, $order_create->id);
        }

        return $order_create->id;
    }


    /**
     * @notes 添加订单商品
     * @param $order_id
     * @param $goods
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/13 6:21 下午
     */
    public static function getOrderGoodsData($order_id, $goods,$user_id)
    {
        $user = User::where('id', $user_id)->find();
        $discount = UserLevel::where('id', $user['level'])->value('discount');
        if($discount == 0 || self::$order_type != OrderEnum::NORMAL_ORDER){
            $discount = 10;
        }
        $goods_data = [];
        foreach ($goods as $key => $value) {
            $Goods = Goods::where('id', $value['goods_id'])->field('name,image,shop_id')->find();
            $GoodsItem = GoodsItem::where([
                ['id', '=', $value['item_id']],
                ['goods_id', '=', $value['goods_id']],
            ])->field('id,goods_id,price,image,spec_value_ids,spec_value_str,weight')
                ->find();
            $goodsItemPrice = GoodsItem::getGoodsItemPrice($GoodsItem);
            $goods_data[$key]['order_id'] = $order_id;
            $goods_data[$key]['shop_id'] = $Goods['shop_id'];
            $goods_data[$key]['goods_id'] = $value['goods_id'];
            $goods_data[$key]['item_id'] = $value['item_id'];
            $goods_data[$key]['goods_num'] = $value['num'];
            $goods_data[$key]['goods_name'] = $Goods['name'];
            $goods_data[$key]['goods_price'] = $goodsItemPrice;
            $goods_data[$key]['total_price'] = round($goodsItemPrice * $value['num'], 2);
            $goods_data[$key]['total_pay_price'] = self::calculateGoodsPrice([$value],$discount);
            $goods_data[$key]['spec_value'] = $GoodsItem['spec_value_str'];
            $goods_data[$key]['spec_value_ids'] = $GoodsItem['spec_value_ids'];
            $goods_data[$key]['image'] = !empty($Goods['image']) ? UrlServer::setFileUrl($Goods['image']) : '';
            $goods_data[$key]['weight'] = $GoodsItem['weight'];
            $goods_data[$key]['create_time'] = time();
        }
        $goods_data = self::shareDiscount($goods_data, $order_id);

        return $goods_data;
    }

    public static function shareDiscount($goodsData, $orderId)
    {
        // 获取订单优惠价格
        $orderDiscount = Order::where('id', $orderId)->value('discount_amount');
        if ($orderDiscount <= 0) {
            // 未使用优惠
            foreach ($goodsData as $key => &$item) {
                $item['discount_price'] = 0;
            }
            return $goodsData;
        }
        // 累计应付总金额
        $sumPayPrice = array_sum(array_column($goodsData, 'total_pay_price')) ? : 0;
        // 根据比例分摊优惠金额
        $sumDiscount = 0;
        foreach ($goodsData as $key => &$item) {
            if ($key == (count($goodsData) - 1)) {
                // 最后一条记录
                $item['discount_price'] = $orderDiscount - $sumDiscount;
            }
            if ($sumPayPrice <= 0) {
                continue;
            }
            $item['discount_price'] = round($item['total_pay_price'] / $sumPayPrice * $orderDiscount, 2);
            // 优惠超过实付金额 使用实付
            $item['discount_price'] = min($item['discount_price'], $item['total_pay_price']);
            
            $item['total_pay_price'] -=  $item['discount_price'];
        }

        return $goodsData;
    }

    /**
     * @notes 扣除商品库存
     * @param $goods
     * @return bool
     * @author suny
     * @date 2021/7/13 6:21 下午
     */
    public static function subGoodsStock($goods)
    {
        $goods_ids = [];

        foreach ($goods as $key => $value) {
            $goods_item_stock_dec = GoodsItem::where([
                ['id', '=', $value['item_id']],
                ['goods_id', '=', $value['goods_id']],
            ])->dec('stock', $value['num'])
                ->update();
            $goods_stock_dec = Goods::where('id', $value['goods_id'])
                ->dec('stock', $value['num'])
                ->update();
            if (false === $goods_item_stock_dec) {
                return false;
            }
            if (false === $goods_stock_dec) {
                return false;
            }

            $goods_ids[] = $value['goods_id'];
        }

        // 下架总库存为0商品
        //库存为0的商品暂不下架，显示为缺货
//        self::outGoods($goods_ids);

        return true;
    }


    /**
     * @notes 下架总库存为0商品
     * @param $goods_ids
     * @return bool|void
     * @author 段誉
     * @date 2021/12/21 14:46
     */
    public static function outGoods($goods_ids)
    {
        try{
            $goods = Goods::where('id', 'in', $goods_ids)
                ->field('id, stock')
                ->select();

            if (empty($goods)) {
                return true;
            }

            $need_handle_ids = [];
            foreach ($goods as $good) {
                if ($good['stock'] <= 0) {
                    $need_handle_ids[] =  $good['id'];
                }
            }

            if (empty($need_handle_ids)){
                return true;
            }
            //下架订单商品中 商品总库存已为0的商品
            Goods::where('id', 'in', $need_handle_ids)->update(['status' => 0]);

            // 下架或删除商品更新收藏状态
            event('UpdateCollect', ['goods_id' => $need_handle_ids]);

        } catch (\Exception $e) {}
    }


    /**
     * @notes 添加订单日志表
     * @param $order_id
     * @param $user_id
     * @param $shop_id
     * @return array
     * @author suny
     * @date 2021/7/13 6:21 下午
     */
    public static function getOrderLogData($order_id, $user_id, $shop_id)
    {

        $order_log_data = [];
        $order_log_data['type'] = OrderLogEnum::TYPE_USER;
        $order_log_data['channel'] = OrderLogEnum::USER_ADD_ORDER;
        $order_log_data['order_id'] = $order_id;
        $order_log_data['handle_id'] = $user_id;
        $order_log_data['shop_id'] = $shop_id;
        $order_log_data['content'] = OrderLogEnum::getLogDesc(OrderLogEnum::USER_ADD_ORDER);
        $order_log_data['create_time'] = time();

        return $order_log_data;

    }

    /**
     * @notes 删除购物车
     * @param $cart_id
     * @return bool
     * @author suny
     * @date 2021/7/13 6:21 下午
     */
    public static function delCart($cart_id)
    {

        $delCart = Cart::where([
            ['id', 'in', $cart_id],
            ['selected', '=', 1]
        ])
            ->delete();
        if (false === $delCart) {
            return false;
        }
        return true;

    }

    /**
     * @notes 订单列表
     * @param $user_id
     * @param $type
     * @param $page
     * @param $size
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/13 6:21 下午
     */
    public static function getOrderList($user_id, $type, $page, $size)
    {

        $order = new Order();
        $where[] = ['del', '=', 0];
        $where[] = ['user_id', '=', $user_id];

        switch ($type) {
            case 'pay':
                $where[] = ['order_status', '=', OrderEnum::ORDER_STATUS_NO_PAID];
                break;
            case 'delivery':
                $where[] = ['order_status', 'in', [OrderEnum::ORDER_STATUS_DELIVERY, OrderEnum::ORDER_STATUS_GOODS]];
                break;
            case 'finish':
                $where[] = ['order_status', '=', OrderEnum::ORDER_STATUS_COMPLETE];
                break;
            case 'close':
                $where[] = ['order_status', '=', OrderEnum::ORDER_STATUS_DOWN];
                break;
        }

        $count = $order->where(['del' => 0, 'user_id' => $user_id])
            ->where($where)
            ->count();

        $lists = $order->where(['del' => 0, 'user_id' => $user_id])
            ->where($where)
            ->with(['order_goods', 'shop'])
            ->field('id,order_sn,pay_way as pay_way_base,pay_way,order_status,pay_status,order_amount,order_status,order_type,shipping_status,create_time,shop_id,delivery_type')
            ->page($page, $size)
            ->order('id desc')
            ->select();

        $lists->append(['goods_count', 'pay_btn', 'cancel_btn', 'delivery_btn', 'take_btn', 'del_btn', 'comment_btn', 'content_btn','order_cancel_time']);

        foreach ($lists as $list) {
            if ($list['order_type'] == OrderEnum::SECKILL_ORDER) {//如果是秒杀
                foreach ($list['order_goods'] as $item) {
                    $seckill_price = GoodsItem::isSeckill($item['item_id']);
                    if ($seckill_price != 0) {
                        $item['goods_price'] = $seckill_price;
                    }
                }
            }

            // 查看提货码按钮
            $list['pickup_btn'] = 0;
            // 订单状态描述
            $list['order_status_desc'] = OrderEnum::getOrderStatus($list['order_status']);
            if ($list['order_status'] == OrderEnum::ORDER_STATUS_DELIVERY
                && $list['delivery_type'] == OrderEnum::DELIVERY_TYPE_SELF
                && $list['pay_status'] == PayEnum::ISPAID
            ) {
                $list['pickup_btn'] = 1;
                $list['order_status_desc'] = '待取货';
            }

            if ($list['order_type'] == OrderEnum::TEAM_ORDER) {
                $team = TeamJoin::field('id,status')
                    ->where(['order_id' => $list['id']])
                    ->findOrEmpty()->toArray();
                if ($team['status'] != TeamEnum::TEAM_STATUS_SUCCESS) {
                    $list['pickup_btn'] = 0;
                }
            }
        }
        $data = [
            'list' => $lists,
            'page' => $page,
            'size' => $size,
            'count' => $count,
            'more' => is_more($count, $page, $size)
        ];
        return $data;
    }

    /**
     * @notes 通过规格id查询秒杀价格
     * @param $item_id
     * @return int|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/13 6:21 下午
     */
    public static function getSekillPriceByItemId($item_id)
    {

        $where = [
            'item_id' => $item_id,
            'del' => 0,
            'review_status' => 1
        ];
        $seckill = SeckillGoods::where($where)->find();
        return isset($seckill['price']) ? $seckill['price'] : 0;
    }

    /**
     * @notes 订单详情
     * @param $order_id
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/13 6:22 下午
     */
    public static function getOrderDetail($order_id)
    {

        $order = Order::with(['order_goods', 'shop'])
            ->field([ '*', 'pay_way as pay_way_base' ])
            ->where(['del' => 0, 'id' => $order_id])
            ->find();
        
        if (isset($order['shop']['id'])) {
            $order['shop']['run_start_time'] = $order['shop']['run_start_time'] ? date('H:i:s', $order['shop']['run_start_time']) : '';
            $order['shop']['run_end_time'] = $order['shop']['run_end_time'] ? date('H:i:s', $order['shop']['run_end_time']) : '';
        }
        
        if ($order) {
            $order->append(['delivery_address', 'pay_btn', 'cancel_btn', 'delivery_btn', 'take_btn', 'del_btn','view_invoice_btn','save_invoice_btn', 'order_cancel_time'])
                ->hidden(['user_id', 'order_source',
                    'city', 'district', 'address', 'shipping_status', 'shipping_code',
                    'pay_status', 'transaction_id', 'del', 'province']);

            // $refund_days = ConfigServer::get('after_sale', 'refund_days', 7 * 86400, 0) * 86400;

            foreach ($order->order_goods as $order_good) {
                if ($order['order_type'] == OrderEnum::SECKILL_ORDER) { // 是秒杀商品
                    $seckill_price = GoodsItem::isSeckill($order_good['item_id']);
                    if ($seckill_price != 0) {
                        $order_good['goods_price'] = $seckill_price;
                    }
                }$order_good['sum_price'] = $order_good['goods_price'] * $order_good['goods_num'];
                $order_good['comment_btn'] = 0;
                if ($order['pay_status'] == PayEnum::ISPAID && $order['order_status'] == OrderEnum::ORDER_STATUS_COMPLETE && $order_good['is_comment'] == 0) {
                    $order_good['comment_btn'] = 1;
                }
                $order_good['refund_btn'] = 0;

                // $confirm_take_time = strtotime($order['confirm_take_time']) ?: 0;
                // $refund_time = $confirm_take_time + $refund_days;
                if ($order['order_status'] == OrderEnum::ORDER_STATUS_COMPLETE && $order_good['refund_status'] == OrderGoodsEnum::REFUND_STATUS_NO) {
                    $order_good['refund_btn'] = 1;
                }
                $order_good['sum_price'] = $order_good['goods_price'] * $order_good['goods_num'];
            }

            //订单商品总价
            $order->goods_price = $order->goods_price + $order->member_amount;
        }

        // 如果是拼团的订单
        if ($order['order_type'] == OrderEnum::TEAM_ORDER) {
            $teamJoin = (new TeamJoin())->where(['order_id' => $order['id']])->findOrEmpty()->toArray();
            $teamJoin['team_snap'] = json_decode($teamJoin['team_snap'], true);
            $order['team'] = [
                'team_activity_id' => $teamJoin['team_activity_id'],
                'team_id' => $teamJoin['team_id'],
                'identity' => $teamJoin['identity'] == 1 ? '团长' : '团员',
                'people_num' => $teamJoin['team_snap']['people_num'],
                'status' => $teamJoin['status'],
                'status_text' => TeamEnum::getStatusDesc($teamJoin['status'])
            ];
        }

        $order['order_type'] = Order::getOrderType($order['order_type']);
        $order['pay_way'] = PayEnum::getPayWay($order['pay_way']);
        $order['create_time'] = $order['create_time'] == 0 ? '' : $order['create_time'];
        $order['update_time'] = $order['update_time'] == 0 ? '' : $order['update_time'];
        $order['confirm_take_time'] = $order['confirm_take_time'] == 0 ? '' : date('Y-m-d H:i:s', $order['confirm_take_time']);;
        $order['shipping_time'] = $order['shipping_time'] == 0 ? '' : date('Y-m-d H:i:s', $order['shipping_time']);
        $order['pay_time'] = $order['pay_time'] == 0 ? '' : date('Y-m-d H:i:s', $order['pay_time']);
        $order['cancel_time'] = $order['cancel_time'] == 0 ? '' : date('Y-m-d H:i:s', $order['cancel_time']);

        // 虚拟商品 发货内容
        if ($order['delivery_type'] != OrderEnum::DELIVERY_TYPE_VIRTUAL || $order['shipping_status'] != OrderEnum::SHIPPING_FINISH) {
            $order['delivery_content'] = '';
        }
        
        // 提货码
        if ($order['order_status'] == OrderEnum::ORDER_STATUS_DELIVERY
            && $order['delivery_type'] == OrderEnum::DELIVERY_TYPE_SELF
            && $order['pay_status'] == PayEnum::ISPAID)
        {
            $order['show_pickup_code'] = 1;
        } else {
            $order['show_pickup_code'] = 0;
            $order['pickup_code'] = "";
        }

        // 订单状态描述
        $order['order_status_desc'] = OrderEnum::getOrderStatus($order['order_status']);
        if ($order['order_status'] == OrderEnum::ORDER_STATUS_DELIVERY && $order['delivery_type'] == OrderEnum::DELIVERY_TYPE_SELF) {
            $order['order_status_desc'] = '待取货';
        }

        // 商家地址
        $shop = Shop::where(['id' => $order['shop_id']])->findOrEmpty();
        $region = Db::name('dev_region')
            ->where('id', 'IN', [$shop['province_id'], $shop['city_id'], $shop['district_id']])
            ->order('level asc')
            ->column('name');
        $order['shop_address'] = implode('', $region) . $shop['address'];

        return $order->toArray();
    }
    
    static function wxReceiveDetail($id, $user_id)
    {
        $result = [
            'transaction_id'    => '',
        ];
        
        $order = Order::where('id', $id)->where('user_id', $user_id)->findOrEmpty();
        
        if ($order->isEmpty()) {
            return $result;
        }
    
        $result['transaction_id'] = $order->transaction_id ? : OrderTrade::where('id', $order->trade_id)->value('transaction_id', '');
    
        return $result;
    }

    /**
     * @notes 取消订单
     * @param $order_id
     * @param $user_id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\PDOException
     * @author suny
     * @date 2021/7/13 6:22 下午
     */
    public static function cancel($order_id, $user_id)
    {
        $time = time();
        $order = Order::with(['orderGoods'])->where(['del' => 0, 'user_id' => $user_id, 'id' => $order_id])->find();

        if (!$order || (int)$order['order_status'] > OrderEnum::ORDER_STATUS_DELIVERY) {
            return JsonServer::error('很抱歉!订单无法取消');
        }

        $cancel_limit = ConfigServer::get('transaction', 'paid_order_cancel_time', 60);
        $limit_time = $order->getOrigin('pay_time') + $cancel_limit * 60;
        if ($limit_time < time() && $order['order_status'] != OrderEnum::ORDER_STATUS_NO_PAID) {
            return JsonServer::error('很抱歉!订单已不可取消');
        }

        Db::startTrans();
        try {
            // 如果是拼团的订单
            $team_join = (new TeamJoin())->where(['order_id' => $order['id'],'status'=>TeamEnum::TEAM_STATUS_CONDUCT])->findOrEmpty()->toArray();//拼团中
            if ($order['order_type'] == OrderEnum::TEAM_ORDER && !empty($team_join)) {
                $team_id = $team_join['team_id'];
                $teamJoin = (new TeamJoin())->alias('TJ')
                    ->field(['TJ.*,O.order_sn,O.order_status,O.pay_status,O.refund_status,O.order_amount'])
                    ->where(['team_id' => $team_id])
                    ->join('order O', 'O.id=TJ.order_id')
                    ->select()->toArray();

                TeamFound::update(['status' => TeamEnum::TEAM_STATUS_FAIL, 'team_end_time' => $time], ['id' => $team_id]);
                foreach ($teamJoin as $item) {
                    TeamJoin::update(['status' => TeamEnum::TEAM_STATUS_FAIL, 'update_time' => $time], ['id' => $item['id']]);
                    OrderRefundLogic::cancelOrder($item['order_id'], OrderLogEnum::TYPE_USER, $user_id);  //取消订单

                    if ($item['pay_status'] == PayEnum::ISPAID) {
                        $order = (new Order())->findOrEmpty($item['order_id'])->toArray();
                        OrderRefundLogic::cancelOrderRefundUpdate($order); //更新订单状态
                        OrderRefundLogic::refund($order, $order['order_amount'], $order['order_amount']); //订单退款
                    }
                }
            } else {
                //取消订单
                OrderRefundLogic::cancelOrder($order_id, OrderLogEnum::TYPE_USER, $user_id);
                //已支付的订单,取消,退款
                if ($order['pay_status'] == PayEnum::ISPAID) {
                    //更新订单状态
                    OrderRefundLogic::cancelOrderRefundUpdate($order);
                    //订单退款
                    OrderRefundLogic::refund($order, $order['order_amount'], $order['order_amount']);
                }
            }

            Db::commit();
            return JsonServer::success('取消成功');
        } catch (Exception $e) {
            Db::rollback();
            self::addErrorRefund($order, $e->getMessage());
            return JsonServer::error($e->getMessage());
        }
    }

    /**
     * @notes 回退商品库存
     * @param $goods
     * @author suny
     * @date 2021/7/13 6:22 下午
     */
    public static function backStock($goods)
    {

        foreach ($goods as $good) {
            //回退库存,回退规格库存,减少商品销量
            Goods::where('id', $good['goods_id'])
                ->update([
                    'stock' => Db::raw('stock+' . $good['goods_num'])
                ]);

            //补充规格表库存
            GoodsItem::where('id', $good['item_id'])
                ->inc('stock', $good['goods_num'])
                ->update();
        }
    }

    /**
     * @notes 增加退款失败记录
     * @param $order
     * @param $err_msg
     * @return int|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author suny
     * @date 2021/7/13 6:22 下午
     */
    public static function addErrorRefund($order, $err_msg)
    {

        $orderRefund = new OrderRefund();
        $refund_data = [
            'order_id' => $order['id'],
            'user_id' => $order['user_id'],
            'refund_sn' => createSn('order_refund', 'refund_sn'),
            'order_amount' => $order['order_amount'],//订单应付金额
            'refund_amount' => $order['order_amount'],//订单退款金额
            'transaction_id' => $order['transaction_id'],
            'create_time' => time(),
            'refund_status' => 2,
            'refund_msg' => json_encode($err_msg, JSON_UNESCAPED_UNICODE),
        ];
        return $orderRefund->insertGetId($refund_data);
    }

    /**
     * @notes 获取退款订单的应付金额
     * @param $order
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/13 6:22 下午
     */
    public static function getOrderTotalFee($order)
    {

        $OrderTrade = new OrderTrade();
        $trade = $OrderTrade
            ->where('transaction_id', $order['transaction_id'])
            ->find();

        $total_fee = $order['order_amount'];
        if ($trade) {
            $total_fee = $trade['order_amount'];
        }
        return $total_fee;
    }


    /**
     * @notes 确认订单
     * @param $order_id
     * @param $user_id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/13 6:22 下午
     */
    public static function confirm($order_id, $user_id)
    {

        $order = Order::where(['del' => 0, 'id' => $order_id])->find();
        if ($order['order_status'] == OrderEnum::ORDER_STATUS_COMPLETE) {
            return JsonServer::error('订单已完成');
        }
        if ($order['shipping_status'] == 0) {
            return JsonServer::error('订单未发货');
        }
        $order->order_status = OrderEnum::ORDER_STATUS_COMPLETE;
        $order->update_time = time();
        $order->confirm_take_time = time();
        $order->save();

        //订单日志
        OrderLogLogic::record(
            OrderLogEnum::TYPE_USER,
            OrderLogEnum::USER_CONFIRM_ORDER,
            $order_id,
            $user_id,
            OrderLogEnum::USER_CONFIRM_ORDER
        );

        return JsonServer::success('确认成功');
    }

    /**
     * @notes 删除订单
     * @param $order_id
     * @param $user_id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/13 6:23 下午
     */
    public static function del($order_id, $user_id)
    {

        $where = [
            'order_status' => OrderEnum::ORDER_STATUS_DOWN,
            'user_id' => $user_id,
            'id' => $order_id,
            'del' => 0,
        ];
        $order = Order::where($where)->find();

        if (!$order) {
            return JsonServer::error('订单无法删除');
        }

//        $res = $order->save(['del' => 1, 'update_time' => time()]);
        $data = ['del' => 1, 'update_time' => time(), 'pat_status' => OrderEnum::ORDER_STATUS_DOWN];
        $res = Order::update($data, ['id' => $order['id']]);
        OrderLogLogic::record(
            OrderLogEnum::TYPE_USER,
            OrderLogEnum::USER_DEL_ORDER,
            $order_id,
            $user_id,
            OrderLogEnum::USER_DEL_ORDER
        );
        return JsonServer::success('删除成功', ['res' => $res]);
    }

    /**
     * @notes 获取订单支付结果
     * @param $trade_id
     * @return array|false
     * @author suny
     * @date 2021/7/13 6:23 下午
     */
    public static function pay_result($id,$from)
    {
        switch ($from) {
            case 'trade' : //如果是父订单类型下单，$id为父订单id
                $result = OrderTrade::alias('ot')
                    ->where([ 'ot.id' => $id ])
                    ->join('order o','o.trade_id = ot.id')
                    ->field(['ot.id', 'ot.t_sn as order_sn', 'o.pay_time', 'o.pay_way', 'ot.total_amount' , 'o.pay_status'])
                    ->order('o.pay_status desc')
                    ->findOrEmpty()
                    ->toArray();
                $result['total_amount'] = '￥' . $result['total_amount'];
                break;

            case 'order' : //如果是子订单类型下单，$id为子订单id
                $result = Order::where(['id' => $id])
                    ->field(['id', 'order_sn', 'pay_time', 'pay_way', 'total_amount', 'pay_status' ])
                    ->findOrEmpty()
                    ->toArray();
                $result['total_amount'] = '￥' . $result['total_amount'];
                break;

            case 'integral':
                $result = IntegralOrder::where(['id' => $id])
                    ->field(['id', 'order_sn', 'pay_time', 'pay_way', 'order_amount', 'order_integral', 'exchange_way', 'pay_status' ])
                    ->findOrEmpty()->toArray();
                $order_integral = $result['order_integral'] > 0 ? $result['order_integral'] . '积分' : '';
                $order_amount = $result['order_amount'] > 0 ? '+ ￥' . $result['order_amount'] . '元' : '';
                $result['total_amount'] = $order_integral . $order_amount;
                break;
            default :
                return false;
        }
        if ($result) {
            $result['pay_time']     = date('Y-m-d H:i:s', $result['pay_time']);
            $result['pay_way']      = PayEnum::getPayWay($result['pay_way']);
            $result['pay_status']   = $result['pay_status'] ?? 0;
            return $result;
        }
        return false;
    }

    /**
     * @notes 获取支付方式
     * @param $user_id
     * @return array|array[]|\array[][]|\array[][][]
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/13 6:23 下午
     */
    public static function getPayWay($user_id, $client, $params)
    {

        $payModel = new Pay();
        $payway = $payModel->where(['status' => 1])->order('sort')->hidden(['config'])->select()->toArray();
        foreach ($payway as $k => &$item) {
            if ($item['code'] == 'wechat') {
                $item['extra'] = '微信快捷支付';
                $item['pay_way'] = PayEnum::WECHAT_PAY;
            }

            if ($item['code'] == 'balance') {
                $user_money = Db::name('user')->where(['id' => $user_id])->value('user_money');
                $item['extra'] = '可用余额:' . $user_money;
                $item['pay_way'] = PayEnum::BALANCE_PAY;
                if($params['from'] == 'recharge') {
                    unset($payway[$k]);
                }
            }

            if ($item['code'] == 'alipay') {
                $item['extra'] = '';
                $item['pay_way'] = PayEnum::ALI_PAY;
                if (in_array($client, [ ClientEnum::mnp, ClientEnum::oa ])) {
                    unset($payway[$k]);
                }
            }
            
            if ($item['code'] == 'hfdg_wechat') {
                $item['extra'] = '';
                $item['pay_way'] = PayEnum::HFDG_WECHAT;
                if (! in_array($client, [ ClientEnum::mnp, ClientEnum::oa ])) {
                    unset($payway[$k]);
                }
            }
    
            if ($item['code'] == 'hfdg_alipay') {
                $item['extra'] = '';
                $item['pay_way'] = PayEnum::HFDG_ALIPAY;
                if (in_array($client, [ ClientEnum::mnp, ClientEnum::oa ])) {
                    unset($payway[$k]);
                }
            }
        }

        if($params['from'] == 'order') {
            $order = Order::findOrEmpty($params['order_id']);
        } else if($params['from'] == 'trade') {
            $order = OrderTrade::findOrEmpty($params['order_id']);
        } else if($params['from'] == 'integral') {
            $order = IntegralOrder::findOrEmpty($params['order_id']);
        } else {
            $order = RechargeOrder::findOrEmpty($params['order_id']);
        }
        
        $cancelTime = ConfigServer::get('transaction', 'unpaid_order_cancel_time', 60);
        if(empty($cancelTime) || $params['from'] == 'integral') {
            $cancelTime = 0;
        } else {
            $cancelTime = strtotime($order['create_time']) + intval($cancelTime) * 60;
        }
        
        return [
            'pay_way' => array_values($payway),
            'order_amount' => $order['order_amount'],
            'cancel_time' => $cancelTime,
        ];
    }

    /**
     * @notes 查询物流
     * @param $id
     * @param $user_id
     * @return array|false
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author suny
     * @date 2021/7/13 6:23 下午
     */
    public static function orderTraces($id, $user_id)
    {

        $order = new Order();
        $order = $order->alias('o')
            ->join('order_goods og', 'o.id = og.order_id')
            ->join('goods g', 'g.id = og.goods_id')
            ->where(['o.id' => $id, 'o.user_id' => $user_id, 'pay_status' => OrderEnum::ORDER_STATUS_DELIVERY, 'o.del' => 0])
            ->field('o.id,o.delivery_id,order_status,total_num,og.image,o.consignee,o.mobile,o.province,o.city,o.district,o.address,pay_time,confirm_take_time,o.shipping_status,shipping_time')
            ->append(['delivery_address'])
            ->find();
        if (!self::checkDelivery($order['delivery_id'])) {
            return false;
        }
        //初始化数据
        $order_tips = '已下单';
        $order_traces = [];
        $traces = [];//物流轨迹
        $shipment = [//发货
            'title' => '已发货',
            'tips' => '',
            'time' => '',
        ];
        $finish = [//交易完成
            'title' => '交易完成',
            'tips' => '',
            'time' => '',
        ];

        if ($order) {
            $order_delivery = Delivery::where(['order_id' => $id])->field('invoice_no,shipping_name,shipping_id,mobile')->find();

            $express = ConfigServer::get('express', 'way', '', '');
            //已发货
            if ($express && $order['shipping_status']) {
                $key = ConfigServer::get($express, 'appkey');
                $app = ConfigServer::get($express, 'appsecret');
                //获取物流配置
                if ($app && $key) {
                    //快递配置设置为快递鸟时
                    if ($express === 'kdniao') {
                        $expressage = (new Kdniao($app, $key, Env::get('app.app_debug', 'true')));
                        $shipping_field = 'codebird';
                    } else {
                        $expressage = (new Kd100($app, $key, Env::get('app.app_debug', 'true')));
                        $shipping_field = 'code100';
                    }
                    //快递编码
                    $shipping_code = Db::name('express')->where(['id' => $order_delivery['shipping_id']])->value($shipping_field);
                    //获取物流轨迹
                    if (in_array(strtolower($shipping_code ), [ 'sf', 'shunfeng' ])) {
                        if ($express === 'kdniao') {
                            $expressage->logistics($shipping_code, $order_delivery['invoice_no'], substr($order_delivery['mobile'],-4));
                        } else {
                            $expressage->logistics($shipping_code, $order_delivery['invoice_no'], $order_delivery['mobile']);
                        }
                    }else {
                        $expressage->logistics($shipping_code, $order_delivery['invoice_no']);
                    }
                    $traces = $expressage->logisticsFormat();
                    //获取不到物流轨迹时
                    if ($traces == false) {
                        $error = $expressage->getError();
                        $error = json_decode($error,true);
                        if ($express === 'kdniao') {
                            if($error['Success'] == false){
                                $traces[] = [$error['Reason']];
                            }
                        } else {
                            if($error['result'] == false){
                                $traces[] = [$error['message']];
                            }
                        }

                    } else {
                        foreach ($traces as &$item) {
                            $item = array_values(array_unique($item));
                        }
                    }

                }
            }
            //待收货
            if ($order['order_status'] == 2) {
                $shipment['tips'] = '商品已出库';
                $shipment['time'] = date('Y-m-d H:i:s', $order['shipping_time']);
            }
            //确认收货
            if ($order['order_status'] == 3) {
                $order_tips = '交易完成';
                $finish['tips'] = '订单交易完成';
                $finish['time'] = $order['confirm_take_time'] ? date('Y-m-d H:i:s', $order['confirm_take_time']) : $order['confirm_take_time'];
            }
            //数据合并
            $order_traces = [
                'order' => [
                    'tips' => $order_tips,
                    'image' => UrlServer::getFileUrl($order['image']),
                    'count' => $order['total_num'],
                    'invoice_no' => $order_delivery['invoice_no'],
                    'shipping_name' => empty($order_delivery['shipping_name']) ? '-' : $order_delivery['shipping_name'],
                ],
                'take' => [
                    'contacts' => $order['consignee'],
                    'mobile' => $order['mobile'],
                    'address' => $order['delivery_address'],
                ],
                'finish' => $finish,
                'delivery' => [
                    'title' => '运输中',
                    'traces' => $traces
                ],
                'shipment' => $shipment,
                'buy' => [
                    'title' => '已下单',
                    'tips' => '订单提交成功',
                    'time' => date('Y-m-d H:i:s', $order['pay_time'])
                ],
            ];
            return $order_traces;
        }

        return $order_traces;

    }

    /**
     * @notes 配送方式无需快递的
     * @param $delivery_id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/13 6:23 下午
     */
    public static function checkDelivery($delivery_id)
    {

        $delivery = Delivery::where(['id' => $delivery_id])
            ->find();
        if ($delivery['send_type'] == 2) {
            return false;
        }
        return true;
    }

    /**
     * @notes 判断商家营业状态
     * @param $value
     * @return bool|string
     * @author suny
     * @date 2021/7/13 6:23 下午
     */
    public static function checkShop($value)
    {
        $shop = Shop::where('id', $value['shop_id'])->field('is_run, is_pay')->findOrEmpty();
        if (!$shop['is_run']) {
            return '该商家已暂停营业';
        }

        if (!$shop['is_pay']) {
            return '该商家支付功能已关闭';
        }
        return true;
    }

    /**
     * @notes 修改优惠券状态
     * @param $coupon_id
     * @param $order_id
     * @return CouponList|false
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/13 6:23 下午
     */
    public static function editCoupon($coupon_id, $order_id)
    {

        $status = CouponList::where(['id' => $coupon_id, 'status' => 0])->find();
        if (!$status) {
            return false;
        }
        $time = time();
        $data = [
            'status' => 1,
            'use_time' => $time,
            'update_time' => $time,
            'order_id' => $order_id
        ];
        $res = CouponList::where('id', $status->id)
            ->update($data);
        return $res;
    }

    /**
     * @notes 设置秒杀商品销量
     * @param $item_id
     * @param $num
     * @return bool
     * @author suny
     * @date 2021/7/13 6:23 下午
     */
    public static function setSeckillSaleSum($item_id, $num)
    {

        $result = SeckillGoods::where('item_id', $item_id)
            ->inc('sales_sum', $num)
            ->save();
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public static function getPayStatus($id,$from)
    {

        switch ($from){
            case 'trade' : //如果是父订单类型下单，$id为父订单id
                $order_trade = OrderTrade::alias('ot')
                    ->where(['ot.id' => $id])
                    ->join('order o','o.trade_id = ot.id')
                    ->field(['ot.id','o.id as order_id', 'ot.t_sn as order_sn', 'o.pay_time', 'o.pay_way', 'ot.total_amount','o.pay_status','o.order_status'])
                    ->select()
                    ->toArray();
                foreach ($order_trade as $item) {
                    if($item['pay_time']){
                        $item['pay_time'] = date('Y-m-d H:i:s', $item['pay_time']);
                    }
                    $create_time = Db::name('order')->where(['id' => $item['order_id']])->value('create_time');
                    $unpaid_order_cancel_time = ConfigServer::get('transaction','unpaid_order_cancel_time',60);
                    $cancel_time = $create_time + $unpaid_order_cancel_time * 60;
                    $item['pay_status_text'] = PayEnum::getPayStatus($item['pay_status']);
                    $result = $item;
                    $goods_lists = Db::name('order_goods')
                        ->where(['order_id' => $item['order_id']])
                        ->field('id,goods_name')
                        ->order('shop_id desc')
                        ->select()
                        ->toArray();
                    foreach ($goods_lists as $goods_list) {
                        $order_goods[] = $goods_list;
                    }
                    $order = Order::where(['id' => $item['order_id']])
                        ->find()
                        ->append(['pc_address'])
                        ->toArray();
                    $contact = $order['consignee'];
                    $mobile = $order['mobile'];
                    $address = [
                        'contact' => $contact,
                        'mobile' => $mobile,
                        'delivery_address' => $order['pc_address'],
                    ];

                    $pay_way_text = PayEnum::getPayWay($item['pay_way']);
                    $result['pay_way'] = $pay_way_text;
                    $result['cancel_time'] = $cancel_time;
                }
                $result['order_goods'] = $order_goods;
                $result['address'] = $address;
                break;

            case 'order' : //如果是子订单类型下单，$id为子订单id
                $result = Order::where(['id' => $id])
                    ->field(['id', 'order_sn', 'pay_time', 'pay_way', 'total_amount','consignee','mobile','province','city','district','address','pay_status','order_status'])
                    ->findOrEmpty()
                    ->append(['pc_address'])
                    ->toArray();
                $create_time = Db::name('order')->where(['id' => $id])->value('create_time');
                $unpaid_order_cancel_time = ConfigServer::get('transaction','unpaid_order_cancel_time',60);
                $cancel_time = $create_time + $unpaid_order_cancel_time * 60;
                $order_goods = Db::name('order_goods')
                    ->where(['order_id' => $result['id']])
                    ->field('id,goods_name')
                    ->order('shop_id desc')
                    ->select()
                    ->toArray();
                $contact = $result['consignee'];
                $mobile = $result['mobile'];
                $address = [
                    'contact' => $contact,
                    'mobile' => $mobile,
                    'delivery_address' => $result['pc_address'],
                ];
                $result['order_goods'] = $order_goods;
                $result['address'] = $address;
                $result['cancel_time'] = $cancel_time;
                if($result['pay_time']){
                    $result['pay_time'] = date('Y-m-d H:i:s', $result['pay_time']);
                }
                break;
            default :
                return false;
        }
        return $result;
    }

}
