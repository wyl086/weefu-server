<?php

namespace app\common\listener\websocket;


use app\common\model\kefu\ChatRelation;

/**
 * 已读状态
 * Class Read
 * @package app\common\listener\websocket
 */
class Read
{
    public function handle($params)
    {
        $user_id = $params['data']['user_id'] ?? 0;
        $shop_id = $params['data']['shop_id'] ?? 0;

        $relation = ChatRelation::where(['user_id' => $user_id, 'shop_id' => $shop_id])->findOrEmpty();

        if (!$relation->isEmpty()) {
            ChatRelation::update(['is_read' => 1], ['id' => $relation['id']]);
        }
    }
}