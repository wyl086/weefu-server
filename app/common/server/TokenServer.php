<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\common\server;


use think\Exception;
use think\facade\Cache;

/**
 * Token 服务类
 * Class TokenServer
 * @package app\common\server
 */
class TokenServer
{
    /**
     * Notes: 生成令牌
     * @author FZR(2021/1/28 10:37)
     * @param string $tokenSalt (加密盐)
     * @return string
     */
    public static function generateToken($tokenSalt)
    {
        $randChar = getRandChar(32);
        $timestamp = $_SERVER['REQUEST_TIME_FLOAT'];
        return md5($randChar . $timestamp . $tokenSalt);
    }

    /**
     * Notes: 根据Key获取当前Token中变量值
     * @param string $key (键, 必填, 用于校验数据是否正常获取)
     * @param bool $isWhole (是否获取全部)
     * @return mixed
     * @throws Exception
     * @author FZR(2021/1/28 10:42)
     */
    public static function getCurrentTokenVar($key, $isWhole=false)
    {
        $token = request()->header('token');
        $vars = Cache::get($token);

        if (empty($token) || !$token || !$vars) {

            throw new Exception('Token已过期或无效Token');

        } else {
            // 如果不是数组则转成数组
            if (!is_array($vars)) {
                $vars = json_decode($vars, true);
            }
            // 判断Key是否存在数组中
            if (array_key_exists($key, $vars)) {
                // 重置缓存时间
                Cache::set($token, json_encode($vars, JSON_UNESCAPED_UNICODE), 7200);
                // 返回缓存中的数据
                return $isWhole ? $vars : $vars[$key];
            } else {
                throw new Exception('尝试获取的Token变量并不存在');
            }
        }
    }
}