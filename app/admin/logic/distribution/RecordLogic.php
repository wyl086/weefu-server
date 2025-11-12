<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\admin\logic\distribution;

use app\common\basics\Logic;
use app\common\model\distribution\DistributionOrderGoods;

class RecordLogic extends Logic
{
    public static function lists($get)
    {
        $where = [];
        // 搜索
        if(!empty($get['keyword'])) {
            $fieldDesc = '';
            switch($get['keyword_type']) {
                case 'order_sn':
                    $fieldDesc = 'o.order_sn';
                    break;
                case 'user_nickname':
                    $fieldDesc = 'u.nickname';
                    break;
                case 'user_sn':
                    $fieldDesc = 'u.sn';
                    break;
                case 'user_mobile':
                    $fieldDesc = 'u.mobile';
                    break;
            }
            $where[] = [$fieldDesc, '=', trim($get['keyword'])];
        }
        // 佣金状态
        if(!empty($get['status'])) {
            $where[] = ['dog.status', '=', $get['status']];
        }
        // 记录时间
        if(!empty($get['start_time'])) {
            $where[] = ['dog.create_time', '>=', strtotime($get['start_time'])];
        }
        if(!empty($get['end_time'])) {
            $where[] = ['dog.create_time', '<=', strtotime($get['end_time'])];
        }
        $lists = DistributionOrderGoods::alias('dog')
            ->field('dog.money, dog.status as status_desc,dog.create_time as distribution_create_time,u.nickname as user_nickname,u.sn as user_sn,u.mobile as user_mobile,og.total_pay_price,o.order_sn')
            ->leftJoin('user u', 'u.id=dog.user_id')
            ->leftJoin('order_goods og', 'og.id=dog.order_goods_id')
            ->leftJoin('order o', 'o.id=og.order_id')
            ->where($where)
            ->order('dog.create_time', 'desc')
            ->page($get['page'], $get['limit'])
            ->select()
            ->toArray();

        $count = DistributionOrderGoods::alias('dog')
            ->field('dog.money, dog.status as status_desc,dog.create_time as distribution_create_time,u.nickname as user_nickname,u.sn as user_sn,u.mobile as user_mobile,og.total_pay_price,o.order_sn')
            ->leftJoin('user u', 'u.id=dog.user_id')
            ->leftJoin('order_goods og', 'og.id=dog.order_goods_id')
            ->leftJoin('order o', 'o.id=og.order_id')
            ->where($where)
            ->count();

        return [
            'count' => $count,
            'lists' => $lists
        ];
    }
}