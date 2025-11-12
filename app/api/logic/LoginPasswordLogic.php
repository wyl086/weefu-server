<?php

namespace app\api\logic;

use app\common\basics\Logic;
use app\common\model\Client_;
use app\common\model\user\User;

class LoginPasswordLogic extends Logic
{

    /**
     * Notes: 忘记密码(找回密码)
     * @param $post
     * @author 段誉(2021/6/23 17:01)
     * @return array|bool
     */
    public static function forget($post)
    {
        try {
            $client = self::getClient($post);
            $account = User::where(['mobile' => $post['mobile'], 'del' => 0])
                ->find();
            if (!$account) {
                throw new \Exception('账号不存在');
            }
            //更新密码
            $password = create_password($post['password'], $account['salt']);//生成密码
            if ($account['password'] == $password) {
                throw new \Exception('密码未改动');
            }
            $data = [
                'password' => $password,
                'update_time' => time(),
            ];
            User::where(['id' => $account['id'], 'del' => 0])
                ->update($data);

            $token = LoginLogic::createSession($account['id'], $client);
            return ['token' => $token];
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    /***
     * @param $post
     * @return int
     */
    public static function getClient($post)
    {
        $client = $post['client'] ?? Client_::mnp;
        $client_arr = array_keys(Client_::getClient(true));
        if (in_array($client, $client_arr)) {
            return $client;
        }
        return Client_::mnp;
    }
}