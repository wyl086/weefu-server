<?php

namespace app\common\listener;

use app\common\logic\MessageNoticeLogic;

/**
 * 消息通知
 * Class Notice
 * @package app\common\listener
 */
class Notice
{
    public function handle($params)
    {
        try {
            if (empty($params['scene'])) {
                throw new \Exception('参数缺失');
            }
            $user_id = $params['params']['user_id'] ?? '';

            //找到当前场景的通知设置记录,发送通知
            $res = MessageNoticeLogic::noticeByScene($user_id, $params);
            if (false === $res) {
                throw new \Exception(MessageNoticeLogic::getError());
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}