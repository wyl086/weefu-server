<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\admin\logic\order;


use app\common\basics\Logic;
use app\common\enum\OrderEnum;
use app\common\enum\ShopEnum;
use app\common\model\order\Order;
use app\common\enum\OrderLogEnum;
use app\common\enum\PayEnum;
use app\common\logic\OrderRefundLogic;
use app\common\model\Express;
use app\common\model\user\UserLevel;
use app\common\server\ExportExcelServer;
use app\common\server\UrlServer;
use expressage\Kd100;
use expressage\Kdniao;
use app\common\server\ConfigServer;
use think\facade\Db;
use think\facade\Validate;


/**
 * 订单管理-逻辑
 * Class GoodsLogic
 * @package app\shop\logic\goods
 */
class OrderLogic extends Logic
{
    /**
     * @notes 订单统计
     * @param array $get
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/14 10:04 上午
     */
    public static function statistics($get = [], $is_export = false)
    {
        $order  = new Order();
        $where  = static::getWhere($get);
        $page   = $get['page'] ?? 1;
        $limit  = $get['limit'] ?? 10;

        if (Validate::must($get['page'] ?? '')) {
            $page = $get['page'];
        }

        if (Validate::must($get['limit'] ?? '')) {
            $limit = $get['limit'];
        }

        // 导出excel
        if (true === $is_export) {
            return self::export($where);
        }

        $field = 'o.*,s.name as shop_name,u.level';

        $count = $order
            ->alias('o')
            ->field($field)
            ->join('shop s', 's.id = o.shop_id')
            ->join('user u', 'u.id = o.user_id')
            ->join('order_goods g', 'g.order_id = o.id')
            ->with(['order_goods', 'user', 'shop'])
            ->where($where)
            ->group('o.id')
            ->count();

        $lists = $order
            ->alias('o')
            ->field($field)
            ->join('shop s', 's.id = o.shop_id')
            ->join('user u', 'u.id = o.user_id')
            ->join('order_goods g', 'g.order_id = o.id')
            ->with(['order_goods', 'user', 'shop'])
            ->where($where)
            ->append([
                'delivery_address', 'pay_status_text', 'order_source_text',
                'order_status_text', 'delivery_type_text','order_type_text',
                'pay_way_text',
            ])
            ->page($page, $limit)
            ->order('o.id desc')
            ->group('o.id')
            ->select();

        $user_level = UserLevel::where(['del'=>0])->column('name','id');

        foreach ($lists as &$list) {
            $list['pay_time'] = $list['pay_time'] == '0' ? '未支付' : date('Y-m-d H:i:s', $list['pay_time']);
            $list['user']['avatar'] = UrlServer::getFileUrl($list['user']['avatar']);
            $list['shop']['logo'] = UrlServer::getFileUrl($list['shop']['logo']);
            foreach ($list['order_goods'] as $order_good) {
                $order_good['image'] = UrlServer::getFileUrl($order_good['image']);
            }

            // 会员等级
            $list['user_level'] = '暂无等级';
            if(isset($user_level[$list['level']])) {
                $list['user_level'] = $user_level[$list['level']];
            }

            //会员优惠
            $list['member_amount'] = $list['member_amount'] ?? 0.00;
        }
        return ['count' => $count, 'lists' => $lists];
    }

    static function getWhere($data)
    {
        $where = [];
        $where[] = [ 'o.del', '=', 0 ];
        $where[] = [ 'o.delete', '=', 0 ];
        
        //订单状态选项卡
        if (Validate::must($data['type'] ?? '')) {
            $where[] = [ 'order_status', '=', $data['type'] ];
        }
        
        //订单搜素
        if (! empty($data['search_key']) && ! empty($data['keyword'])) {
            $keyword = $data['keyword'];
            switch ($data['search_key']) {
                case 'order_sn':
                    $where[] = ['o.order_sn', '=', $keyword];
                    break;
                case 'user_sn':
                    $where[] = ['u.sn', '=', $keyword];
                    break;
                case 'shop_name':
                    $where[] = ['s.name', 'like', '%' . $keyword . '%'];
                    break;
                case 'goods_name':
                    $where[] = ['g.goods_name', 'like', '%' . $keyword . '%'];
                    break;
                case 'user_id':
                    $where[] = ['o.user_id', '=', $keyword];
                    break;
                case 'nickname':
                    $where[] = ['u.nickname', 'like', '%' . $keyword . '%'];
                    break;
                case 'user_mobile':
                    $where[] = ['u.mobile', '=', $keyword];
                    break;
                case 'consignee':
                    $where[] = ['consignee', '=', $keyword];
                    break;
                case 'consignee_mobile':
                    $where[] = ['o.mobile', '=', $keyword];
                    break;
            }
        }
        
        //商家名称
        if (Validate::must($data['shop_name'] ?? '')) {
            $where[] = [ 's.name', 'like', '%' . $data['shop_name'] . '%' ];
        }
        
        //商品名称
        if (Validate::must($data['goods_name'] ?? '')) {
            $where[] = ['g.goods_name', 'like', '%' . $data['goods_name'] . '%'];
        }
        
        //配送方式
        if (Validate::must($data['delivery_type'] ?? '')) {
            $where[] = ['o.delivery_type', '=', $data['delivery_type']];
        }
        
        //订单状态
        if (Validate::must($data['order_status'] ?? '')) {
            $where[] = [ 'o.order_status', '=', $data['order_status'] ];
        }
        
        //订单类型
        if (Validate::must($data['order_type'] ?? '')) {
            $where[] = [ 'o.order_type', '=', $data['order_type'] ];
        }
        
        //付款方式
        if (Validate::must($data['pay_way'] ?? '')) {
            $where[] = [ 'o.pay_way', '=', $data['pay_way'] ];
        }
        
        //订单来源
        if (Validate::must($data['order_source'] ?? '')) {
            $where[] = ['o.order_source', '=', $data['order_source']];
        }
    
        //下单时间
        if (Validate::must($data['start_time'] ?? '')) {
            $where[] = ['o.create_time', '>=', strtotime($data['start_time'])];
        }
    
        if (Validate::must($data['end_time'] ?? '')) {
            $where[] = ['o.create_time', '<=', strtotime($data['end_time'])];
        }
        
        return $where;
    }

    /**
     * @notes 订单详情
     * @param $id
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/14 10:04 上午
     */

    public static function getDetail($id)
    {
        $order = new Order();
        $result = $order
            ->with(['user', 'order_goods', 'invoice'])
            ->where('id', $id)
            ->append(['delivery_address', 'pay_status_text', 'order_status_text', 'pay_way_text', 'order_type_text'])
            ->find();
        $result['pay_time'] = $result['pay_time'] == '0' ? '未支付' : date('Y-m-d H:i:s', $result['pay_time']);
        $result['user']['avatar'] = UrlServer::getFileUrl($result['user']['avatar']);
        foreach ($result['order_goods'] as &$order_goods) {
            $order_goods['goods_image'] = empty($order_goods['spec_image']) ?
                UrlServer::getFileUrl($order_goods['image']) : UrlServer::getFileUrl($order_goods['spec_image']);
        }

        // 自提提货时间
        if ($result['delivery_type'] == OrderEnum::DELIVERY_TYPE_SELF && $result['verification_status']) {
            $result['confirm_take_time'] = date('Y-m-d H:i:s', $result['confirm_take_time']);
        } else {
            $result['confirm_take_time'] = '';
        }

        //会员优惠
        $result['member_amount'] = $result['member_amount'] ?? 0.00;

        return $result;
    }

    /**
     * @notes 物流信息
     * @param $order_id
     * @return array|\PDOStatement|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author suny
     * @date 2021/7/14 10:05 上午
     */
    public static function shippingInfo($order_id)
    {
        $shipping = Db::name('delivery')->where('order_id', $order_id)->find();
        if ($shipping) {
            $shipping['create_time_text'] = date('Y-m-d H:i:s', $shipping['create_time']);
        }
        $shipping['traces'] = self::getShipping($order_id);
        return $shipping;
    }

    /**
     * @notes 物流轨迹
     * @param $order_id
     * @return bool|string[]
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/14 10:05 上午
     */
    public static function getShipping($order_id)
    {
        $orderModel = new Order();
        $order = $orderModel->alias('o')
            ->field('invoice_no,shipping_name,shipping_id,o.shipping_status,o.mobile')
            ->join('delivery d', 'd.order_id = o.id')
            ->where(['o.id' => $order_id])
            ->find();
        $express = ConfigServer::get('express', 'way', '', '');
        $key = ConfigServer::get($express, 'appkey', '', '');
        $app = ConfigServer::get($express, 'appsecret', '', '');

        if (empty($express) || $order['shipping_status'] != 1 || empty($app) || empty($key)) {
            return $traces[] = ['暂无物流信息'];
        }
        //快递配置设置为快递鸟时
        if ($express === 'kdniao') {
            $expressage = (new Kdniao($app, $key, true));
            $shipping_field = 'codebird';
        } else {
            $expressage = (new Kd100($app, $key, true));
            $shipping_field = 'code100';
        }

        //快递编码
        $shipping_code = Db::name('express')
            ->where(['id' => $order['shipping_id']])
            ->value($shipping_field);

        //获取物流轨迹
        if (in_array(strtolower($shipping_code ), [ 'sf', 'shunfeng' ])) {
            if ($express === 'kdniao') {
                $expressage->logistics($shipping_code, $order['invoice_no'], substr($order['mobile'],-4));
            } else {
                $expressage->logistics($shipping_code, $order['invoice_no'], $order['mobile']);
            }
        }else {
            $expressage->logistics($shipping_code, $order['invoice_no']);
        }

        $traces = $expressage->logisticsFormat();
        if ($traces == false) {
            $traces[] = [$expressage->getError()];
        } else {
            foreach ($traces as &$item) {
                $item = array_values(array_unique($item));
            }
        }
        return $traces;
    }

    /**
     * @notes 获取物流
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/14 10:05 上午
     */
    public static function express()
    {

        return Express::where('del', 0)->select();
    }


    /**
     * @notes 取消订单(返回商品规格表库存)
     * @param $order_id
     * @param $admin_id
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     * @author suny
     * @date 2021/7/14 10:07 上午
     */
    public static function cancel($order_id, $admin_id)
    {

        Db::startTrans();
        try {
            $order = Order::where(['id' => $order_id], ['orderGoods'])->find();
            //取消订单
            OrderRefundLogic::cancelOrder($order_id, OrderLogEnum::TYPE_SHOP, $admin_id);
            //已支付的订单,取消,退款
            if ($order['pay_status'] == PayEnum::ISPAID) {
                //更新订单状态
                OrderRefundLogic::cancelOrderRefundUpdate($order);
                //订单退款
                OrderRefundLogic::refund($order, $order['order_amount'], $order['order_amount']);
            }
            Db::commit();

            return true;

        } catch (\Exception $e) {
            Db::rollback();
            //增加退款失败记录
            OrderRefundLogic::addErrorRefund($order, $e->getMessage());
            return $e->getMessage();
        }
    }


    /**
     * @notes 获取各列表数量
     * @return int
     * @author suny
     * @date 2021/7/14 10:08 上午
     */
    public static function getAll()
    {
        $data = input();
        
        unset($data['type']);
        
        return Order::alias('o')
            ->join('shop s', 's.id = o.shop_id')
            ->join('user u', 'u.id = o.user_id')
            ->join('order_goods g', 'g.order_id = o.id')
            ->with(['order_goods', 'user', 'shop'])
            ->where(static::getWhere($data))
            ->group('o.id')
            ->count();
    }

    /**
     * @notes 拼装统计信息
     * @param $order_status
     * @return array
     * @author suny
     * @date 2021/7/14 10:08 上午
     */
    public static function getStat($order_status)
    {
        $data   = input();
        
        unset($data['type']);
        
        $result = [];
        
        foreach ($order_status as $status => $title) {
            $num = Order::alias('o')
                ->join('shop s', 's.id = o.shop_id')
                ->join('user u', 'u.id = o.user_id')
                ->join('order_goods g', 'g.order_id = o.id')
                ->with(['order_goods', 'user', 'shop'])
                ->where(static::getWhere($data))
                ->where('o.order_status', $status)
                ->group('o.id')
                ->count();
            
            $result[] = [
                'title'     => $title,
                'status'    => $status,
                'count'     => $num,
            ];
        }
        
        return $result;
    }


    /**
     * @notes 导出Excel
     * @param array $condition
     * @return array|false
     * @author 段誉
     * @date 2022/4/24 10:10
     */
    public static function export($condition = [])
    {
        try {
            $field = 'o.*,order_status as order_status_text,pay_way as pay_way_text,
            o.delivery_type as delivery_type_text,order_type as order_type_text,
            u.nickname,s.name as shop_name,s.type as shop_type';

            $lists = Order::alias('o')
                ->join('shop s', 's.id = o.shop_id')
                ->join('user u', 'u.id = o.user_id')
                ->join('order_goods g', 'g.order_id = o.id')
                ->with(['order_goods'])
                ->field($field)
                ->where($condition)
                ->append(['delivery_address', 'pay_status_text', 'order_source_text'])
                ->order('o.id desc')
                ->group('o.id')
                ->select()
                ->toArray();

            foreach ($lists as &$item) {
                $orderGoodsList = [];
                $goodsItemList = [];
                $goodsPriceList = [];
                $goodsNumList = [];
                foreach ($item['order_goods'] as $good) {
                    $orderGoodsList[] = $good['goods_name'];
                    $goodsItemList[] = $good['spec_value'];
                    $goodsPriceList[] = $good['goods_price'];
                    $goodsNumList[] = $good['goods_num'];
                }
                $item['order_goods_list'] = implode(';', $orderGoodsList);
                $item['goods_item_list'] = implode(';', $goodsItemList);
                $item['goods_price_list'] = implode(';', $goodsPriceList);
                $item['goods_num_list'] = implode(';', $goodsNumList);
                $item['shop_type'] = ShopEnum::getShopTypeDesc($item['shop_type']);
            }

            $excelFields = [
                'shop_name' => '商家名称',
                'shop_type' => '商家类型',
                'order_sn' => '订单编号',
                'order_type_text' => '订单类型',
                'nickname' => '用户名称',
                'order_goods_list' => '商品信息',
                'goods_item_list' => '规格',
                'goods_price_list' => '商品价格',
                'goods_num_list' => '商品数量',
                'order_amount' => '实付金额',
                'consignee' => '收货人',
                'mobile' => '收货人手机',
                'delivery_address' => '收货地址',
                'pay_status_text' => '支付状态',
                'order_status_text' => '订单状态',
                'create_time' => '下单时间',
            ];

            $export = new ExportExcelServer();
            $export->setFileName('订单');
            $result = $export->createExcel($excelFields, $lists);

            return ['url' => $result];

        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

}