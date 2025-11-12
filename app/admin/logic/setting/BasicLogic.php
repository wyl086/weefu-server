<?php

namespace app\admin\logic\setting;

use app\common\basics\Logic;
use app\common\server\ConfigServer;
use app\common\server\FileServer;
use app\common\server\UrlServer;

/**
 * 网站基础设置
 * Class BasicLogic
 * @package app\admin\logic\setting
 */
class BasicLogic extends Logic
{

    /**
     * Notes: 网站设置
     * @author 段誉(2021/6/10 23:52)
     * @return array
     */
    public static function getBasicConfig()
    {
        $config = [
            'file_url' => UrlServer::getFileUrl('/'),
            'name' => ConfigServer::get('website', 'name'),
            'web_favicon' => ConfigServer::get('website', 'web_favicon'),
            'client_login_logo' => ConfigServer::get('website', 'client_login_logo'),
            'pc_client_login_logo' => ConfigServer::get('website', 'pc_client_login_logo'),
            'user_image' => ConfigServer::get('website', 'user_image'),
            'goods_image' => ConfigServer::get('website', 'goods_image'),

            'platform_login_logo' => ConfigServer::get('website_platform', 'platform_login_logo'),
            'platform_login_image' => ConfigServer::get('website_platform', 'platform_login_image'),
            'platform_login_title' => ConfigServer::get('website_platform', 'platform_login_title'),
            'platform_admin_logo' => ConfigServer::get('website_platform', 'platform_admin_logo'),
            'platform_contact' => ConfigServer::get('website_platform', 'platform_contact'),
            'platform_mobile' => ConfigServer::get('website_platform', 'platform_mobile'),

            'shop_login_logo' => ConfigServer::get('website_shop', 'shop_login_logo'),
            'shop_login_image' => ConfigServer::get('website_shop', 'shop_login_image'),
            'shop_login_title' => ConfigServer::get('website_shop', 'shop_login_title'),
            'shop_admin_logo' => ConfigServer::get('website_shop', 'shop_admin_logo'),
            'shop_default_logo' => ConfigServer::get('website_shop', 'shop_default_logo'),
            'shop_default_bg' => ConfigServer::get('website_shop', 'shop_default_bg'),
        ];
        return $config;
    }


    /**
     * Notes: 网站设置-商城设置
     * @param $post
     * @author 段誉(2021/6/10 23:53)
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function setWebsiteBasic($post)
    {
        ConfigServer::set('website', 'name', $post['name']);
        ConfigServer::set('website', 'web_favicon', UrlServer::setFileUrl($post['web_favicon'] ?? ''));
        ConfigServer::set('website', 'client_login_logo', UrlServer::setFileUrl($post['client_login_logo'] ?? ''));
        ConfigServer::set('website', 'pc_client_login_logo', UrlServer::setFileUrl($post['pc_client_login_logo'] ?? ''));
        ConfigServer::set('website', 'user_image', UrlServer::setFileUrl($post['user_image'] ?? ''));
        ConfigServer::set('website', 'goods_image', UrlServer::setFileUrl($post['goods_image'] ?? ''));
    }

    /**
     * Notes: 网站设置-平台设置
     * @param $post
     * @author 段誉(2021/6/10 23:53)
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function setPlatform($post)
    {
        ConfigServer::set('website_platform', 'platform_login_logo', UrlServer::setFileUrl($post['platform_login_logo'] ?? ''));
        ConfigServer::set('website_platform', 'platform_login_image', UrlServer::setFileUrl($post['platform_login_image'] ?? ''));
        ConfigServer::set('website_platform', 'platform_login_title', $post['platform_login_title']);
        ConfigServer::set('website_platform', 'platform_admin_logo', UrlServer::setFileUrl($post['platform_admin_logo'] ?? ''));
        ConfigServer::set('website_platform', 'platform_contact', $post['platform_contact']);
        ConfigServer::set('website_platform', 'platform_mobile', $post['platform_mobile']);
    }


    /**
     * Notes: 网站设置-商家设置
     * @param $post
     * @author 段誉(2021/6/10 23:53)
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function setShop($post)
    {
        ConfigServer::set('website_shop', 'shop_login_logo', UrlServer::setFileUrl($post['shop_login_logo'] ?? ''));
        ConfigServer::set('website_shop', 'shop_login_image', UrlServer::setFileUrl($post['shop_login_image'] ?? ''));
        ConfigServer::set('website_shop', 'shop_login_title', $post['shop_login_title']);
        ConfigServer::set('website_shop', 'shop_admin_logo', UrlServer::setFileUrl($post['shop_admin_logo'] ?? ''));
        ConfigServer::set('website_shop', 'shop_default_logo', UrlServer::setFileUrl($post['shop_default_logo'] ?? ''));
        ConfigServer::set('website_shop', 'shop_default_bg', UrlServer::setFileUrl($post['shop_default_bg'] ?? ''));
    }


    /**
     * @notes 获取版权资质
     * @return array[]
     * @author ljj
     * @date 2022/2/22 9:30 上午
     */
    public static function getCopyright()
    {
        $config = [
            'company_name' => ConfigServer::get('copyright', 'company_name'),
            'number' => ConfigServer::get('copyright', 'number'),
            'link' => ConfigServer::get('copyright', 'link'),
            'business_license' => ConfigServer::get('copyright', 'business_license'),
            'other_qualifications' => ConfigServer::get('copyright', 'other_qualifications',[]),
        ];

        $config['business_license'] = $config['business_license'] ? UrlServer::getFileUrl($config['business_license']) : '';
        if (!empty($config['other_qualifications'])) {
            foreach ($config['other_qualifications'] as &$val) {
                $val = UrlServer::getFileUrl($val);
            }
        }


        return ['config'=>$config];
    }

    /**
     * @notes 设置版权资质
     * @param $post
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2022/2/22 9:37 上午
     */
    public static function setCopyright($post)
    {
        $num = count($post['other_qualifications'] ?? []);
        if ($num > 5) {
            return '其他资质图片不能超过五张';
        }

        ConfigServer::set('copyright', 'company_name', $post['company_name']);
        ConfigServer::set('copyright', 'number', $post['number']);
        ConfigServer::set('copyright', 'link', $post['link']);
        ConfigServer::set('copyright', 'business_license', UrlServer::setFileUrl($post['business_license'] ?? ''));
        $other_qualifications = [];
        if (!empty($post['other_qualifications'])) {
            foreach ($post['other_qualifications'] as &$val) {
                $val = UrlServer::setFileUrl($val);
            }
            $other_qualifications = json_encode($post['other_qualifications']);
        }
        ConfigServer::set('copyright', 'other_qualifications', $other_qualifications);

        return true;
    }

}