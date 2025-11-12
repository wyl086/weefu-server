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


namespace app\shop\validate;


use app\common\basics\Validate;
use app\common\model\shop\ShopAdmin;
use think\facade\Cache;

/**
 * 登录数据验证
 * Class LoginValidate
 * @package app\shop\validate
 */
class LoginValidate extends Validate
{
    protected $rule = [
        'account'   => 'require',
        'password'  => 'require|password',
    ];

    protected $message = [
        'account.require'   => '请填写登录账号',
        'password.require'  => '请填写登录密码',
        'password.password' => '账号密码错误',
    ];


    /**
     * 账号密码验证码
     * @param $password
     * @param $other
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function password($password, $other, $data)
    {
        if ($this->safe() === false) {
            $this->message['password.password'] .= ':多次输入错误';
            return false;
        }

        $adminModel = new ShopAdmin();
        $admin_info = $adminModel->alias('a')
            ->field('a.*')
            ->join('shop s', 's.id = a.shop_id')
            ->where(['a.account' => $data['account'], 'a.del' => 0])
            ->find();

        if (empty($admin_info)) {
            $this->safe(true);
            return '账号不存在';
        }

        if ($admin_info['disable']) {
            return '账号被禁用';
        }

        $password = generatePassword($password, $admin_info['salt']);
        if ($password != $admin_info['password']) {
            $this->safe(true);
            return false;
        }

        return true;
    }

    /**
     * 连续30分钟内15次输错密码，无法登录
     * @param bool $add
     * @return bool
     */
    protected function safe($add = false)
    {
        $cache_name = 'shop_admin_login_error_count' . request()->ip();
        if ($add) {
            $admin_login_error_count = Cache::get($cache_name);
            $admin_login_error_count++;
            Cache::tag('shop_admin_login_error_count')->set($cache_name, $admin_login_error_count, 1800);
        }
        $count = Cache::get($cache_name);

        if (!empty($count) && $count >= 15) {
            return false;
        }
        return true;
    }

}