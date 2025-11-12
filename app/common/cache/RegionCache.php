<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\common\cache;


use think\facade\Db;

class RegionCache extends CacheBase
{

    public function setTag()
    {
        return 'region';
    }

    /**
     * 子类实现查出数据
     * @return mixed
     */
    public function setData()
    {
        return Db::name('dev_region')
            ->column('name', 'id');
    }
}