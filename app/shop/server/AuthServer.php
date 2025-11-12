<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | gitee下载：https://gitee.com/multshop_gitee
// | github下载：https://github.com/multshop-github
// | 访问官网：https://www.multshop.cn
// | 访问社区：https://home.multshop.cn
// | 访问手册：http://doc.multshop.cn
// | 微信公众号：multshop技术社区
// | multshop系列产品在gitee、github等公开渠道开源版本可免费商用，未经许可不能去除前后端官方版权标识
// |  multshop系列产品收费版本务必购买商业授权，购买去版权授权后，方可去除前后端官方版权标识
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\shop\server;

use app\common\model\shop\ShopAuth;
use app\common\model\shop\ShopRoleAuthIndex;

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
        $result = ShopAuth::where('id', 'in', $ids)
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
        return ShopRoleAuthIndex::where(['role_id' => $role_id])
            ->column('menu_auth_id');
    }

    /**
     * 获取系统所有权限
     * @return array
     */
    private static function getAllAuth()
    {
        return ShopAuth::where(['del' => 0, 'disable' => 0])
            ->column('id');
    }

    /**
     * 获取用户没有权限uri
     * @param $role_id
     * @return array
     */
    public static function getRoleNoneAuthArr($role_id)
    {
        return ShopAuth::where('id', 'in', self::getRoleNoneAuthIds($role_id))
            ->column('uri');
    }
}