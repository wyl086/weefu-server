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


namespace app\shop\logic;


use app\common\basics\Logic;
use app\common\model\shop\ShopAdmin;
use app\common\server\ConfigServer;
use think\facade\Cookie;

/**
 * 商家登录逻辑
 * Class LoginLogic
 * @package app\shop\logic
 */
class LoginLogic extends Logic
{

    /**
     * Notes: 登录
     * @param $post
     * @author 段誉(2021/4/10 10:40)
     * @return bool
     */
    public static function login($post)
    {
        $adminModel = new ShopAdmin();
        $admin_info = $adminModel->alias('a')
            ->join('shop s', 's.id = a.shop_id')
            ->field(['a.id', 'a.account', 'a.name', 'role_id', 'shop_id', 's.name' => 'shop_name'])
            ->where(['a.account' => $post['account'], 'a.del' => 0])
            ->findOrEmpty()->toArray();

        //session
        session('shop_info', $admin_info);

        //登录信息更新
        $adminModel->where(['account' => $post['account']])
            ->update([
                'login_ip' => request()->ip(),
                'login_time' => time()
            ]);

        //记住账号
        if (isset($post['remember_account']) && $post['remember_account'] == 'on') {
            Cookie::set('account', $post['account']);
        } else {
            Cookie::delete('account');
        }
        return true;
    }

    /**
     * Notes: 退出
     * @author 段誉(2021/4/10 10:40)
     */
    public static function logout()
    {
        session('shop_info', null);
    }



    public static function config()
    {
        $config = [
            'company_name' => ConfigServer::get('copyright', 'company_name'),
            'number' => ConfigServer::get('copyright', 'number'),
            'link' => ConfigServer::get('copyright', 'link'),

            'login_logo' => ConfigServer::get('website_shop', 'shop_login_logo'),
            'login_image' => ConfigServer::get('website_shop', 'shop_login_image'),
            'login_title' => ConfigServer::get('website_shop', 'shop_login_title'),

            'name' => ConfigServer::get('website', 'name'),
            'web_favicon' => ConfigServer::get('website', 'web_favicon'),
        ];
        return $config;
    }


}