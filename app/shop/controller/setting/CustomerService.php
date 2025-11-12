<?php
namespace app\shop\controller\setting;

use app\common\basics\ShopBase;
use app\common\server\ConfigServer;
use app\common\server\JsonServer;
use app\common\server\UrlServer;

class CustomerService extends ShopBase
{
    public function index()
    {
        $image = ConfigServer::get('shop_customer_service', 'image', '', $this->shop_id);
        $image = $image ? UrlServer::getFileUrl($image) : '';
        $config = [
            'type'              => ConfigServer::get('shop_customer_service', 'type', 1,$this->shop_id),
            'wechat' => ConfigServer::get('shop_customer_service', 'wechat', '', $this->shop_id),
            'phone' => ConfigServer::get('shop_customer_service', 'phone', '', $this->shop_id),
            'business_time' => ConfigServer::get('shop_customer_service', 'business_time', '', $this->shop_id),
            'image' => $image,
        ];
        return view('', [
            'config' => $config
        ]);
    }

    public function set()
    {
        $post = $this->request->post();
        ConfigServer::set('shop_customer_service', 'type', $post['type'], $this->shop_id);
        ConfigServer::set('shop_customer_service', 'wechat', $post['wechat'], $this->shop_id);
        ConfigServer::set('shop_customer_service', 'phone', $post['phone'], $this->shop_id);
        ConfigServer::set('shop_customer_service', 'business_time', $post['business_time'], $this->shop_id);
        if(isset($post['image'])){
            ConfigServer::set('shop_customer_service', 'image', clearDomain($post['image']), $this->shop_id);
        }
        return JsonServer::success('设置成功');
    }
}