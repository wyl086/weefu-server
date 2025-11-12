<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\admin\logic\wechat;

use app\common\basics\Logic;
use app\common\server\ConfigServer;

class OpLogic extends Logic
{
    public static function getConfig($config_list)
    {
        $config = [];
        foreach ($config_list as $config_name){
            $value = ConfigServer::get('op', $config_name, '');
            $config[$config_name] = $value;
        }
        return $config;
    }

    public static function setConfig($config_list)
    {
        foreach ($config_list as $config_name => $config_value){
            ConfigServer::set('op',$config_name,$config_value);
        }
        return true;
    }
}