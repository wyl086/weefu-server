<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\admin\server;


use app\common\model\Auth;
use app\common\server\ArrayServer;

class MenuServer
{
    /**
     * 获取菜单树
     * @param $role_id (角色ID)
     * @return array
     */
    public static function getMenuTree($role_id)
    {
        try {
            // 获取所有菜单
            $lists = (new Auth())
                ->where(['type' => 1, 'del' => 0, 'disable' => 0])
                ->order(['sort' => 'desc'])
                ->withAttr('uri', function ($value) {
                    return self::uri($value);
                })->select()->toArray();
            // 处理返回数据
            $none_auth = AuthServer::getRoleNoneAuthIds($role_id);
            $lists = self::setRoleMenu($none_auth, $lists);
            return ArrayServer::linear_to_tree($lists);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 设置角色菜单
     * @param $none_auth
     * @param $lists
     * @return mixed
     */
    private static function setRoleMenu(&$none_auth, $lists)
    {
        foreach ($lists as $k => $v) {
            if (in_array($v['id'], $none_auth)) {
                unset($lists[$k]);
            }
        }
        return array_values($lists);
    }

    /**
     * 设置uri
     * @param $uri
     * @return string
     */
    private static function uri($uri)
    {
        return $uri;
//        if ($uri) {
//            return Route::buildUrl($uri);
//        }
//        return '';
    }


    /**
     * Notes: 创建菜单
     * @param $menu
     * @param bool $max
     * @author 张无忌(2021/2/3 9:53)
     * @return string
     */
    protected static function createHtml($menu, $max = false)
    {
        //一级菜单
        if ($max) {
            $html = '';
            $choose_class = 'layui-this';
            foreach ($menu as $k => $v) {
                $sub = isset($v['sub']) ? $v['sub'] : [];
                if ($sub) {
                    $html .= '<li data-name="set" class="layui-nav-item"><a href="javascript:;" lay-tips="' . $v['name'] . '" lay-direction="2"><i class="layui-icon ' . $v['icon'] . '"></i><cite>' . $v['name'] . '</cite></a><dl class="layui-nav-child">';
                    $html .= self::createHtml($sub);
                    $html .= '</li>';
                } else {
                    $html .= '<li data-name="set" class="layui-nav-item ' . $choose_class . '"><a href="javascript:;" lay-href="' . self::uri($v['uri']) . '" lay-tips="' . $v['name'] . '" lay-direction="2"><i class="layui-icon ' . $v['icon'] . '"></i><cite>' . $v['name'] . '</cite></a>';
                    $html .= '</dl></li>';
                }
                $choose_class = '';
            }
            return $html;
        }

        //二三级菜单无子菜单
        $html = '';
        foreach ($menu as $k => $v) {
            $sub = isset($v['sub']) ? $v['sub'] : [];
            if ($sub) {
                $html .= '<dd class="layui-nav-itemed"><a href="javascript:;">' . $v['name'] . '<i class="layui-icon ' . $v['icon'] . '"></i></a><dl class="layui-nav-child">';
                $html .= self::createHtml($sub);
                $html .= '</dl></dd>';
            } else {
                $html .= '<dd><a href="javascript:;" lay-href="' . self::uri($v['uri']) . '"><i class="layui-icon ' . $v['icon'] . '"></i>' . $v['name'] . '</a></dd>';
            }
        }
        return $html;
    }

}