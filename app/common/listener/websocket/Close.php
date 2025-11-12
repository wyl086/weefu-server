<?php

namespace app\common\listener\websocket;



use app\common\model\kefu\ChatRelation;

/**
 * 关闭事件
 * Class Close
 * @package app\common\listener\websocket
 */
class Close
{
    public function handle($params)
    {
        $fd = $params['fd'];
        $handleClass = $params['handle'];
        // 当前fd信息
        $data = $handleClass->getDataByFd($fd);

        if (!empty($data) && $data['type'] == 'user') {
            $relation = ChatRelation::where([
                'user_id' => $data['uid'],
                'shop_id' => $data['shop_id']
            ])->findOrEmpty();

            $kefu_fd = $handleClass->getFdByUid($relation['kefu_id'] ?? 0, 'kefu');

            if (!empty($kefu_fd)) {
                $relation['online'] = 0;
                $handleClass->pushData($kefu_fd, 'user_online', $relation);
            }
        }
    }
}