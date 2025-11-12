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

class PcLogic extends Logic
{

    /**
     * 获取PC商城设置
     */
    public static function getConfig()
    {
        $config = [
            'is_open' => ConfigServer::get('pc', 'is_open', 1),
            'page' => ConfigServer::get('pc', 'page', 1),
            'page_url' => ConfigServer::get('pc', 'page_url', ''),
            'pc_url' => request()->domain() . '/pc'
        ];

        return $config;
    }

    /**
     * PC商城设置
     */
    public static function set($params)
    {
        ConfigServer::set('pc', 'is_open', $params['is_open']);
        ConfigServer::set('pc', 'page', $params['page']);
        ConfigServer::set('pc', 'page_url', $params['page_url']);


        // 恢复原入口
        if(file_exists('./pc/index_lock.html')) {
            // 存在则原商城入口被修改过，先清除修改后的入口
            unlink('./pc/index.html');
            // 恢复原入口
            rename('./pc/index_lock.html', './pc/index.html');

            // 删除旧的缓存文件
            array_map('unlink', glob('../runtime/index/temp/'.'*.php'));
        }

        // pc商城关闭 且 显示空白页
        if($params['is_open'] == 0 && $params['page'] == 1) {
            // 变更文件名
            rename('./pc/index.html', './pc/index_lock.html');
            // 创建新空白文件
            $newfile = fopen('./pc/index.html', 'w');
            fclose($newfile);
        }

        // pc商城关闭 且 跳转指定页
        if($params['is_open'] == 0 && $params['page'] == 2 && !empty($params['page_url'])) {
            // 变更文件名
            rename('./pc/index.html', './pc/index_lock.html');
            // 创建重定向文件
            $newfile = fopen('./pc/index.html', 'w');
            $content = '<script>window.location.href = "' . $params['page_url'] . '";</script>';
            fwrite($newfile, $content);
            fclose($newfile);
        }
    }
}