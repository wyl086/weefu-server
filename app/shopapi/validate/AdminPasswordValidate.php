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

namespace app\shopapi\validate;

use app\common\basics\Validate;
use app\common\model\shop\ShopAdmin;

/**
 * 管理员密码
 * Class AdminPasswordValidate
 * @package app\admin\validate
 */
class AdminPasswordValidate extends Validate
{
    protected $rule = [
        'old_password'          => 'require|verify',
        'password'              => 'require|length:6,16|confirm',
//        'password_confirm'      => 'require',
    ];

    protected $message = [
        'old_password.require'      => '当前密码不能为空',
        'old_password.verify'       => '当前密码输入不正确',
        'password.require'          => '新密码不能为空',
        'password.length'           => '密码长度必须为6到16位之间',
        'password.confirm'          => '两次密码输入不一致',
        'password_confirm.require'  => '请输入确认密码',
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
        $admin = ShopAdmin::find($data['admin_id']);
        $password = generatePassword($old_password, $admin['salt']);

        if ($password != $admin['password']) {
            return false;
        }

        return true;
    }
}