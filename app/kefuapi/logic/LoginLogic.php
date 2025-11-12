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

namespace app\kefuapi\logic;

use app\common\basics\Logic;
use app\common\enum\KefuEnum;
use app\common\model\Admin;
use app\common\model\kefu\KefuSession;
use app\common\model\shop\ShopAdmin;
use app\common\server\UrlServer;
use think\facade\Config;

/**
 * 客服登录逻辑
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
        if (KefuEnum::TYPE_SHOP == $params['type']) {
            $kefu = (new ShopAdmin())->alias('a')
                ->field(['k.id', 'k.nickname', 'k.avatar', 'k.shop_id', 'a.account'])
                ->join('kefu k', 'a.id = k.admin_id and a.shop_id = k.shop_id')
                ->where(['a.account' => $params['account'], 'k.del' => 0])
                ->findOrEmpty()->toArray();
        } else {
            $kefu = (new Admin())->alias('a')
                ->field(['k.id', 'k.nickname', 'k.avatar', 'k.shop_id', 'a.account'])
                ->join('kefu k', 'a.id = k.admin_id')
                ->where(['a.account' => $params['account'], 'k.shop_id' => 0, 'k.del' => 0])
                ->findOrEmpty()->toArray();
        }

        $kefu['avatar'] = !empty($kefu['avatar']) ? UrlServer::getFileUrl($kefu['avatar']) : "";

        $kefu['token'] = self::createSession($kefu['id'], $kefu['shop_id'], $params['client']);

        return $kefu;
    }


    /**
     * @notes 退出登录
     * @param $user_id
     * @param $client
     * @return KefuSession
     * @author 段誉
     * @date 2021/11/23 17:14
     */
    public static function logout($user_id, $client)
    {
        return (new KefuSession())
            ->where(['kefu_id' => $user_id, 'client' => $client])
            ->update(['update_time' => time(), 'expire_time' => time()]);
    }


    /**
     * @notes 创建会话
     * @param $admin_id
     * @param $client
     * @return string
     * @author 段誉
     * @date 2021/11/9 16:38
     */
    public static function createSession($kefu_id, $shop_id, $client)
    {
        $result = KefuSession::where(['kefu_id' => $kefu_id, 'client' => $client])->findOrEmpty();

        $time = time();
        $expire_time = $time + Config::get('project.token_expire_time');

        // 新token
        $token = md5($kefu_id . $client . $time);

        $data = [
            'shop_id' => $shop_id,
            'kefu_id' => $kefu_id,
            'token' => $token,
            'client' => $client,
            'update_time' => $time,
            'expire_time' => $expire_time,
        ];

        if ($result->isEmpty()) {
            KefuSession::create($data);
        } else {
            KefuSession::where([
                'kefu_id' => $kefu_id,
                'shop_id' => $shop_id,
                'client' => $client,
            ])->update($data);
        }

        return $token;
    }


}
