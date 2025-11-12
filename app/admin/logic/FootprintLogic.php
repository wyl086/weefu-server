<?php


namespace app\admin\logic;


use app\common\basics\Logic;
use app\common\model\Footprint;
use app\common\server\ConfigServer;
use Exception;

class FootprintLogic extends Logic
{
    /**
     * @Notes: 气泡场景列表
     * @Author: 张无忌
     * @return array
     */
    public static function lists()
    {
        try {
            $footprintModel = new Footprint();
            return $footprintModel->select()->toArray();
        } catch (Exception $e) {
            return ['error'=>$e->getMessage()];
        }
    }

    /**
     * @Notes: 气泡详细
     * @Author: 张无忌
     * @param $id
     * @return array
     */
    public static function detail($id)
    {
        $footprintModel = new Footprint();
        return $footprintModel->findOrEmpty((int)$id)->toArray();
    }

    /**
     * @Notes: 编辑足迹气泡
     * @Author: 张无忌
     * @param $post
     * @return bool
     */
    public static function edit($post)
    {
        try {
            $footprintModel = new Footprint();
            $footprintModel->where(['id' => (int)$post['id']])
                ->update(['status' => $post['status']]);

            return true;
        } catch (Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @Notes: 足迹设置
     * @Author: 张无忌
     * @param $post
     * @return bool
     */
    public static function set($post)
    {
        try {
            ConfigServer::set('footprint', 'footprint_duration', $post['duration']);
            ConfigServer::set('footprint', 'footprint_status', $post['status']);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}