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


namespace app\shop\logic\after_sale;


use app\common\basics\Logic;
use app\common\enum\NoticeEnum;
use app\common\enum\OrderGoodsEnum;
use app\common\enum\PayEnum;
use app\common\model\after_sale\{AfterSale, AfterSaleLog};
use app\common\logic\AfterSaleLogLogic;
use app\common\logic\OrderRefundLogic;
use app\common\model\order\Order as OrderModel;
use app\common\model\order\Order;
use app\common\model\order\OrderGoods;
use app\common\model\shop\Shop;
use app\common\server\AreaServer;
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
     * @param $shop_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author suny
     * @date 2021/7/14 10:19 上午
     */
    public static function list($get = [], $shop_id = 0, $is_export = false)
    {

        $after_sale = new AfterSale();
        $where = [];
        $page = '1';
        $limit = '10';
        $where[] = ['o.delete', '=', 0];
        $where[] = ['a.del', '=', 0];
        $where[] = ['o.shop_id', '=', $shop_id];
        //订单类型
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
            ->with(['order_goods', 'user', 'order'])
            ->where($where)
            ->group('a.id')
            ->count();

        $lists = $after_sale
            ->alias('a')
            ->field($field)
            ->join('order o', 'o.id = a.order_id')
            ->join('user u', 'u.id = a.user_id')
            ->join('order_goods g', 'g.id = a.order_goods_id')
            ->with(['order_goods', 'user', 'order'])
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
            $list['status'] = AfterSale::getStatusDesc($list['status']);
            $list['user']['avatar'] = UrlServer::getFileUrl($list['user']['avatar']);
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
     * @date 2021/7/14 10:19 上午
     */
    public static function getDetail($id,$shop_id)
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

        $result['shop_address'] = self::getShopAddress($shop_id);
        return $result;
    }

    /**
     * @notes 获取商家地址
     * @return string
     * @author suny
     * @date 2021/7/14 10:19 上午
     */
    public static function getShopAddress($shop_id)
    {

        $shop_info = Shop::where('id', $shop_id)->find();

        $refund_address = $shop_info['refund_address'];
        $shop_province = $refund_address['province_id'] ?? ''; //省份
        $shop_city = $refund_address['city_id'] ?? ''; //城市
        $shop_district = $refund_address['district_id'] ?? ''; //县区
        $shop_address = $refund_address['address'] ?? ''; //详细地址
        $shop_contact = $refund_address['nickname'] ?? ''; //联系人
        $shop_mobile = $refund_address['mobile'] ?? ''; //联系电话

        //组装退货地址
        if (empty($shop_province) || empty($shop_city) || empty($shop_district)) {
            $arr = [];
        } else {
            $arr = [$shop_province, $shop_city, $shop_district];
        }
        $shop_address = AreaServer::getAddress($arr, $shop_address);

        return $shop_address . '(' . $shop_contact . ',' . $shop_mobile . ')';
    }

    /**
     * @notes 商家同意售后
     * @param $id
     * @param $shop_id
     * @return false
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/14 10:19 上午
     */
    public static function agree($id, $shop_id)
    {

        $after_sale = AfterSale::find($id);
        if ($after_sale['del'] == 1) {
            return false;
        }
        $after_sale->update_time = time();
        //仅退款
        if ($after_sale['refund_type'] == AfterSale::TYPE_ONLY_REFUND) {
            $after_sale->status = AfterSale::STATUS_WAIT_REFUND;//更新为等待退款状态
        }

        //退款退货
        if ($after_sale['refund_type'] == AfterSale::TYPE_REFUND_RETURN) {
            $after_sale->status = AfterSale::STATUS_WAIT_RETURN_GOODS;//更新为商品待退货状态
        }

        $after_sale->save();
        //记录日志
        AfterSaleLogLogic::record(
            AfterSaleLog::TYPE_SHOP,
            AfterSaleLog::SHOP_AGREE_REFUND,
            $after_sale['order_id'],
            $after_sale['id'],
            $shop_id,
            AfterSaleLog::SHOP_AGREE_REFUND
        );

        // 仅退款;更新订单商品为等待退款
        if ($after_sale['refund_type'] == AfterSale::TYPE_ONLY_REFUND) {
            $order_goods = OrderGoods::find(['id' => $after_sale['order_goods_id']]);
            $order_goods->refund_status = OrderGoodsEnum::REFUND_STATUS_WAIT;//等待退款
            $order_goods->save();
        }

        $mobile = Order::where(['id' => $after_sale['order_id']])->value('mobile');

        //通知用户
        event('Notice', [
            'scene' => NoticeEnum::AFTER_SALE_NOTICE,
            'mobile' => $mobile,
            'params' => [
                'after_sale_sn' => $after_sale['sn'],
                'user_id' => $after_sale['user_id'],
                'after_sale_result' => AfterSaleLog::getLogDesc(AfterSaleLog::SHOP_AGREE_REFUND),
                'after_sale_remark' => AfterSaleLog::getLogDesc(AfterSaleLog::SHOP_AGREE_REFUND),
                'time' => date('Y-m-d H:i:s', time())
            ]
        ]);
    }


    /**
     * @notes 商家拒绝
     * @param $post
     * @param $shop_id
     * @return false
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/14 10:19 上午
     */
    public static function refuse($post, $shop_id)
    {

        $id = $post['id'];
        $after_sale = AfterSale::find($id);
        if ($after_sale['del'] == 1) {
            return false;
        }
        $after_sale->update_time = time();
        $after_sale->status = AfterSale::STATUS_REFUSE_REFUND;//更新为拒绝退款状态
        $after_sale->admin_remark = isset($post['remark']) ? $post['remark'] : '';
        $after_sale->save();
        //记录日志
        AfterSaleLogLogic::record(
            AfterSaleLog::TYPE_SHOP,
            AfterSaleLog::SHOP_REFUSE_REFUND,
            $after_sale['order_id'],
            $after_sale['id'],
            $shop_id,
            AfterSaleLog::SHOP_REFUSE_REFUND
        );

        $mobile = Order::where(['id' => $after_sale['order_id']])->value('mobile');

        //通知用户
        event('Notice', [
            'scene' => NoticeEnum::AFTER_SALE_NOTICE,
            'mobile' => $mobile,
            'params' => [
                'after_sale_sn' => $after_sale['sn'],
                'user_id' => $after_sale['user_id'],
                'after_sale_result' => AfterSaleLog::getLogDesc(AfterSaleLog::SHOP_REFUSE_REFUND),
                'after_sale_remark' => AfterSaleLog::getLogDesc(AfterSaleLog::SHOP_REFUSE_REFUND),
                'time' => date('Y-m-d H:i:s', time())
            ]
        ]);
    }


    /**
     * @notes 商家收货
     * @param $post
     * @param $admin_id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/14 10:20 上午
     */
    public static function takeGoods($post, $admin_id)
    {

        $id = $post['id'];
        $after_sale = AfterSale::find($id);
        $after_sale->update_time = time();
        $after_sale->status = AfterSale::STATUS_WAIT_REFUND;//更新为等待退款状态
        $after_sale->save();
        //记录日志
        AfterSaleLogLogic::record(
            AfterSaleLog::TYPE_SHOP,
            AfterSaleLog::SHOP_TAKE_GOODS,
            $after_sale['order_id'],
            $after_sale['id'],
            $admin_id,
            AfterSaleLog::SHOP_TAKE_GOODS
        );
        //更新订单商品为等待退款状态
        $order_goods = OrderGoods::find(['id' => $after_sale['order_goods_id']]);
        $order_goods->refund_status = OrderGoodsEnum::REFUND_STATUS_WAIT;//等待退款
        $order_goods->save();
    }


    /**
     * @notes 商家拒绝收货
     * @param $post
     * @param $admin_id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/14 10:20 上午
     */
    public static function refuseGoods($post, $admin_id)
    {

        $id = $post['id'];
        $after_sale = AfterSale::find($id);
        $after_sale->update_time = time();
        $after_sale->status = AfterSale::STATUS_REFUSE_RECEIVE_GOODS;//更新为拒绝收货状态
        $after_sale->admin_remark = isset($post['remark']) ? $post['remark'] : '';
        $after_sale->save();
        //记录日志
        AfterSaleLogLogic::record(
            AfterSaleLog::TYPE_SHOP,
            AfterSaleLog::SHOP_REFUSE_TAKE_GOODS,
            $after_sale['order_id'],
            $after_sale['id'],
            $admin_id,
            AfterSaleLog::SHOP_REFUSE_TAKE_GOODS
        );
    }


    /**
     * @notes 确认退款 ===> 退款
     * @param $id
     * @param $admin_id
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     * @author suny
     * @date 2021/7/14 10:20 上午
     */
    public static function confirm($id, $admin_id)
    {

        //售后记录状态
        $after_sale = AfterSale::find($id);

        if($after_sale['del'] == 1){
            return '该售后已经撤销';
        }
        $order = OrderModel::find(['id' => $after_sale['order_id']]);
        $order_goods = OrderGoods::find(['id' => $after_sale['order_goods_id']]);

        Db::startTrans();
        try {
            //更新售后为退款成功状态
            $after_sale->update_time = time();
            $after_sale->status = AfterSale::STATUS_SUCCESS_REFUND;
            $after_sale->save();
            //售后日志
            AfterSaleLogLogic::record(
                AfterSaleLog::TYPE_SHOP,
                AfterSaleLog::REFUND_SUCCESS,
                $after_sale['order_id'],
                $after_sale['id'],
                $admin_id,
                AfterSaleLog::REFUND_SUCCESS
            );
            //更新订单和订单商品状态
            OrderRefundLogic::afterSaleRefundUpdate($order, $order_goods['id'], $admin_id);
            //订单退款
            OrderRefundLogic::refund($order, $order['order_amount'], $after_sale['refund_price']);

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            //增加退款失败记录
            OrderRefundLogic::addErrorRefund($order, $e->getMessage());
            //记录日志
            AfterSaleLogLogic::record(
                AfterSaleLog::TYPE_SHOP,
                AfterSaleLog::REFUND_ERROR,//退款失败
                $after_sale['order_id'],
                $after_sale['id'],
                $admin_id,
                AfterSaleLog::REFUND_ERROR,//退款失败
                $e->getMessage()
            );
            return $e->getMessage();
        }
    }

    /**
     * @notes 全部数量
     * @return int
     * @author suny
     * @date 2021/7/13 3:53 下午
     */
    public static function getAll($shop_id)
    {

        return AfterSale::alias('a')
            ->where(['a.del' => 0, 'shop_id' => $shop_id])
            ->join('order o', 'a.order_id = o.id')
            ->count('a.id');
    }

    /**
     * @notes 获取统计数量
     * @param $status
     * @return array
     * @author suny
     * @date 2021/7/13 4:07 下午
     */
    public static function getStatus($status, $shop_id)
    {

        foreach ($status as $key => $value) {
            $count = AfterSale::alias('a')
                ->join('order o', 'a.order_id = o.id')
                ->where(['status' => $key, 'a.del' => 0, 'shop_id' => $shop_id])
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
            u.nickname,o.order_sn';

            $lists = (new AfterSale())
                ->alias('a')
                ->field($field)
                ->join('order o', 'o.id = a.order_id')
                ->join('user u', 'u.id = a.user_id')
                ->join('order_goods g', 'g.id = a.order_goods_id')
                ->with(['order_goods', 'user', 'order'])
                ->where($condition)
                ->order('a.id desc')
                ->group('a.id')
                ->select()->toArray();

            foreach ($lists as &$list) {
                $list['refund_type'] = AfterSale::getRefundTypeDesc($list['refund_type']);
                $list['status'] = AfterSale::getStatusDesc($list['status']);
            }

            $excelFields = [
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
