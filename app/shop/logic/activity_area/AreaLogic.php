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
namespace app\shop\logic\activity_area;
use app\common\basics\Logic;
use app\common\server\JsonServer;
use think\facade\Db;
use app\common\model\activity_area\ActivityArea;
use app\common\model\activity_area\ActivityAreaGoods;
use app\common\server\UrlServer;


class  AreaLogic extends Logic {

    /**
     * Notes:活动专区列表
     * @param $get
     * @return array
     * @author: cjhao 2021/4/15 16:25
     */
    public static function lists($get){
        $where[] = ['del','=',0];
        $lists = ActivityArea::where($where)
            ->page($get['page'],$get['limit'])
            ->select();

        $count = ActivityArea::where($where)
            ->page($get['page'],$get['limit'])
            ->count();

        return ['count'=>$count,'lists'=>$lists];
    }

    /**
     * Notes:添加活动专区
     * @param $post
     * @return int|string
     * @author: cjhao 2021/4/15 17:25
     */
    public static function add($post){
        $post['create_time'] = time();
        if($post['status'] == 'on'){
            $post['status'] = 1; //专区显示
        }else{
            $post['status'] = 0; //专区隐藏
        }
        $post['image'] = UrlServer::setFileUrl($post['image']);
        return ActivityArea::insert($post);
    }

    /**
     * Notes:获取活动专区
     * @param $id
     * @return array|\think\Model|null
     * @author: cjhao 2021/4/15 17:29
     */
    public static function getActivityArea($id){
        return ActivityArea::where(['id'=>$id,'del'=>0])->find();
    }

    /***
     * 获取所有活动专区
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getActivityAreaAll(){
        return ActivityArea::where(['del'=>0])
            ->select();
    }

    /***
     * @param $post
     * @return ActivityArea
     */
    public static function edit($post){
        $post['image'] = UrlServer::setFileUrl($post['image']);
        return ActivityArea::update($post);
    }

    /***
     * @param $id
     * @return ActivityArea
     */
    public static function del($id){

        return ActivityArea::update(['del'=>1,'id'=>$id]);
    }
}