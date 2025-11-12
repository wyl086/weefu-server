<?php
namespace app\admin\logic\setting;

use app\common\basics\Logic;
use app\common\server\ConfigServer;

class HotSearchLogic extends Logic
{
    public static function info()
    {
        // 使用 [''] 目标，触发后台页面可正常显示一个空的添加项
        $info = ConfigServer::get('hot_search','hot_keyword',['']);
        $info = empty($info) ? [''] : $info;
        return $info;
    }


    public static function set($post)
    {
        if ($post['hot_keyword'] == ['']){
            return ConfigServer::set('hot_search','hot_keyword',[]);
        }
        $hotKeyword = array_filter($post['hot_keyword'], function($value) {
            return !empty($value);
        });
        return ConfigServer::set('hot_search','hot_keyword', $hotKeyword);
    }
}