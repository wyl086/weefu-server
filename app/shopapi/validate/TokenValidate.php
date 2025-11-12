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
use app\common\model\ShopSession as SessionModel;

/**
 * 商家移动端管理员登录token验证
 * Class TokenValidate
 * @package app\shopapi\validate
 */
class TokenValidate extends Validate
{
    protected $rule = [
        'token' => 'require|valid|admin',
    ];

    /**
     * User: 意象信息科技 lr
     * Desc: token验证
     * @param $token
     * @param $other
     * @param $data
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function valid($token, $other, $data)
    {
        $session = SessionModel::where(['token' => $token])->find();
        if (empty($session)) {
            return '会话失效，请重新登录';
        }
        if ($session['expire_time'] <= time()) {
            return '登录超时，请重新登录';
        }
        return true;
    }

    /**
     * User: 意象信息科技 lr
     * Desc 用户验证
     * @param $token
     * @param $other
     * @param $data
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function admin($token, $other, $data)
    {
        $admin_id = SessionModel::where(['token' => $token])
            ->value('admin_id');

        $admin_info = ShopAdmin::where(['id' => $admin_id, 'del' => 0])
            ->find();
        if (empty($admin_info)) {
            return '用户不存在';
        }
        if ($admin_info['disable'] == 1) {
            return '用户被禁用';
        }
        return true;
    }


}