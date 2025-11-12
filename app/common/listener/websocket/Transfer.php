<?php

namespace app\common\listener\websocket;


use app\common\enum\ChatMsgEnum;
use app\common\enum\ChatRecordEnum;
use app\common\model\kefu\ChatRecord;
use app\common\model\kefu\ChatRelation;
use app\common\model\kefu\Kefu;
use app\common\websocket\Response;

/**
 * 转接事件
 * Class Transfer
 * @package app\common\listener\websocket
 */
class Transfer
{

    protected $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }


    public function handle($params)
    {
        $old_kefu_fd = $params['fd'];
        $request_data = $params['data'];
        $handleClass = $params['handle'];

        // 当前客服
        $old_kefu = $handleClass->getDataByFd($old_kefu_fd);
        $old_kefu_id = $old_kefu['uid'] ?? 0;
        $shop_id = $old_kefu['shop_id'];

        // 当前用户
        $user_fd = $handleClass->getFdByUid($request_data['user_id'], 'user');
        $user_id = $request_data['user_id'] ?? 0;

        // 新客服的 fd
        $new_kefu_fd = $handleClass->getFdByUid($request_data['kefu_id'], 'kefu');
        $new_kefu_id = $request_data['kefu_id'] ?? 0;

        $user_connect = $handleClass->onlineFd($user_fd);
        $kefu_connect = $handleClass->onlineFd($new_kefu_fd);


        if (empty($user_id) || empty($user_fd) || empty($user_connect)) {
            return $handleClass->pushData($old_kefu_fd, 'error', $this->response->formatSendError('该用户不在线'));
        }

        if (empty($new_kefu_id) || empty($new_kefu_fd) || empty($kefu_connect)) {
            return $handleClass->pushData($old_kefu_fd, 'error', $this->response->formatSendError('该客服不在线'));
        }

        $relation = ChatRelation::where(['user_id' => $user_id, 'shop_id' => $shop_id])->findOrEmpty();

        if (empty($relation) || $relation['kefu_id'] != $old_kefu_id) {
            return $handleClass->pushData($old_kefu_fd, 'error', $this->response->formatSendError('转接失败'));
        }

        $new_kefu = Kefu::where(['id' => $new_kefu_id])->findOrEmpty();

        $record = [];

        if (!$new_kefu->isEmpty()) {
            // 增加通知记录-主要告知用户
            $record = ChatRecord::create([
                'shop_id' => $new_kefu['shop_id'],
                'from_id' => $new_kefu['id'],
                'from_type' => 'kefu',
                'to_id' => $user_id,
                'to_type' => 'user',
                'msg' => '客服(' . $new_kefu['nickname'] . ')为您服务',
                'msg_type' => ChatMsgEnum::TYPE_TEXT,
                'is_read' => 1,
                'type' => ChatRecordEnum::TYPE_NOTICE,
                'create_time' => time(),
            ])->toArray();

            $record['goods'] = [];
            $record['from_avatar'] = $new_kefu['avatar'];
            $record['from_nickname'] = $new_kefu['nickname'];
            $record['create_time_stamp'] = $record['create_time'];
            $record['create_time'] = date('Y-m-d H:i:s', $record['create_time']);

            // 更新关系
            ChatRelation::update([
                'kefu_id' => $new_kefu_id,
                'msg' => '',
                'msg_type' => ChatMsgEnum::TYPE_TEXT,
                'update_time' => time()
            ], ['id' => $relation['id']]);
        }

        if (!empty($record)) {
            // 用于前端显示 ‘xxx为你服务’
            $handleClass->pushData($user_fd, 'chat', $record);
        }

        // 通知用户,新客服id 头像昵称
        $handleClass->pushData($user_fd, 'transfer', [
            'avater' => $new_kefu['avater'],
            'nickname' => $new_kefu['nickname'] ?? '客服',
            'id' => $new_kefu['id'] ?? 0,
        ]);

        // 通知原客服转接成功
        $handleClass->pushData($old_kefu_fd, 'transfer', [
            'status' => 'send_success',
        ]);


        $relation['online'] = 1;
        $relation['msg'] = '';
        $relation['msg_type'] = ChatMsgEnum::TYPE_TEXT;
        $relation['update_time'] = '';
        // 通知新客服转接成功
        $handleClass->pushData($new_kefu_fd, 'transfer', [
            'status' => 'get_success',
            'user' => $relation,
        ]);
    }
}