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


namespace app\shop\logic\order;


use app\common\basics\Logic;
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
     * @notes 列表条件
     * @param $get
     * @param $shop_id
     * @return array
     * @author 段誉
     * @date 2022/4/24 11:05
     */
    public static function getListsCondition($get, $shop_id)
    {
        $where[] = ['o.shop_id', '=', $shop_id];
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
        return $where;
    }

    /**
     * @notes 发票列表
     * @param $get
     * @param $shop_id
     * @return array
     * @author 段誉
     * @date 2022/4/12 17:56
     */
    public static function getInvoiceLists($get, $shop_id)
    {
        $where = self::getListsCondition($get, $shop_id);

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
     * @notes 开票
     * @param $params
     * @author 段誉
     * @date 2022/4/12 18:58
     */
    public static function setInvoice($params)
    {
        OrderInvoice::update([
            'id' => $params['id'],
            'status' => $params['status'],
            'invoice_number' => $params['invoice_number'],
            'invoice_time' => time(),
        ]);
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
    public static function export($get, $shop_id)
    {
        try {
            $where = self::getListsCondition($get, $shop_id);

            $field = ['i.*', 'o.order_sn', 'o.order_amount', 'order_status','o.create_time' => 'order_create_time'];

            $lists = (new OrderInvoice())->alias('i')
                ->field($field)
                ->join('order o', 'o.id = i.order_id')
                ->order('i.id desc')
                ->where($where)
                ->append(['type_text', 'header_type_text', 'status_text'])
                ->select()->toArray();

            foreach ($lists as &$item) {
                $item['order_status'] = Order::getOrderStatus($item['order_status']);
                $item['order_create_time'] = date('Y-m-d h:i:s', $item['order_create_time']);
            }

            $excelFields = [
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