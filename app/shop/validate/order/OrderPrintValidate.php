<?php


namespace app\shop\validate\order;


use app\common\basics\Validate;
use app\common\model\printer\Printer;
use app\common\model\printer\PrinterConfig;

class OrderPrintValidate extends Validate
{
    protected $rule = [
        'id' => 'require|checkPrint',
    ];

    protected $message = [
        'id.require' => '缺少ID字段',
    ];

    protected function checkPrint($value, $rule, $data)
    {
        $config = PrinterConfig::where(['status' => 1, 'shop_id' => $data['shop_id']])->findOrEmpty();

        if ($config->isEmpty()) {
            return '请先到小票打印里面配置打印设置';
        }

        $printer = Printer::where(['config_id' => $config['id'], 'shop_id' => $data['shop_id']])->findOrEmpty();

        if ($printer->isEmpty()) {
            return '请先添加打印机';
        }
        return true;
    }
}