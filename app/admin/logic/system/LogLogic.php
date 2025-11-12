<?php
namespace app\admin\logic\system;

use app\common\basics\Logic;
use app\common\model\system\SystemLog;

class LogLogic extends Logic
{
    public static function lists($get)
    {

        $where = [];
        if (isset($get['account']) && $get['account']) {
            $where[] = ['account', 'like', "%{$get['account']}%"];
        }

        if (isset($get['uri']) && $get['uri']) {
            $where[] = ['uri', 'like', "%{$get['uri']}%"];
        }

        if (isset($get['type']) && $get['type']) {
            $where[] = ['type', '=', strtolower($get['type'])];
        }

        if (isset($get['ip']) && $get['ip']) {
            $where[] = ['ip', 'like', "%{$get['ip']}%"];
        }

        if (isset($get['start_time']) && !empty($get['start_time'])) {
            $where[] = ['create_time', '>=', strtotime($get['start_time'])];
        }

        if (isset($get['end_time']) && !empty($get['end_time'])) {
            $where[] = ['create_time', '<=', strtotime($get['end_time'])];
        }

        $lists = SystemLog::where($where)
            ->page($get['page'], $get['limit'])
            ->order(['id' => 'desc'])
            ->select()
            ->toArray();

        foreach ($lists as $k => $v) {
//            $lists[$k]['create_time_str'] = date('Y-m-d H:i:s', $v['create_time']);
            $lists[$k]['param'] = str_replace([" ", "　", "\t", "\n", "\r"], '', $v['param']);
        }

        $count = SystemLog::where($where)->count();

        return ['lists' => $lists, 'count' => $count];
    }
}