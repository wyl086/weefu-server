<?php
namespace app\api\controller;

use app\common\basics\Api;
use app\common\server\ConfigServer;
use app\common\server\JsonServer;
use app\common\server\UrlServer;
use app\common\model\shop\Shop;

class Setting extends Api
{
    public $like_not_need_login = ['getPlatformCustomerService', 'getShopCustomerService'];

    /**
     * 平台客服
     */
    public function getPlatformCustomerService()
    {
        $image = ConfigServer::get('customer_service', 'image', '');
        $image = $image ? UrlServer::getFileUrl($image) : '';
        $config = [
            'wechat' => ConfigServer::get('customer_service', 'wechat', ''),
            'phone' => ConfigServer::get('customer_service', 'phone', ''),
            'business_time' => ConfigServer::get('customer_service', 'business_time', ''),
            'image' => $image
        ];
        return JsonServer::success('', $config);
    }

    /**
     * 商家客服
     */
    public function getShopCustomerService()
    {
        $shop_id = $this->request->get('shop_id', '', 'intval');
        $shop = Shop::field('id,name,logo')->where('id', $shop_id)->findOrEmpty();
        if($shop->isEmpty()) {
            return JsonServer::error('店铺信息不存在');
        }
        $shop = $shop->toArray();
        $shop['logo'] = $shop['logo'] ? UrlServer::getFileUrl($shop['logo']) : '';
        $image = ConfigServer::get('shop_customer_service', 'image', '', $shop_id);
        $image = $image ? UrlServer::getFileUrl($image) : '';
        $config = [
            'wechat' => ConfigServer::get('shop_customer_service', 'wechat', '', $shop_id),
            'phone' => ConfigServer::get('shop_customer_service', 'phone', '', $shop_id),
            'business_time' => ConfigServer::get('shop_customer_service', 'business_time', '',$shop_id),
            'image' => $image
        ];
        return JsonServer::success('', [
            'config' => $config,
            'shop' => $shop
        ]);
    }
}
