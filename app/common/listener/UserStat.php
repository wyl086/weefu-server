<?php
namespace app\common\listener;

use think\Request;
use think\facade\Db;

class UserStat
{

    public function handle(Request $request)
    {
        try{
            $ip = $request->ip();
            $record = Db::name('stat')
                ->where('ip', '=', $ip)
                ->whereTime('create_time', 'today')
                ->find();

            if ($record) {
                Db::name('stat')
                    ->where('id', $record['id'])
                    ->inc('count',1)
                    ->update();
            } else {
                $data = [
                    'ip'          => $ip,
                    'count'       => 1,
                    'create_time' => time()
                ];
                Db::name('stat')->insert($data);
            }

        } catch (\Exception $e) {

        }
    }

    
}