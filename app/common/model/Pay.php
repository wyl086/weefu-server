<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\common\model;

use app\common\basics\Models;
use app\common\server\UrlServer;

/**
 * 支付
 * Class Pay
 * @package app\common\model
 */
class Pay extends Models
{
    protected $name = 'dev_pay';

    // 设置json类型字段
    protected $json = ['config'];

    // 设置JSON数据返回数组
    protected $jsonAssoc = true;

    //图片路径
    public function getIconAttr($value, $data)
    {
        return UrlServer::getFileUrl($value);
    }


    //支付设置状态
    public function getStatusTextAttr($value, $data)
    {
        if ($data['status'] == 1){
            return '启用';
        }
        return '关闭';
    }
    
}