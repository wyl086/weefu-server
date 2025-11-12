<?php
namespace app\shop\logic\decoration;

use app\common\basics\Logic;
use app\common\model\shop\Shop;
use app\common\enum\ShopEnum;
use app\common\server\ConfigServer;
use app\common\server\UrlServer;

class IndexLogic extends Logic
{
    public static function getShopSet($shop_id)
    {
        $shop = Shop::field('logo,background,cover,banner')->findOrEmpty($shop_id)->toArray();
        $shop['logo'] = UrlServer::getFileUrl(!empty($shop['logo']) ? $shop['logo'] : ShopEnum::DEFAULT_LOGO);
        $shop['background'] = UrlServer::getFileUrl(!empty($shop['background']) ? $shop['background'] : ShopEnum::DEFAULT_BG);
        $shop['pc_cover'] = UrlServer::getFileUrl(!empty($shop['cover']) ? $shop['cover'] : ShopEnum::DEFAULT_COVER);
        $shop['pc_banner'] = UrlServer::getFileUrl(!empty($shop['banner']) ? $shop['banner'] : ShopEnum::DEFAULT_BANNER);
        $shop['dome_background'] = UrlServer::getFileUrl(ShopEnum::DOME_BG);
        $shop['dome_cover'] = UrlServer::getFileUrl(ShopEnum::DOME_COVER);
        $shop['dome_banner'] = UrlServer::getFileUrl(ShopEnum::DOME_BANNER);
        return $shop;
    }

    public static function set($post)
    {
        try{
            $update = [
                'logo' => clearDomain($post['logo']),
                'background' => clearDomain($post['background']),
                'cover' => clearDomain($post['pc_cover']),
                'banner' => isset($post['pc_banner']) ? clearDomain($post['pc_banner']) : '',
                'update_time' => time()
            ];
            $where = [
                'id' => $post['shop_id'],
                'del' => 0
            ];
            Shop::where($where)->update($update);
            return true;
        }catch(\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }
}