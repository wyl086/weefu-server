<?php

namespace app\common\listener\websocket;


use app\common\logic\ChatLogic;
use app\common\model\kefu\ChatRelation;

/**
 * 用户上线
 * Class UserOnline
 * @package app\common\listener\websocket
 */
class UserOnline
{
    public function handle($params)
    {
        $handleClass = $params['handle'];
        $fd = $params['fd'];

        // 当前用户信息
        $user = $handleClass->getDataByFd($fd);

        // 接收人信息
        $to_id = $params['data']['kefu_id'] ?? 0;

        if (empty($user['type'] || $user['type'] != 'user' || empty($to_id))) {
            return true;
        }

        // 是否有绑定关系
        $relation_id = ChatLogic::bindRelation($user['uid'], $to_id, $user['shop_id'], [
            'client' => $user['client'],
        ], 1);

        $relation = ChatRelation::where(['id' => $relation_id])->findOrEmpty();

        $to_fd = $handleClass->getFdByUid($to_id, 'kefu');
        if (!empty($to_fd)) {
            $relation['online'] = 1;
            return $handleClass->pushData($to_fd, 'user_online', $relation);
        }
        return true;
    }
}