<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | gitee下载：https://gitee.com/multshop_gitee
// | github下载：https://github.com/multshop-github
// | 访问官网：https://www.multshop.cn
// | 访问社区：https://home.multshop.cn
// | 访问手册：http://doc.multshop.cn
// | 微信公众号：multshop技术社区
// | multshop系列产品在gitee、github等公开渠道开源版本可免费商用，未经许可不能去除前后端官方版权标识
// |  multshop系列产品收费版本务必购买商业授权，购买去版权授权后，方可去除前后端官方版权标识
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\shopapi\logic;

use app\common\basics\Logic;
use app\common\model\shop\ShopAdmin;
use app\common\model\ShopSession;
use app\common\model\ShopSession as SessionModel;
use app\common\server\UrlServer;
use think\facade\Config;
use think\facade\Cache;

/**
 * 商家移动端管理员登录逻辑
 * Class LoginLogic
 * @package app\shopapi\logic
 */
class LoginLogic extends Logic
{

    /**
     * @notes 账号密码登录
     * @param $params
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2021/11/9 16:37
     */
    public static function accountLogin($params)
    {
        $adminModel = new ShopAdmin();

        $admin = $adminModel->alias('a')
            ->join('shop s', 's.id = a.shop_id')
            ->field(['a.id', 'a.account', 'a.name', 'role_id', 'shop_id', 's.name' => 'shop_name', 's.logo' => 'shop_logo'])
            ->where(['a.account' => $params['account'], 'a.del' => 0])
            ->findOrEmpty()->toArray();

        $admin['shop_logo'] = UrlServer::getFileUrl($admin['shop_logo']);
        $admin['token'] = self::createSession($admin['id'], $params['client']);

        //登录信息更新
        $adminModel->where(['account' => $params['account']])->update([
            'login_ip' => request()->ip(),
            'login_time' => time()
        ]);
        return $admin;
    }


    /**
     * @notes 退出登录
     * @param $user_id
     * @param $client
     * @return SessionModel
     * @author 段誉
     * @date 2021/11/9 16:37
     */
    public static function logout($user_id, $client)
    {
        $time = time();
        $token = (new ShopSession())
            ->where(['admin_id' => $user_id, 'client' => $client])
            ->value('token');

        Cache::delete($token);

        return (new ShopSession())
            ->where(['admin_id' => $user_id, 'client' => $client])
            ->update(['update_time' => $time, 'expire_time' => $time]);
    }


    /**
     * @notes 创建会话
     * @param $admin_id
     * @param $client
     * @return string
     * @author 段誉
     * @date 2021/11/9 16:38
     */
    public static function createSession($admin_id, $client)
    {
        //清除之前缓存
        $token = SessionModel::where(['admin_id' => $admin_id, 'client' => $client])
            ->value('token');
        if ($token) {
            Cache::delete($token);
        }

        $result = SessionModel::where(['admin_id' => $admin_id, 'client' => $client])
            ->findOrEmpty();

        $time = time();
        $expire_time = $time + Config::get('project.token_expire_time');

        // 新token
        $token = md5($admin_id . $client . $time);

        $shop_amdin = ShopAdmin::where(['id' => $admin_id])->findOrEmpty();

        $data = [
            'shop_id' => $shop_amdin['shop_id'],
            'admin_id' => $admin_id,
            'token' => $token,
            'client' => $client,
            'update_time' => $time,
            'expire_time' => $expire_time,
        ];

        if ($result->isEmpty()) {
            SessionModel::create($data);
        } else {
            SessionModel::where(['admin_id' => $admin_id, 'client' => $client])
                ->update($data);
        }

        //更新登录信息
        $login_ip = request()->ip();
        ShopAdmin::where(['id' => $admin_id])
            ->update(['login_time' => $time, 'login_ip' => $login_ip]);

        // 获取最新的用户信息
        $admin_info = ShopAdmin::alias('a')
            ->join('shop_session s', 'a.id=s.admin_id')
            ->where(['s.token' => $token])
            ->field('a.*,s.token,s.client')
            ->find();
        $admin_info = $admin_info ? $admin_info->toArray() : [];

        //创建新的缓存
        $ttl = 0 + Config::get('project.token_expire_time');
        Cache::set($token, $admin_info, $ttl);

        return $token;
    }


}
