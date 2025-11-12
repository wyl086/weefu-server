<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\api\cache;


use app\common\cache\CacheBase;
use think\facade\Db;
use think\facade\Config;

class TokenCache extends CacheBase
{

    public function setTag()
    {
        return 'token';
    }

    /**
     * 子类实现查出数据
     * @return mixed
     */
    public function setData()
    {
        //刷新token过期时间
        $time = time();
        $expire_time = $time + Config::get('project.token_expire_time');
        Db::name('session')
            ->where(['token' => $this->extend['token']])
            ->update(['update_time' => $time, 'expire_time' => $expire_time]);

        //返回用户信息
        $user_info = Db::name('user')->alias('u')
            ->join('session s', 'u.id=s.user_id')
            ->where(['s.token' => $this->extend['token']])
            ->field('u.*,s.token,s.client')
            ->find();
        return $user_info;
    }
}