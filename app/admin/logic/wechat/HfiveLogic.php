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
use think\facade\Env;

class HfiveLogic extends Logic
{

    /**
     * 获取H5商城设置
     */
    public static function getConfig()
    {
        $config = [
            'is_open' => ConfigServer::get('h5', 'is_open', 1),
            'page' => ConfigServer::get('h5', 'page', 1),
            'page_url' => ConfigServer::get('h5', 'page_url', ''),
            'h5_url' => request()->domain() . '/mobile'
        ];

        return $config;
    }

    /**
     * H5商城设置
     */
    public static function set($params)
    {
        ConfigServer::set('h5', 'is_open', $params['is_open']);
        ConfigServer::set('h5', 'page', $params['page']);
        ConfigServer::set('h5', 'page_url', $params['page_url']);


        // 恢复原入口
        if(file_exists('./mobile/index_lock.html')) {
            // 存在则原商城入口被修改过，先清除修改后的入口
            unlink('./mobile/index.html');
            // 恢复原入口
            rename('./mobile/index_lock.html', './mobile/index.html');

            // 删除旧的缓存文件
            array_map('unlink', glob('../runtime/index/temp/'.'*.php'));
        }

        // H5商城关闭 且 显示空白页
        if($params['is_open'] == 0 && $params['page'] == 1) {
            // 变更文件名
            rename('./mobile/index.html', './mobile/index_lock.html');
            // 创建新空白文件
            $newfile = fopen('./mobile/index.html', 'w');
            fclose($newfile);
        }

        // H5商城关闭 且 跳转指定页
        if($params['is_open'] == 0 && $params['page'] == 2 && !empty($params['page_url'])) {
            // 变更文件名
            rename('./mobile/index.html', './mobile/index_lock.html');
            // 创建重定向文件
            $newfile = fopen('./mobile/index.html', 'w');
            $content = '<script>window.location.href = "' . $params['page_url'] . '";</script>';
            fwrite($newfile, $content);
            fclose($newfile);
        }
    }
}