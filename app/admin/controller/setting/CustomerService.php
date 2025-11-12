<?php
namespace app\admin\controller\setting;

use app\common\basics\AdminBase;
use app\common\server\ConfigServer;
use app\common\server\JsonServer;
use app\common\server\UrlServer;

class CustomerService extends AdminBase
{
    public function index()
    {
        $image = ConfigServer::get('customer_service', 'image', '');
        $image = $image ? UrlServer::getFileUrl($image) : '';
        $config = [
            'type'              => ConfigServer::get('customer_service', 'type', 1),
            'wechat'            => ConfigServer::get('customer_service', 'wechat', ''),
            'phone'             => ConfigServer::get('customer_service', 'phone', ''),
            'business_time'     => ConfigServer::get('customer_service', 'business_time', ''),
            'image'             => $image,
        ];
        return view('', [
            'config' => $config
        ]);
    }

    public function set()
    {
        $post = $this->request->post();
        ConfigServer::set('customer_service', 'type', $post['type']);
        ConfigServer::set('customer_service', 'wechat', $post['wechat']);
        ConfigServer::set('customer_service', 'phone', $post['phone']);
        ConfigServer::set('customer_service', 'business_time', $post['business_time']);
        if(isset($post['image'])){
            ConfigServer::set('customer_service', 'image', clearDomain($post['image']));
        }
        return JsonServer::success('设置成功');
    }
}