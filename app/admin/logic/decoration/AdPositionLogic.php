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
use app\common\enum\AdEnum;
use app\common\enum\AdPositionEnum;
use app\common\basics\Logic;
use think\facade\Db;

class AdPositionLogic extends Logic{

    /**
     * Notes:广告位列表
     * @param $get
     * @return array
     * @author: cjhao 2021/4/17 14:23
     */
    public static function lists($get){
        $where[] = ['del','=',0];
        $where[] = ['terminal','=',$get['terminal']];

        $lists = Db::name('ad_position')
                ->where($where)
                ->withAttr('terminal_desc',function ($value,$data){
                    return AdEnum::getTerminal($data['terminal']);
                })
                ->paginate(['list_rows'=>$get['limit'],'page'=>$get['page']]);

        $list = $lists->items();
        $count = $lists->total();

        return ['count'=>$count,'lists'=>$list];

    }

    /**
     * Notes:添加广告
     * @param $post
     * @return int|string
     * @author: cjhao 2021/4/17 14:51
     */
    public static function add($post){
        $post['attr'] = 1;
        $post['create_time'] = time();
        if(!empty(trim($post['size']))) {
            $tempArr = explode('*', $post['size']);
            $post['width'] = $tempArr[0];
            $post['height'] = $tempArr[1];
        }
        unset($post['size']);
        return Db::name('ad_position')->insert($post);
    }


    /**
     * Notes:编辑广告位
     * @param $post
     * @return int
     * @author: cjhao 2021/4/17 14:52
     */
    public static function edit($post){
        if(!empty($post['size'])) {
            $tempArr = explode('*', $post['size']);
            $post['width'] = $tempArr[0];
            $post['height'] = $tempArr[1];
            unset($post['size']);
        }else{
            $post['width'] = '';
            $post['height'] = '';
            unset($post['size']);
        }
        $post['update_time'] = time();
        return Db::name('ad_position')->update($post,['id'=>$post['id']]);
    }

    /**
     * Notes:获取广告位
     * @param $id
     * @return \think\Collection
     * @author: cjhao 2021/4/17 14:56
     */
    public static function getPosition($id){
        $info =  Db::name('ad_position')
            ->where(['id'=>$id,'del'=>0])
            ->find();
        if(!empty($info['width']) && !empty($info['height'])) {
            $info['size'] = $info['width'] . '*' . $info['height'];
        }else{
            $info['size'] = '';
        }
        return $info;

    }

    /**
     * Notes:删除广告位
     * @param $id
     * @return int
     * @author: cjhao 2021/4/19 11:36
     */
    public static function del($id){
        return Db::name('ad_position')
                ->where(['id'=>$id])
                ->update(['del'=>1]);
    }

    /**
     * Notes:切换广告位状态
     * @param $post
     * @return int
     * @author: cjhao 2021/4/19 11:38
     */
    public static function swtichStatus($post){
        return Db::name('ad_position')
                ->update($post);
    }


}
