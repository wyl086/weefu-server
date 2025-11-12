<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\common\model\integral;

use app\common\basics\Models;
use app\common\server\UrlServer;


/**
 * 积分商品
 * Class IntegralGoods
 * @package app\common\model\integral
 */
class IntegralGoods extends Models
{

    // 商品详情(富文本内容)
    public function getContentAttr($value,$data){
/*        $preg = '/(<img .*?src=")[^https|^http](.*?)(".*?>)/is';*/
//        $local_url = UrlServer::getFileUrl();
//        return  preg_replace($preg, "\${1}$local_url\${2}\${3}",$value);
        $content = $data['content'];
        if (!empty($content)) {
            $content = HtmlGetImage($content);
        }
        return $content;
    }
    public function setContentAttr($value,$data)
    {
        $content = $data['content'];
        if (!empty($content)) {
            $content = HtmlSetImage($content);
        }
        return $content;
    }
}