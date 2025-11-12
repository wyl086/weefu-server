<?php

namespace app\common\listener;


use app\common\model\printer\PrinterConfig;
use app\common\server\YlyPrinter;
use app\common\model\printer\Printer as PrinterModel;
use app\shop\logic\order\OrderLogic;
use app\shop\logic\printer\PrinterLogic;
use think\facade\Log;

class Printer
{
    public function handle($params)
    {
        try {
            $order_id = $params['order_id'] ?? '';

            if (empty($order_id)) {
                return false;
            }

            //获取订单信息
            $order = OrderLogic::getPrintOrder($order_id);

            //打印机配置
            $config = PrinterConfig::where(['status' => 1, 'shop_id' => $order['shop_id']])->findOrEmpty();

            //打印机列表
            $printers = PrinterModel::where([
                'config_id' => $config['id'],
                'del' => 0,
                'auto_print' => 1,
                'shop_id' => $order['shop_id'],
                'status' => 1
            ])->select()->toArray();

            if (empty($printers) || $config->isEmpty()) {
                return false;
            }

            //获取打印模板
            $template = PrinterLogic::getPrinterTpl($order['shop_id']);

            //示例化打印机类
            $yly_print = new YlyPrinter($config['client_id'], $config['client_secret'], $order['shop_id']);

            //调用打印机
            $yly_print->ylyPrint($printers, $order, $template);

            return true;

        } catch (\Exception $e) {
            Log::write('订单自动打印小票失败:' . $e->getMessage());
        }
    }


}