<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\admin\logic\decoration;
use app\common\basics\Logic;
use think\facade\Db;

class BottomNavLogic extends Logic{

    /**
     * Notes:获取底部导航
     * @param $get
     * @author: cjhao 2021/4/22 11:45
     */
    public static function lists(){
        $where[] = ['del','=',0];

        $lists = Db::name('bottom_nav')
                ->where($where)
                ->select();

        return ['lists'=>$lists];
    }

    /**
     * Notes:获取底部导航
     * @param $id
     * @return array|\think\Model|null
     * @author: cjhao 2021/4/22 15:00
     */
    public static function getBootomNav($id){
        return Db::name('bottom_nav')
                ->where(['id'=>$id,'del'=>0])
                ->find();
    }

    /**
     * Notes:更新底部导航
     * @param $post
     * @return int
     * @author: cjhao 2021/4/22 15:00
     */
    public static function edit($post){
        return Db::name('bottom_nav')
                ->where(['id'=>$post['id']])
                ->update($post);
    }
}