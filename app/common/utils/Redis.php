<?php

namespace app\common\utils;

use think\facade\Cache;

class Redis
{
    public $redis = null;


    public function __construct()
    {
        $this->redis = Cache::store('redis')->handler();
    }


    /**
     * @notes 设置缓存
     * @param $key
     * @param $val
     * @param null $time
     * @return false
     * @author 段誉
     * @date 2021/12/20 12:13
     */
    public function set($key, $val, $time = null)
    {
        if (empty($key)) {
            return false;
        }
        if (is_array($val)) {
            $val = json_encode($val, JSON_UNESCAPED_UNICODE);
        }
        return $this->redis->set($key, $val, $time);
    }


    /**
     * @notes 获取缓存
     * @param $key
     * @return false
     * @author 段誉
     * @date 2021/12/20 12:14
     */
    public function get($key)
    {
        if (empty($key)) {
            return false;
        }
        return $this->redis->get($key);
    }


    /**
     * @notes 删除指定
     * @param $key
     * @return mixed
     * @author 段誉
     * @date 2021/12/20 12:02
     */
    public function del($key)
    {
        return $this->redis->del($key);
    }


    /**
     * @notes 清空
     * @return mixed
     * @author 段誉
     * @date 2021/12/20 12:02
     */
    public function flashAll()
    {
        return $this->redis->flushAll();
    }


    /**
     * @notes 获取集合
     * @param $key
     * @return mixed
     * @author 段誉
     * @date 2021/12/20 12:11
     */
    public function sMembers($key)
    {
        return $this->redis->sMembers($key);
    }


    /**
     * @notes 设置缓存时间
     * @param $key
     * @param $ttl
     * @return mixed
     * @author 段誉
     * @date 2021/12/20 12:02
     */
    public function expire($key, $ttl)
    {
        return $this->redis->expire($key, $ttl);
    }


    /**
     * @notes 向集合添加成员
     * @param $key
     * @param $val
     * @return mixed
     * @author 段誉
     * @date 2021/12/20 12:04
     */
    public function sadd($key, $val)
    {
        return $this->redis->sAdd($key, $val);
    }


    /**
     * @notes 移除集合成员
     * @param $key
     * @param $val
     * @return mixed
     * @author 段誉
     * @date 2021/12/20 12:04
     */
    public function srem($key, $val)
    {
        return $this->redis->sRem($key, $val);
    }

    /**
     * @notes 对象转数组
     * @param $key
     * @return array|false
     * @author 段誉
     * @date 2021/12/20 12:03
     */
    public function getSmembersArray($key)
    {
        $res = $this->sMembers($key);
        if (is_object($res)) {
            return (array)$res;
        }
        return $res;
    }


    /**
     * @notes 相似keys
     * @param $prefix
     * @return mixed
     * @author 段誉
     * @date 2021/12/20 12:02
     */
    public function keys($prefix)
    {
        return $this->redis->keys($prefix.'*');
    }

}