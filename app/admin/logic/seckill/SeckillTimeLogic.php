<?php
namespace app\admin\logic\seckill;

use app\common\basics\Logic;
use app\common\model\seckill\SeckillTime;
use app\common\model\seckill\SeckillGoods;
use think\facade\Db;

class SeckillTimeLogic extends Logic
{
    public static function addTime($post){
        try{
            $post['create_time'] = time();
            $post['update_time'] = time();
            SeckillTime::create($post);
            return true;
        }catch(\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    public static function timeList($get){
        $where[] = ['del','=',0];
        $count = SeckillTime::where($where)->count();
        $list = SeckillTime::where($where)
            ->order('start_time asc')
            ->page($get['page'], $get['limit'])
            ->select()
            ->toArray();
        foreach ($list as &$item){
            $item['time'] = $item['start_time'].' ~ '.$item['end_time'];
        }
        return ['count' => $count, 'list' => $list];
    }

    public static function getTime($id){
        $seckillTime = SeckillTime::where(['del'=>0, 'id'=>$id])->findOrEmpty();
        if($seckillTime->isEmpty()) {
            return [];
        }
        return $seckillTime->toArray();
    }

    public static function editTime($post){
        try{
            $post['update_time'] = time();
            SeckillTime::where(['id'=>$post['id']])->update($post);
            return true;
        }catch(\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    public static function delTime($id){
        Db::startTrans();
        try{
            $update_data = [
                'update_time'   => time(),
                'del'           => 1,
            ];
            SeckillTime::where(['id'=>$id])->update($update_data);
            SeckillGoods::where(['del'=>0, 'seckill_id'=>$id])->update($update_data);
            Db::commit();
            return true;
        }catch(\Exception $e) {
            self::$error = $e->getMessage();
            Db::rollback();
            return false;
        }
    }
}