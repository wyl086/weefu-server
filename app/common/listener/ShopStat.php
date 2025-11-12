<?php
namespace app\common\listener;

use think\Request;
use think\facade\Db;

class ShopStat
{

    public function handle($shop_id,Request $request)
    {
        try{
            $ip = $request->ip();
            $record = Db::name('shop_stat')
                ->where([
                    ['shop_id', '=', $shop_id],
                    ['ip', '=', $ip]
                ])
                ->whereTime('create_time', 'today')
                ->find();
            if ($record) {
                Db::name('shop_stat')
                    ->where('id', $record['id'])
                    ->inc('count',1)
                    ->update();

            } else {
                $data = [
                    'shop_id'     => $shop_id,
                    'ip'          => $ip,
                    'count'       => 1,
                    'create_time' => time()
                ];
                Db::name('shop_stat')->insert($data);
                    
                $where = [
                    'del' => 0,
                    'id' => $shop_id
                ];
                Db::name('shop')
                    ->where($where)
                    ->inc('visited_num',1)
                    ->update();
            }

        } catch (\Exception $e) {

        }
    }

    
}