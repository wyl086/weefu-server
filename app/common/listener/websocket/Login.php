<?php

namespace app\common\listener\websocket;


use app\common\enum\KefuEnum;
use app\common\model\kefu\Kefu;
use app\common\model\user\User;
use app\common\websocket\Response;

/**
 * 登录事件
 * Class Login
 * @package app\common\listener\websocket
 */
class Login
{

    protected $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }


    public function handle(array $event)
    {
        ['type' => $type, 'token' => $token, 'client' => $client] = $event;

        if (empty($type) || empty($token) || empty($client)) {
            return $this->response->error('参数缺失');
        }

        if (!in_array($type, ['user', 'kefu'])) {
            return $this->response->error('类型错误');
        }

        if ('user' == $type) {
            // 查询用户信息
            $user = (new User())->alias('u')
                ->field('u.id, u.sn, u.nickname, u.avatar, u.mobile, u.level, u.group_id, u.sex, u.disable, u.del')
                ->join('session s', 'u.id = s.user_id')
                ->where(['s.token' => $token, 's.client' => $client])
                ->findOrEmpty();
        } else {
            // 查询客服信息
            $user = (new Kefu())->alias('k')
                ->field('k.*')
                ->join('kefu_session s', 'k.id = s.kefu_id')
                ->where(['s.token' => $token, 'client' => $client])
                ->findOrEmpty();
        }

        if ($user->isEmpty() || $user['del'] || $user['disable']) {
            return $this->response->error('用户信息不存在或用户已被禁用');
        }

        $user['client'] = $client;

        return $this->response->success('', $user->toArray());
    }

}