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

class File extends Models
{
    /**
     * NOTE: 获取器,修改图片路径
     * @author: 张无忌
     * @param $value
     * @return string
     */
    public static function getUriAttr($value)
    {
        return $value ? UrlServer::getFileUrl($value) : '';
    }
}