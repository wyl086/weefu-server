<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\admin\logic\finance;


use app\common\basics\Logic;
use app\common\model\AccountLog;
use app\common\server\ExportExcelServer;

class IntegralLogic extends Logic
{
    /**
     * @notes 积分明细
     * @param $get
     * @return array
     * @author ljj
     * @date 2022/2/22 5:59 下午
     */
    public static function integral($get, $is_export = false)
    {
        $where[] = ['source_type','in',AccountLog::integral_change];
        //用户信息
        if (isset($get['user_info']) && $get['user_info'] != '') {
            $where[] = ['u.sn|u.nickname', '=', $get['user_info']];
        }
        //开始时间
        if (isset($get['start_time']) && $get['start_time'] != '') {
            $where[] = ['al.create_time', '>=', strtotime($get['start_time'])];
        }
        //结束时间
        if (isset($get['end_time']) && $get['end_time'] != '') {
            $where[] = ['al.create_time', '<=', strtotime($get['end_time'])];
        }

        // 导出
        if (true === $is_export) {
            return self::export($where);
        }

        $lists = AccountLog::alias('al')
            ->join('user u', 'al.user_id = u.id')
            ->field('al.id,al.user_id,al.source_type,al.change_amount,al.left_amount,al.remark,al.change_type,al.create_time,u.sn as user_sn,u.nickname')
            ->where($where)
            ->page($get['page'], $get['limit'])
            ->order('id','desc')
            ->select()
            ->toArray();

        foreach ($lists as &$list) {
            $list['change_amount'] = $list['change_type'] == 1 ? '+'.$list['change_amount'] : '-'.$list['change_amount'];
        }


        $count = AccountLog::alias('al')
            ->join('user u', 'al.user_id = u.id')
            ->where($where)
            ->count();

        return ['count' => $count, 'lists' => $lists];
    }


    /**
     * @notes 导出商家账户明细Excel
     * @param array $where
     * @return array|false
     * @author 段誉
     * @date 2022/4/24 10:10
     */
    public static function export($where)
    {
        try {
            $lists = AccountLog::alias('al')
                ->join('user u', 'al.user_id = u.id')
                ->field('al.id,al.user_id,al.source_type,al.change_amount,al.left_amount,al.remark,al.change_type,al.create_time,u.sn as user_sn,u.nickname')
                ->where($where)
                ->order('id','desc')
                ->select()
                ->toArray();

            foreach ($lists as &$list) {
                $list['change_amount'] = $list['change_type'] == 1 ? '+'.$list['change_amount'] : '-'.$list['change_amount'];
            }

            $excelFields = [
                'user_sn' => '用户编号',
                'nickname' => '会员信息',
                'source_type' => '变动类型',
                'change_amount' => '积分变动',
                'left_amount' => '剩余积分',
                'remark' => '备注',
                'create_time' => '变动时间',
            ];

            $export = new ExportExcelServer();
            $export->setFileName('积分明细');
            $result = $export->createExcel($excelFields, $lists);

            return ['url' => $result];

        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }
}