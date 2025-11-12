<?php
namespace app\common\command;

use app\common\model\bargain\BargainLaunch;
use app\common\model\bargain\Bargain;
use app\common\enum\BargainEnum;
use app\common\server\ConfigServer;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Log;

class BargainClose extends Command
{
    protected function configure()
    {
        $this->setName('bargain_close')
            ->setDescription('关闭砍价记录');
    }
    protected function execute(Input $input, Output $output)
    {
        try {
            $now = time();
            $bargainModel = new Bargain();
            $bargainLaunchModel = new BargainLaunch();

            $succeed_ids = [];
            $defeat_ids = [];
            //砍价成功后的下单时间
            $payment_limit_time = ConfigServer::get('bargain', 'payment_limit_time', 0) * 60;
            if($payment_limit_time > 0){
                $payment_limit_time = $now + $payment_limit_time;
            }
            //找出所有超时未关掉的订单
            $bargainLaunchModel->where([['status','=',BargainLaunch::conductStatus],['launch_end_time','<=',$now]])
                ->chunk(100, function($launchs) use(&$succeed_ids,&$defeat_ids) {

                    foreach ($launchs as $launch){
                        $launch = $launch->toarray();
                        //任意金额购买时，更新砍价成功
                        if(2 == $launch['bargain_snap']['payment_where']){
                            $succeed_ids[] = $launch['id'];
                        }else{
                            $defeat_ids[] = $launch['id'];

                        }
                    }
                });
            //标记成功
            if($succeed_ids){
                $bargainLaunchModel->where(['id'=>$succeed_ids])->update(['status'=>BargainLaunch::successStatus,'payment_limit_time'=>$payment_limit_time,'bargain_end_time'=>$now]);
            }
            //标记失败
            if($defeat_ids){
                $bargainLaunchModel->where(['id'=>$defeat_ids])->update(['status'=>BargainLaunch::failStatus,'bargain_end_time'=>$now]);
            }

            // 查询出要关闭的砍价活动
            $bargain_ids = $bargainModel->where([
                ['activity_end_time', '<', $now],
                ['del', '=', 0],
                ['status', '=', 1]
            ])->column('id');

            // 结束砍价活动(结束时间 < 当前时间)
            $bargainModel->whereIn('id', $bargain_ids)
                ->update(['status' => 0]);


        } catch (\Exception $e) {
            Log::write('结束砍价活动失败:'.$e->getMessage());
        }
    }
}