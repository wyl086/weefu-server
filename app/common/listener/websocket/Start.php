<?php

namespace app\common\listener\websocket;



use app\common\logic\ChatLogic;
use app\common\utils\Redis;
use think\facade\Log;

/**
 * 启动事件
 * Class Start
 * @package app\common\listener\websocket
 */
class Start
{
    public function handle($params)
    {
        try{
            $prefix = ChatLogic::getChatPrefix();
            if (empty($prefix)) {
                return true;
            }
            $redis = new Redis();
            $redis->del($redis->keys($prefix));
            return true;
        } catch (\Exception $e) {
            Log::write('swoole启动异常:'.$e->getMessage());
        }
    }
}