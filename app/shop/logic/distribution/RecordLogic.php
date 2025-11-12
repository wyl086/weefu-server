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

namespace app\shop\logic\distribution;

use app\common\basics\Logic;
use app\common\model\distribution\DistributionOrderGoods;

class RecordLogic extends Logic
{
    public static function lists($get)
    {
        $where = [
            ['dog.shop_id', '=', $get['shop_id']]
        ];
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