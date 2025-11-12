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

namespace app\kefuapi\validate;


use app\common\basics\Validate;
use app\common\model\Admin;
use app\common\model\kefu\Kefu;
use app\common\model\kefu\KefuSession;
use app\common\model\shop\ShopAdmin;

/**
 * 客服登录验证
 * Class TokenValidate
 * @package app\kefu\validate
 */
class TokenValidate extends Validate
{
    protected $rule = [
        'token' => 'require|valid|chat',
    ];

    /**
     * @notes token验证
     * @param $token
     * @param $other
     * @param $data
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2021/11/23 10:55
     */
    protected function valid($token, $other, $data)
    {
        $session = KefuSession::where(['token' => $token])->find();
        if (empty($session)) {
            return '会话失效，请重新登录';
        }
        if ($session['expire_time'] <= time()) {
            return '登录超时，请重新登录';
        }
        return true;
    }


    /**
     * @notes 用户验证
     * @param $token
     * @param $other
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2021/11/23 17:29
     */
    protected function chat($token, $other, $data)
    {
        $kefu = (new Kefu())->alias('k')
            ->join('kefu_session ks', 'k.id = ks.kefu_id')
            ->where(['ks.token' => $token, 'k.del' => 0])
            ->field('k.*,ks.token,ks.client')
            ->hidden(['password'])
            ->findOrEmpty();

        if ($kefu->isEmpty()) {
            return '用户不存在';
        }

        // 获取客服对应的管理员信息
        if ($kefu['shop_id'] > 0) {
            $kefu_admin = (new ShopAdmin())->where(['id' => $kefu['admin_id']])->findOrEmpty();
        } else {
            $kefu_admin = (new Admin())->where(['id' => $kefu['admin_id']])->findOrEmpty();
        }

        if ($kefu_admin->isEmpty()) {
            return '关联管理员不存在';
        }

        if ($kefu['disable'] == 1 || $kefu_admin['disable'] == 1) {
            return '用户被禁用';
        }

        return true;
    }


}