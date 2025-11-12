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
use app\common\model\RoleAuthIndex;

class AuthServer
{

    /**
     * 获取角色没有的权限id
     * @param int $role_id
     * @return array
     */
    public static function getRoleNoneAuthIds($role_id)
    {
        if ($role_id == 0) {
            return [];
        }
        $role_auth = self::getRoleAuth($role_id);
        $all_auth = self::getAllAuth();
        return array_diff($all_auth, $role_auth);
    }

    /**
     * 获取角色没有的uri
     * @param $role_id
     */
    public static function getRoleNoneAuthUris($role_id)
    {
        $ids = self::getRoleNoneAuthIds($role_id);
        $result = (new Auth())
            ->where('id', 'in', $ids)
            ->column('uri');
        $data = [];
        foreach ($result as $k => $v) {
            if (empty($v)) {
                continue;
            }
            $data[] = strtolower($v);
        }
        return $data;
    }

    /**
     * 获取角色权限
     * @param $role_id
     * @return array
     */
    private static function getRoleAuth($role_id)
    {
        return (new RoleAuthIndex())
            ->where(['role_id' => $role_id])
            ->column('menu_auth_id');
    }

    /**
     * 获取系统所有权限
     * @return array
     */
    private static function getAllAuth()
    {
        return (new Auth())
            ->where(['del' => 0, 'disable' => 0])
            ->column('id');
    }

    /**
     * 获取用户没有权限uri
     * @param $role_id
     * @return array
     */
    public static function getRoleNoneAuthArr($role_id)
    {
        return (new Auth())
            ->where('id', 'in', self::getRoleNoneAuthIds($role_id))
            ->column('uri');
    }


}