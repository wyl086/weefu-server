<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\admin\logic\common;
use app\common\basics\Logic;
use app\common\model\goods\Goods;
use think\facade\Db;

class goodsLogic extends Logic{

    /**
     * Notes:获取商品列表
     * @param $get
     * @return array
     * @author: cjhao 2021/4/21 14:44
     */
    public static function selectGoods($get){
        $where[] = ['del','=',0];

        if(isset($get['keyword']) && $get['keyword']){
            $where[] = ['name','like','%'.$get['keyword'].'%'];
        }

        $lists = Goods::where($where)
                ->paginate(['list_rows'=>$get['limit'],'page'=>$get['page']]);

        $list = $lists->items();
        foreach ($list as $key => $goods){
            $price = $goods['min_price'].'~'.$goods['max_price'];

            if($goods['min_price'] !== $goods['max_price']){
                $price = $goods['min_price'];
            }

            $list[$key]['price'] = $price;
        }

        $count = $lists->total();

        return ['count'=>$count,'lists'=>$list];
    }
}