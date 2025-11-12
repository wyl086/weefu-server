<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\api\validate;

use think\Validate;
use app\common\model\Session as SessionModel;
use app\common\model\user\User;

class TokenValidate extends Validate
{
    protected $rule = [
        'token' => 'require|valid|user',
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
    protected function user($token, $other, $data)
    {
        $user_id = SessionModel::where(['token' => $token])
            ->value('user_id');

        $user_info = User::where(['id' => $user_id, 'del' => 0])
            ->find();
        if (empty($user_info)) {
            return '用户不存在';
        }
        if ($user_info['disable'] == 1) {
            return '用户被禁用';
        }
        return true;
    }


}