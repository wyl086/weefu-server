<?php

namespace app\kefuapi\validate;

use app\common\basics\Validate;
use app\common\enum\KefuEnum;
use app\common\logic\ChatLogic;
use app\common\model\Admin;
use app\common\model\shop\ShopAdmin;
use think\facade\Cache;

/**
 * 客服登录验证
 * Class LoginValidate
 * @package app\shopapi\validate
 */
class LoginValidate extends Validate
{
    protected $rule = [
        'type' => 'require',
        'client' => 'require',
        'account' => 'require',
        'password' => 'require|checkPassword',
    ];

    protected $message = [
        'type.require' => '参数缺失',
        'account.require' => '请输入账号',
        'password.require' => '请输入密码',
        'password.checkPassword' => '账号或密码错误',
        'client.require' => '请输入客户端'
    ];


    /**
     * @notes 校验密码
     * @param $password
     * @param $other
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2021/11/9 16:02
     */
    protected function checkPassword($password, $other, $data)
    {
        $field = 'k.id, k.shop_id, a.account, a.salt, a.password,
         k.disable as kefu_disable, a.disable as admin_disable';

        $condition = ['a.account' => $data['account'], 'k.del' => 0, 'a.del' => 0];

        if (KefuEnum::TYPE_SHOP == $data['type']) {
            $isPlatform = false;
            $chat = (new ShopAdmin())->alias('a')
                ->field($field)
                ->join('kefu k', 'a.id = k.admin_id and a.shop_id = k.shop_id')
                ->where($condition)
                ->findOrEmpty();
        } else {
            $isPlatform = true;
            $chat = (new Admin())->alias('a')
                ->field($field)
                ->join('kefu k', 'k.admin_id = a.id')
                ->where(['k.shop_id' => 0])
                ->where($condition)
                ->findOrEmpty();
        }

        if (false === $this->safe(false, $isPlatform)) {
            $this->message['password.password'] .= ':多次输入错误';
            return false;
        }

        if ($chat->isEmpty()) {
            $this->safe(true, $isPlatform);
            return '账号不存在';
        }

        if ($chat['kefu_disable'] || $chat['admin_disable']) {
            return '账号被禁用';
        }

        $password = generatePassword($password, $chat['salt']);
        if ($password != $chat['password']) {
            $this->safe(true, $isPlatform);
            return false;
        }

        // 检查后台配置是否开启，当前缓存驱动是否redis
        if (false === ChatLogic::checkConfig($chat['shop_id'])) {
            return ChatLogic::getError() ?: '请联系管理员设置后台配置';
        }

        return true;
    }

    /**
     * 连续30分钟内15次输错密码，无法登录
     * @param bool $add
     * @return bool
     */
    protected function safe($status = false, $isPlatform = false)
    {
        if ($isPlatform) {
            $errorCount = 'platform_kefu_error_count' . request()->ip();
        } else {
            $errorCount = 'shop_kefu_error_count' . request()->ip();
        }

        if ($status) {
            $loginErrorCount = Cache::get($errorCount);
            $loginErrorCount++;
            Cache::tag('kefu_login_error_count')->set($errorCount, $loginErrorCount, 1800);
        }

        $count = Cache::get($errorCount);

        if (!empty($count) && $count >= 15) {
            return false;
        }
        return true;
    }
}