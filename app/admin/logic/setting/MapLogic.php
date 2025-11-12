<?php

namespace app\admin\logic\setting;

use app\common\basics\Logic;
use app\common\server\ConfigServer;


class MapLogic extends Logic
{
    /**
     * @notes 设置地图
     * @return array
     * @author 段誉
     * @date 2022/1/17 10:31
     */
    public static function getConfig()
    {
        $config = [
            'tx_map_key' => ConfigServer::get('map', 'tx_map_key'),
            'is_open_nearby' => ConfigServer::get('map', 'is_open_nearby',0),
        ];
        return $config;
    }

    /**
     * @notes 设置配置
     * @param $post
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/1/17 10:31
     */
    public static function setConfig($post)
    {
        ConfigServer::set('map', 'tx_map_key', $post['tx_map_key']);
        ConfigServer::set('map', 'is_open_nearby', $post['is_open_nearby']);
    }


}