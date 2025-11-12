<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\common\server;


/**
 * URL转换 服务类
 * Class UrlServer
 * @package app\common\server
 */
class UrlServer
{
    /**
     * Notes: 获取文件全路径
     * @param string $uri
     * @author 张无忌(2021/1/29 9:42)
     * @return string
     */
    public static function getFileUrl($uri='',$type='')
    {
        if (strstr($uri, 'http://'))  return $uri;
        if (strstr($uri, 'https://')) return $uri;

        $engine = ConfigServer::get('storage', 'default', 'local');
        if (empty($engine) || $engine === 'local') {
            //图片分享处理
            if ($type && $type == 'share') {
                return ROOT_PATH . $uri;
            }
            $domain = request()->domain(true);
        } else {
            $config = ConfigServer::get('storage_engine',$engine);
            $domain = $config['domain'];
        }
        return self::format($domain, $uri);
    }

    /**
     * NOTE: 设置文件路径转相对路径
     * @author: 张无忌
     * @param string $uri
     * @return mixed
     */
    public static function setFileUrl($uri='')
    {
        $engine = ConfigServer::get('storage', 'default', 'local');
        if (empty($engine) || $engine === 'local') {
            $domain = request()->domain();
            return str_replace($domain.'/', '', $uri);
        } else {
            $config = ConfigServer::get('storage_engine', $engine);
            return str_replace($config['domain'], '', $uri);
        }
    }


    /**
     * @notes 处理域名
     * @param $domain
     * @param $uri
     * @return string
     * @author 段誉
     * @date 2022/6/6 15:41
     */
    public static function format($domain, $uri)
    {
        // 处理域名
        $domainLen = strlen($domain);
        $domainRight = substr($domain, $domainLen -1, 1);
        if ('/' == $domainRight) {
            $domain = substr_replace($domain,'',$domainLen -1, 1);
        }

        // 处理uri
        $uriLeft = substr($uri, 0, 1);
        if('/' == $uriLeft) {
            $uri = substr_replace($uri,'',0, 1);
        }

        return trim($domain) . '/' . trim($uri);
    }
}