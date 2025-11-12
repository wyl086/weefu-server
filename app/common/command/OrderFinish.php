<?php


namespace app\common\command;


use app\common\enum\OrderEnum;
use app\common\model\order\Order;
use app\common\server\ConfigServer;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Log;

class OrderFinish extends Command
{
    protected function configure()
    {
        $this->setName('order_finish')
            ->setDescription('自动确认收货(待收货订单)');
    }

    protected function execute(Input $input, Output $output)
    {
        try {
            $time = time();
            $config = ConfigServer::get('transaction', 'order_auto_receipt_days', 7);
            if ($config == 0) {
                return true;
            }

            $finish_limit = $config * 24 * 60 * 60;
            $model = new Order();
            $orders = $model->field(true)->where([
                ['order_status', '=', OrderEnum::ORDER_STATUS_GOODS],
                ['pay_status', '=', OrderEnum::PAY_STATUS_PAID],
                ['del', '=', 0]
            ])->whereRaw("shipping_time+$finish_limit < $time")
              ->select()->toArray();

            foreach ($orders as $order) {
                $model->where(['id' => $order['id']])
                    ->update([
                        'order_status'      => OrderEnum::ORDER_STATUS_COMPLETE,
                        'update_time'       => $time,
                        'confirm_take_time' => $time,
                    ]);
            }

            return true;
        } catch (\Exception $e) {
            Log::write('自动确认收货异常:'.$e->getMessage());
            return false;
        }
    }
}