<?php

namespace app\common\listener\websocket;


use app\common\enum\ChatMsgEnum;
use app\common\enum\ChatRecordEnum;
use app\common\logic\ChatLogic;
use app\common\model\goods\Goods;
use app\common\model\kefu\ChatRecord;
use app\common\websocket\Response;

/**
 * 对话事件
 * Class Chat
 * @package app\common\listener\websocket
 */
class Chat
{

    protected $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }


    public function handle($params)
    {
        $from_fd = $params['fd'];
        $request_data = $params['data'];
        $handleClass = $params['handle'];

        $from_data = $handleClass->getDataByFd($from_fd);

        $to_fd = $handleClass->getFdByUid($request_data['to_id'], $request_data['to_type']);

        // 当前发送人信息是否存在
        if (empty($from_data['type']) || empty($from_data['uid'])) {
            return $handleClass->pushData($from_fd, 'error', $this->response->formatSendError('聊天用户不存在或不在线'));
        }

        // 验证后台配置是否开启
        $check = $this->checkConfig($from_data['shop_id']);
        if (true !== $check) {
            return $handleClass->pushData($from_fd, 'error', $this->response->formatSendError($check));
        }

        $toName = '客服';
        if ('kefu' == $from_data['type']) {
            $toName = '用户';
        }

        // 接收的人不存在
        if (empty($to_fd)) {
            return $handleClass->pushData($from_fd, 'error', $this->response->formatSendError($toName . '不存在或不在线'));
        }

        // 接收人不在线
        $online_fd = $handleClass->onlineFd($to_fd);
        if (empty($online_fd)) {
            return $handleClass->pushData($from_fd, 'error', $this->response->formatSendError($toName . '不在线'));
        }

        // 添加聊天记录
        $record = $this->insertRecord([
            'shop_id' => $from_data['shop_id'],
            'from_id' => $from_data['uid'],
            'from_type' => $from_data['type'],
            'to_id' => $request_data['to_id'],
            'to_type' => $request_data['to_type'],
            'msg' => $request_data['msg'],
            'msg_type' => $request_data['msg_type'],
        ]);

        $record['from_avatar'] = $from_data['avatar'];
        $record['from_nickname'] = $from_data['nickname'];
        $record['create_time_stamp'] = $record['create_time'];
        $record['create_time'] = date('Y-m-d H:i:s', $record['create_time']);
        $record['update_time'] = date('Y-m-d H:i:s', $record['update_time']);
        $record['goods'] = [];
        if ($request_data['msg_type'] == ChatMsgEnum::TYPE_GOODS) {
            $record['goods'] = json_decode($record['msg'], true);
        }

        // 更新聊天关系记录
        $this->bindRelation([
            'shop_id' => $from_data['shop_id'],
            'from_id' => $from_data['uid'],
            'from_type' => $from_data['type'],
            'to_id' => $request_data['to_id'],
            'to_type' => $request_data['to_type'],
            'msg' => $request_data['msg'],
            'msg_type' => $request_data['msg_type'],
            'client' => $from_data['client']
        ]);

        if (!empty($record)) {
            $handleClass->pushData($from_fd, 'chat', $record);
            return $handleClass->pushData($to_fd, 'chat', $record);
        }
    }


    /**
     * @notes 检查后台配置
     * @param $shop_id
     * @return array|bool|string
     * @author 段誉
     * @date 2021/12/20 18:29
     */
    public function checkConfig($shop_id)
    {
        if (false === ChatLogic::checkConfig($shop_id)) {
            return ChatLogic::getError() ?: '请联系管理员设置后台配置';
        }
        return true;
    }


    /**
     * @notes 增加聊天记录
     * @param $data
     * @return array
     * @author 段誉
     * @date 2021/12/17 14:33
     */
    public function insertRecord($data)
    {
        switch ($data['msg_type']) {
            case ChatMsgEnum::TYPE_IMG:
                $msg = $data['msg'];
                break;

            case ChatMsgEnum::TYPE_GOODS:
                $goods = Goods::where(['id' => $data['msg']])->field([
                    'id', 'image', 'min_price', 'name'
                ])->findOrEmpty();

                $msg = json_encode([
                    'id' => $goods['id'] ?? 0,
                    'image' => $goods->getData('image') ?? '',
                    'min_price' => $goods['min_price'] ?? 0,
                    'name' => $goods['name'] ?? '',
                ], true);
                break;

            default:
                $msg = htmlspecialchars($data['msg']);
        }

        $result = ChatRecord::create([
            'shop_id' => $data['shop_id'],
            'from_id' => $data['from_id'],
            'from_type' => $data['from_type'],
            'to_id' => $data['to_id'],
            'to_type' => $data['to_type'],
            'msg' => $msg,
            'msg_type' => $data['msg_type'],
            'is_read' => $data['is_read'] ?? 1,
            'type' => ChatRecordEnum::TYPE_NORMAL,
            'create_time' => time(),
        ]);

        return $result->toArray();
    }


    /**
     * @notes 绑定关系
     * @param $data
     * @author 段誉
     * @date 2021/12/17 14:33
     */
    public function bindRelation($data)
    {
        if ($data['to_type'] == 'kefu') {
            $kefu_id = $data['to_id'];
            $user_id = $data['from_id'];
        } else {
            $kefu_id = $data['from_id'];
            $user_id = $data['to_id'];
        }

        $is_read = 1;
        if ($data['from_type'] == 'user') {
            $is_read = 0;
        }

        ChatLogic::bindRelation($user_id, $kefu_id, $data['shop_id'], [
            'client' => $data['client'],
            'msg' => $data['msg'],
            'msg_type' => $data['msg_type'],
        ], $is_read);
    }


}