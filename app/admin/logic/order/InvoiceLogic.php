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
use app\common\enum\ShopEnum;
use app\common\model\order\Order;
use app\common\model\order\OrderInvoice;
use app\common\server\ExportExcelServer;


/**
 * 发票管理-逻辑
 * Class InvoiceLogic
 * @package app\shop\logic\order
 */
class InvoiceLogic extends Logic
{

    /**
     * @notes 发票列表
     * @param $get
     * @param $shop_id
     * @return array
     * @author 段誉
     * @date 2022/4/12 17:56
     */
    public static function getInvoiceLists($get, $is_export = false)
    {
        $where = [];
        if (isset($get['status']) && is_numeric($get['status']) && $get['status'] != '') {
            $where[] = ['i.status', '=', (int)$get['status']];
        }

        if (!empty($get['order_sn']) && $get['order_sn'] != '') {
            $where[] = ['order_sn', 'like', '%'.$get['order_sn'].'%'];
        }

        if (isset($get['order_status']) && $get['order_status'] != '') {
            $where[] = ['order_status', '=', $get['order_status']];
        }

        // 创建时间
        if(isset($get['start_time']) && !empty($get['start_time'])) {
            $where[] = ['o.create_time', '>=', strtotime($get['start_time']) ];
        }

        if(isset($get['end_time']) && !empty($get['end_time'])) {
            $where[] = ['o.create_time', '<=', strtotime($get['end_time']) ];
        }

        if (true === $is_export) {
            return self::export($where);
        }

        $field = ['i.*', 'o.order_sn', 'o.order_amount', 'order_status','o.create_time' => 'order_create_time'];

        $model = new OrderInvoice();
        $lists = $model->alias('i')->field($field)
            ->join('order o', 'o.id = i.order_id')
            ->order('i.id desc')
            ->where($where)
            ->append(['type_text', 'header_type_text', 'status_text'])
            ->paginate([
                'page'      => $get['page'] ?? 1,
                'list_rows' => $get['limit'] ?? 10,
                'var_page'  => 'page'
            ])->toArray();

        foreach ($lists['data'] as &$item) {
            $item['order_status'] = Order::getOrderStatus($item['order_status']);
            $item['order_create_time'] = date('Y-m-d h:i:s', $item['order_create_time']);
        }

        return ['count'=>$lists['total'], 'lists'=>$lists['data']];
    }


    /**
     * @notes 发票详情
     * @param $id
     * @return array
     * @author 段誉
     * @date 2022/4/12 18:55
     */
    public static function detail($id)
    {
        $invoice = OrderInvoice::with(['order_data'])
            ->append(['type_text', 'header_type_text', 'status_text'])
            ->findOrEmpty($id)
            ->toArray();
        return $invoice;
    }


    /**
     * @notes 导出Excel
     * @param array $condition
     * @return array|false
     * @author 段誉
     * @date 2022/4/24 10:10
     */
    public static function export($where)
    {
        try {
            $field = ['i.*', 'o.order_sn', 'o.order_amount', 'order_status','o.create_time' => 'order_create_time',
            's.name' => 'shop_name', 's.type' => 'shop_type'];

            $lists = (new OrderInvoice())->alias('i')
                ->field($field)
                ->join('order o', 'o.id = i.order_id')
                ->join('shop s', 's.id = i.shop_id')
                ->order('i.id desc')
                ->where($where)
                ->append(['type_text', 'header_type_text', 'status_text'])
                ->select()->toArray();

            foreach ($lists as &$item) {
                $item['order_status'] = Order::getOrderStatus($item['order_status']);
                $item['order_create_time'] = date('Y-m-d h:i:s', $item['order_create_time']);
                $item['shop_type'] = ShopEnum::getShopTypeDesc($item['shop_type']);
            }

            $excelFields = [
                'shop_name' => '商家名称',
                'shop_type' => '商家类型',
                'order_sn' => '订单编号',
                'order_amount' => '订单金额',
                'order_status' => '订单状态',
                'order_create_time' => '下单时间',
                'type_text' => '发票类型',
                'header_type_text' => '抬头类型',
                'name' => '发票抬头',
                'duty_number' => '税号',
                'email' => '邮箱',
                'status_text' => '开票状态',
                'invoice_number' => '发票编号',
            ];

            $export = new ExportExcelServer();
            $export->setFileName('发票');
            $export->setExportNumber(['invoice_number']);
            $result = $export->createExcel($excelFields, $lists);

            return ['url' => $result];

        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

}