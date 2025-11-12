<?php
// +----------------------------------------------------------------------
// | Multshop多商户商城系统
// +----------------------------------------------------------------------
// | author: multshopTeam-段誉
// +----------------------------------------------------------------------


namespace app\common\websocket;


use app\common\enum\ChatMsgEnum;
use app\common\utils\Redis;
use Swoole\Server;
use Swoole\Websocket\Frame;
use think\App;
use think\Event;
use think\Request;
use think\swoole\Websocket;
use think\swoole\websocket\Room;

class Handler extends Websocket
{
    protected $server;

    protected $room;

    protected $parser;

    protected $cache;

    protected $prefix;

    public function __construct(App $app, Server $server, Room $room, Event $event, Parser $parser, Redis $redis)
    {
        $this->server = $server;
        $this->room = $room;
        $this->parser = $parser;
        $this->cache = $redis;
        $this->prefix = config('default.websocket_prefix');
        parent::__construct($app, $server, $room, $event);
    }


    /**
     * @notes open
     * @param int $fd
     * @param Request $request
     * @return bool|mixed|void
     * @author 段誉
     * @date 2021/12/15 19:13
     */
    public function onOpen($fd, Request $request)
    {
        $token = $request->get('token/s'); // 客服
        $type = $request->get('type/s'); // user, kefu
        $client = $request->get('client/d');
        $shop_id = $request->get('shop_id/d', 0); //当前对话的商家id

        try {
            $user = $this->triggerEvent('login', ['client' => $client, 'token' => $token, 'type' => $type]);

            if ($user['code'] == 20001 || empty($user['data']['id'])) {
                throw new \Exception(empty($user['msg']) ? "未知错误" : $user['msg']);
            }
        } catch (\Throwable $e) {
            echo 'onOpen错误：' . $e->getMessage();
            return $this->server->close($fd);
        }

        // 登录者绑定fd
        $this->bindFd($type, $user['data'], $fd, $shop_id);

        $this->ping($fd);

        return $this->pushData($fd, 'login', [
            'msg' => '连接成功',
            'msg_type' => ChatMsgEnum::TYPE_TEXT
        ]);
    }


    /**
     * @notes onMessage
     * @param Frame $frame
     * @return bool|mixed|void
     * @author 段誉
     * @date 2021/12/20 10:53
     */
    public function onMessage(Frame $frame)
    {
        $param = $this->parser->decode($frame->data);

        try {
            // 回应ping
            if ('ping' === $param['event']) {
                return $this->ping($frame->fd);
            }

            $param['handle'] = $this;
            $param['fd'] = $frame->fd;

            return $this->triggerEvent($param['event'], $param);

        } catch (\Throwable $e) {
            echo $e->getMessage();
            return $this->pushData($frame->fd, 'error', [
                'msg' => $e->getMessage(),
                'msg_type' => ChatMsgEnum::TYPE_TEXT
            ]);
        }
    }



    /**
     * @notes onClose
     * @param int $fd
     * @param int $reactorId
     * @author 段誉
     * @date 2021/12/15 19:03
     */
    public function onClose($fd, $reactorId)
    {
        $this->triggerEvent('close', ['handle' => $this, 'fd' => $fd]);
        $this->removeBind($fd);
        $this->server->close($fd);
    }


    /**
     * @notes 触发事件
     * @param string $event
     * @param array $data
     * @return mixed
     * @author 段誉
     * @date 2021/12/15 19:03
     */
    public function triggerEvent(string $event, array $data)
    {
        return $this->event->until('swoole.websocket.' . $event, $data);
    }



    /**
     * @notes 登录者的id绑定fd
     * @param $type
     * @param $user
     * @param $fd
     * @param $shop_id
     * @author 段誉
     * @date 2021/12/15 19:02
     */
    public function bindFd($type, $user, $fd, $shop_id)
    {
        $uid = $user['id'];

        //检查当前用户当前终端是否已存在
//        $check = $this->getFdByUid($uid, $type);
//        if (!empty($check)) {
//            foreach ($check as $item) {
//                $info = $this->getDataByFd($item);
//                $client = $info['client'] ?? 0;
//                if ($client == $user['client'] && $fd != $item) {
//                    $this->del($this->prefix . 'fd_' . $item);
//                    $this->cache->srem($this->prefix . $type . '_' . $uid, $item);
//                }
//            }
//        }

        // socket_fd_{fd} => ['uid' => {uid}, 'type' => {type}]
        // 以fd为键缓存当前fd的信息
        $fdKey = $this->prefix . 'fd_' . $fd;
        $fdData = [
            'uid' => $uid,
            'type' => $type,
            'nickname' => $user['nickname'],
            'avatar' => $user['avatar'],
            'client' => $user['client'],
            'shop_id' => $shop_id
        ];
        $this->cache->set($fdKey, json_encode($fdData, true));

        // socket_user_1(user_id) => {fd} 用户userid为1 的 fd
        // socket_kefu_2(kefu_id) => {fd} 客服kefu_id为2 的 fd
        $uidKey = $this->prefix . $type . '_' . $uid;
        $this->cache->sadd($uidKey, $fd);

        // socket_user => {fd} 在线用户的所有fd
        if ($type == 'kefu') {
            $groupKey = $this->prefix . 'shop_' . $shop_id . '_kefu';
        } else {
            $groupKey = $this->prefix . 'user';
        }
        $this->cache->sadd($groupKey, $uid);
    }


    /**
     * @notes 移除绑定
     * @param $fd
     * @author 段誉
     * @date 2021/12/15 19:02
     */
    public function removeBind($fd)
    {
        $data = $this->getDataByFd($fd);
        if ($data) {
            $key = $this->prefix . 'user';
            if($data['type'] == 'kefu') {
                $key = $this->prefix . 'shop_'. $data['shop_id'] . '_kefu';
            }
            $this->cache->srem($key, $data['uid']); // socket_user => 11
            $this->cache->srem($this->prefix . $data['type'] . '_' . $data['uid'], $fd); // socket_user_uid => fd
        }
        $this->cache->del($this->prefix . 'fd_' . $fd);
    }



    /**
     * @notes 通过登录id和登录类型获取对应的fd
     * @param $uid
     * @param $type
     * @return bool
     * @author 段誉
     * @date 2021/12/15 19:02
     */
    public function getFdByUid($uid, $type)
    {
        $key = $this->prefix . $type . '_' . $uid;
        return $this->cache->sMembers($key);
    }



    /**
     * @notes 根据fd获取登录的id和登录类型
     * @param $fd
     * @return mixed|string
     * @author 段誉
     * @date 2021/12/15 19:02
     */
    public function getDataByFd($fd)
    {
        $key = $this->prefix . 'fd_' . $fd;
        $result = $this->cache->get($key);
        if (!empty($result)) {
            $result = json_decode($result, true);
        }
        return $result;
    }


    /**
     * @notes ping
     * @param $fd
     * @return bool
     * @author 段誉
     * @date 2021/12/20 15:19
     */
    public function ping($fd)
    {
        $data = $this->getDataByFd($fd);
        if (!empty($data)) {
            return $this->pushData($fd, 'ping', ['client_time' => time()]);
        }
        return true;
    }




    /**
     * @notes 推送数据
     * @param $fd
     * @param $event
     * @param $data
     * @return bool
     * @author 段誉
     * @date 2021/12/15 19:02
     */
    public function pushData($fd, $event, $data)
    {
        $data = $this->parser->encode($event, $data);

        // fd非数组时转为数组
        if (!is_array($fd)) {
            $fd = [$fd];
        }

        // 向fd发送消息
        foreach ($fd as $item) {
            if ($this->server->exist($item)) {
                $this->server->push($item, $data);
            }
        }
        return true;
    }



    /**
     * @notes 在线fd
     * @param $fd
     * @return array
     * @author 段誉
     * @date 2021/12/17 18:19
     */
    public function onlineFd($fd)
    {
        $result = [];

        if (empty($fd)) {
            return $result;
        }

        if (!is_array($fd)) {
            $fd = [$fd];
        }

        foreach ($fd as $item) {

//            $fd_data = $this->getDataByFd($fd);
//            $fd_shop_id = $fd_data['shop_id'] ?? 0;
//            if ($fd_shop_id != $shop_id) {
//                continue;
//            }

            if ($this->server->exist($item)) {
                $result[] = $item;
            }
        }

        return $result;
    }

}
