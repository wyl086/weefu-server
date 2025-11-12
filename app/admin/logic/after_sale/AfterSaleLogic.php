<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\admin\logic\after_sale;

use app\common\basics\Logic;
use app\common\enum\GoodsEnum;
use app\common\enum\PayEnum;
use app\common\model\after_sale\{AfterSale, AfterSaleLog};
use app\common\enum\ShopEnum;
use app\common\model\order\Order as OrderModel;
use app\common\model\shop\Shop;
use app\common\server\AreaServer;
use app\common\server\ConfigServer;
use app\common\server\ExportExcelServer;
use app\common\server\UrlServer;
use think\facade\Db;

/**
 * 售后管理-逻辑
 * Class GoodsLogic
 * @package app\shop\logic\goods
 */
class AfterSaleLogic extends Logic
{

    /**
     * @notes 售后列表
     * @param array $get
     * @return array
     * @author suny
     * @date 2021/7/14 9:56 上午
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\db\exception\DataNotFoundException
     */
    public static function list($get = [], $is_export = false)
    {

        $after_sale = new AfterSale();
        $where = [];
        $page = '1';
        $limit = '10';
        $where[] = ['o.delete', '=', 0];
        $where[] = ['a.del', '=', 0];
        //售后状态
        if (isset($get['type']) && $get['type'] != '') {
            $where[] = ['a.status', '=', $get['type']];
        }

        //订单搜素
        if (!empty($get['search_key']) && !empty($get['keyword'])) {
            $keyword = $get['keyword'];
            switch ($get['search_key']) {
                case 'sn':
                    $where[] = ['a.sn', 'like', '%' . $keyword . '%'];
                    break;
                case 'order_sn':
                    $where[] = ['o.order_sn', 'like', '%' . $keyword . '%'];
                    break;
                case 'goods_name':
                    $where[] = ['g.goods_name', 'like', '%' . $keyword . '%'];
                    break;
                case 'user_sn':
                    $where[] = ['u.sn', 'like', '%' . $keyword . '%'];
                    break;
                case 'nickname':
                    $where[] = ['u.nickname', 'like', '%' . $keyword . '%'];
                    break;
                case 'user_mobile':
                    $where[] = ['u.mobile', 'like', '%' . $keyword . '%'];
                    break;
            }
        }

        if (isset($get['status']) && $get['status'] != '') {
            $where[] = ['a.status', '=', $get['status']];
        }

        //下单时间
        if (isset($get['start_time']) && $get['start_time'] != '') {
            $where[] = ['a.create_time', '>=', strtotime($get['start_time'])];
        }
        if (isset($get['end_time']) && $get['end_time'] != '') {
            $where[] = ['a.create_time', '<=', strtotime($get['end_time'])];
        }

        if (isset($get['page']) && $get['page'] != '') {
            $page = $get['page'];
        }

        if (isset($get['limit']) && $get['limit'] != '') {
            $limit = $get['limit'];
        }

        // 导出excel
        if (true === $is_export) {
            return self::export($where);
        }

        $field = 'a.id,a.sn,a.status,a.order_id,a.order_goods_id,
        a.user_id,a.refund_type,a.create_time,a.refund_price,
        o.order_status,o.shop_id,o.pay_way';

        $count = $after_sale
            ->alias('a')
            ->join('order o', 'o.id = a.order_id')
            ->join('user u', 'u.id = a.user_id')
            ->join('order_goods g', 'g.id = a.order_goods_id')
            ->join('shop s', 's.id = o.shop_id')
            ->with(['order_goods', 'user', 'order', 'shop'])
            ->where($where)
            ->group('a.id')
            ->count();

        $lists = $after_sale
            ->alias('a')
            ->field($field)
            ->join('order o', 'o.id = a.order_id')
            ->join('user u', 'u.id = a.user_id')
            ->join('order_goods g', 'g.id = a.order_goods_id')
            ->join('shop s', 's.id = o.shop_id')
            ->with(['order_goods', 'user', 'order', 'shop'])
            ->where($where)
            ->page($page, $limit)
            ->order('a.id desc')
            ->append(['user.base_avatar', 'order_goods.base_image'])
            ->group('a.id')
            ->select()->toArray();

        foreach ($lists as &$list) {
            $list['order']['pay_way'] = PayEnum::getPayWay($list['order']['pay_way']);
            $list['order']['order_status'] = OrderModel::getOrderStatus($list['order']['order_status']);
            $list['refund_type'] = AfterSale::getRefundTypeDesc($list['refund_type']);
//            $list['create_time'] = date('Y-m-d H:i:s', $list['create_time']);
            $list['status'] = AfterSale::getStatusDesc($list['status']);
            $list['user']['avatar'] = UrlServer::getFileUrl($list['user']['avatar']);
            $list['shop']['logo'] = UrlServer::getFileUrl($list['shop']['logo']);
            foreach ($list['order_goods'] as &$good) {
                $good['image'] = empty($good['spec_image']) ?
                    UrlServer::getFileUrl($good['image']) : UrlServer::getFileUrl($good['spec_image']);
            }
        }
        return ['count' => $count, 'lists' => $lists];
    }

    /**
     * @notes 售后详情
     * @param $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/14 9:56 上午
     */
    public static function getDetail($id)
    {

        $after_sale = new AfterSale();
        $result = $after_sale
            ->with(['order_goods', 'user', 'order', 'logs'])
            ->where('id', $id)
            ->find()->toArray();
        $result['refund_type_text'] = AfterSale::getRefundTypeDesc($result['refund_type']);
        $result['status_text'] = AfterSale::getStatusDesc($result['status']);
        $result['order']['pay_way'] = PayEnum::getPayWay($result['order']['pay_way']);
        $result['order']['order_status'] = OrderModel::getOrderStatus($result['order']['order_status']);

        foreach ($result['order_goods'] as &$good) {
            $good['image'] = empty($good['spec_image']) ?
                UrlServer::getFileUrl($good['image']) : UrlServer::getFileUrl($good['spec_image']);
        }

        foreach ($result['logs'] as &$log) {

            $log['log_img'] = '';
            $log['log_remark'] = '';
            switch ($log['channel']) {
                //会员申请售后
                case AfterSaleLog::USER_APPLY_REFUND:
                    $log['log_img'] = empty($result['refund_image']) ? '' : UrlServer::getFileUrl($result['refund_image']);
                    $refund_reason = empty($result['refund_reason']) ? '未知' : $result['refund_reason'];
                    $refund_remark = empty($result['refund_remark']) ? '暂无' : $result['refund_remark'];
                    $log['log_remark'] = '退款原因(' . $refund_reason . ')' . '退款说明(' . $refund_remark . ')';
                    break;
                //会员发快递
                case AfterSaleLog::USER_SEND_EXPRESS:
                    $log['log_img'] = empty($result['express_image']) ? '' : UrlServer::getFileUrl($result['express_image']);
                    $express_name = $result['express_name'];
                    $invoice_no = $result['invoice_no'];
                    $express_remark = empty($result['express_remark']) ? '暂无' : $result['express_remark'];
                    $log['log_remark'] = '快递公司(' . $express_name . ')' . '单号(' . $invoice_no . ')' . '备注说明(' . $express_remark . ')';
                    break;
                //商家拒绝退款 //商家拒绝收货
                case AfterSaleLog::SHOP_REFUSE_REFUND:
                case AfterSaleLog::SHOP_REFUSE_TAKE_GOODS:
                    $admin_remark = empty($result['admin_remark']) ? '暂无' : $result['admin_remark'];
                    $log['log_remark'] = '备注:' . $admin_remark;
                    break;
            }

        }

        $result['shop_address'] = self::getShopAddress();
        return $result;
    }

    /**
     * @notes 获取商家地址
     * @return string
     * @author suny
     * @date 2021/7/14 9:57 上午
     */
    public static function getShopAddress()
    {

        $shop_province = ConfigServer::get('shop', 'province_id', '');
        $shop_city = ConfigServer::get('shop', 'city_id', '');
        $shop_district = ConfigServer::get('shop', 'district_id', '');
        $shop_address = ConfigServer::get('shop', 'address', '');
        $shop_contact = ConfigServer::get('shop', 'contact', '');
        $shop_mobile = ConfigServer::get('shop', 'mobile', '');
        if (empty($shop_province) || empty($shop_city) || empty($shop_district)) {
            $arr = [];
        } else {
            $arr = [$shop_province, $shop_city, $shop_district];
        }
        $shop_address = AreaServer::getAddress($arr, $shop_address);
        return $shop_address . '(' . $shop_contact . ',' . $shop_mobile . ')';
    }

    /**
     * @notes 全部数量
     * @return int
     * @author suny
     * @date 2021/7/14 9:57 上午
     */
    public static function getAll()
    {
        $where[] = ['o.delete', '=', 0];
        $where[] = ['a.del', '=', 0];

        return AfterSale::alias('a')
            ->where($where)
            ->join('order o', 'a.order_id = o.id')
            ->count('a.id');
    }

    /**
     * @notes 获取统计数量
     * @param $status
     * @return array
     * @author suny
     * @date 2021/7/14 9:57 上午
     */
    public static function getStatus($status)
    {

        foreach ($status as $key => $value) {
            $count = AfterSale::alias('a')
                ->join('order o', 'a.order_id = o.id')
                ->where(['status' => $key, 'o.delete' => 0,'a.del'=>0])
                ->count('a.id');
            $data[] = $value . "(" . $count . ")";
        }
        return $data;
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
            $field = 'a.sn,a.refund_type,a.refund_price,a.status,a.create_time,
            u.nickname,o.order_sn,s.name as shop_name, s.type as shop_type';

            $lists = (new AfterSale())
                ->alias('a')
                ->field($field)
                ->join('order o', 'o.id = a.order_id')
                ->join('user u', 'u.id = a.user_id')
                ->join('order_goods g', 'g.id = a.order_goods_id')
                ->join('shop s', 's.id = o.shop_id')
                ->with(['order_goods', 'user', 'order'])
                ->where($condition)
                ->order('a.id desc')
                ->group('a.id')
                ->select()->toArray();

            foreach ($lists as &$list) {
                $list['refund_type'] = AfterSale::getRefundTypeDesc($list['refund_type']);
                $list['status'] = AfterSale::getStatusDesc($list['status']);
                $list['shop_type'] = ShopEnum::getShopTypeDesc($list['shop_type']);
            }

            $excelFields = [
                'shop_name' => '商家名称',
                'shop_type' => '商家类型',
                'sn' => '售后单号',
                'nickname' => '用户昵称',
                'order_sn' => '订单编号',
                'refund_type' => '售后方式',
                'refund_price' => '售后金额',
                'status' => '售后状态',
                'create_time' => '申请时间',
            ];

            $export = new ExportExcelServer();
            $export->setFileName('售后');
            $result = $export->createExcel($excelFields, $lists);

            return ['url' => $result];

        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }
}
