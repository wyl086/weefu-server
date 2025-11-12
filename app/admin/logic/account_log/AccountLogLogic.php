<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\admin\logic\account_log;
use app\common\model\AccountLog;
use think\Db;

class AccountLogLogic{
    /**
     * 获取变动类型描述
     */
    public static function getTypeDesc($typeArr)
    {
        $temp = [];
        foreach($typeArr as $type) {
            $temp[] = ['id' => $type, 'name' => AccountLog::getAcccountDesc($type)];
        }
        return $temp;
    }

    /**
     * 获取常用时间段
     */
    public static function getTime(){
        $today_date = date('Y-m-d', time());
        $today_start = $today_date.' 00:00:00';
        $today_end = $today_date.' 23:59:59';
        $today = [$today_start, $today_end];

        $yesterday_date = date('Y-m-d', strtotime('-1 day'));
        $yesterday_start = $yesterday_date . ' 00:00:00';
        $yesterday_end = $yesterday_date . ' 23:59:59';
        $yesterday = [$yesterday_start, $yesterday_end];

        $ago7_date = date('Y-m-d', strtotime('-7 day'));
        $ago7_start = $ago7_date . ' 00:00:00';
//        $ago7_end = $ago7_date . ' 23:59:59';
        $ago7_end = $today_date . ' 23:59:59';
        $ago7 = [$ago7_start, $ago7_end];

        $ago30_date = date('Y-m-d', strtotime('-30 day'));
        $ago30_start = $ago30_date . ' 00:00:00';
//        $ago30_end = $ago30_date . ' 23:59:59';
        $ago30_end = $today_date . ' 23:59:59';
        $ago30 = [$ago30_start, $ago30_end];

        $time = [
            'today'         => $today,
            'yesterday'     => $yesterday,
            'days_ago7'     => $ago7,
            'days_ago30'    => $ago30,
        ];

        return $time;
    }

    public static function growthLists($get)
    {
        $where = [
          ['al.source_type', 'in', AccountLog::growth_change]
        ];

        if(isset($get['keyword']) && !empty($get['keyword'])) {
            $where[] = ['u.'.$get['keyword_type'], '=',$get['keyword']];
        }
        if(isset($get['order_source']) && !empty($get['order_source'])) {
            $where[] = ['al.source_type', '=', $get['order_source']];
        }

        if(isset($get['start_time']) && !empty($get['start_time'])) {
            $where[] = ['al.create_time', '>=', strtotime($get['start_time'])];
        }

        if(isset($get['end_time']) && !empty($get['end_time'])) {
            $where[] = ['al.create_time', '<=', strtotime($get['end_time'])];
        }

        $count = AccountLog::alias('al')
            ->leftJoin('user u', 'u.id=al.user_id')
            ->where($where)
            ->count();
        $lists = AccountLog::alias('al')
            ->field('al.*, u.nickname,u.sn,u.mobile')
            ->leftJoin('user u', 'u.id=al.user_id')
            ->where($where)
            ->order('create_time', 'desc')
            ->page($get['page'], $get['limit'])
            ->select()
            ->toArray();
        return [
            'count' => $count,
            'lists' => $lists
        ];
    }
}
