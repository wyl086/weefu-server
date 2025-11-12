<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\common\model\bargain;


use app\common\basics\Models;
use app\common\model\user\User;

/**
 * 砍价活动 助力模型
 * Class BargainKnife
 * @Author 张无忌
 * @package app\common\model
 */
class BargainKnife extends Models
{
    /**
     * @notes 关联用户模型
     * @return \think\model\relation\HasOne
     * @author suny
     * @date 2021/7/13 6:40 下午
     */
    public function user()
    {

        return $this->hasOne(user::class, 'id', 'user_id')
            ->field('id,sn,nickname,avatar,level,mobile,sex,create_time');
    }
}