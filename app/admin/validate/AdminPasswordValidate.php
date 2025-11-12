<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\admin\validate;

use app\common\basics\Validate;
use app\common\model\Admin;

/**
 * 管理员密码
 * Class AdminPasswordValidate
 * @package app\admin\validate
 */
class AdminPasswordValidate extends Validate
{
    protected $rule = [
        'old_password' => 'require|verify',
        'password' => 'require|length:6,16',
        're_password' => 'confirm:password',
    ];

    protected $message = [
        'old_password.require' => '当前密码不能为空',
        'old_password.verify' => '当前密码输入不正确',
        'password.require' => '新密码不能为空',
        'password.length' => '密码长度必须为6到16位之间',
        're_password.confirm' => '两次密码输入不一致',
    ];

    /**
     * 密码验证
     * @param $old_password
     * @param $other
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function verify($old_password, $other, $data)
    {
        $admin = Admin::find($data['admin_id']);
        $password = generatePassword($old_password, $admin['salt']);

        if ($password != $admin['password']) {
            return false;
        }

        return true;
    }
}