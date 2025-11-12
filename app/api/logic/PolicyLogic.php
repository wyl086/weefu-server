<?php
namespace app\api\logic;

use app\common\basics\Logic;
use app\common\server\ConfigServer;
use app\common\server\UrlServer;

class PolicyLogic extends Logic
{
    public static function service()
    {
        $service = ConfigServer::get('policy', 'service', '');
        $preg = '/<img.*?src="((?!(https|http)).*?)".*?\/?>/i';
        $local_url = UrlServer::getFileUrl();
        $res = preg_replace($preg, '<img src="' . $local_url . '${1}" />', $service);
        return $res;
    }

    public static function privacy()
    {
        $privacy = ConfigServer::get('policy', 'privacy', '');
        $preg = '/<img.*?src="((?!(https|http)).*?)".*?\/?>/i';
        $local_url = UrlServer::getFileUrl();
        $res = preg_replace($preg, '<img src="' . $local_url . '${1}" />', $privacy);
        return $res;
    }

    public static function afterSale()
    {
        $after_sale = ConfigServer::get('policy', 'after_sale', '');
        $preg = '/<img.*?src="((?!(https|http)).*?)".*?\/?>/i';
        $local_url = UrlServer::getFileUrl();
        $res = preg_replace($preg, '<img src="' . $local_url . '${1}" />', $after_sale);
        return $res;
    }
    
    public static function userDelete()
    {
        $user_delete = ConfigServer::get('policy', 'user_delete', '');
        $preg = '/<img.*?src="((?!(https|http)).*?)".*?\/?>/i';
        $local_url = UrlServer::getFileUrl();
        $res = preg_replace($preg, '<img src="' . $local_url . '${1}" />', $user_delete);
        return $res;
    }
}