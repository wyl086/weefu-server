<?php
namespace app\admin\logic\system;

use app\common\basics\Logic;
use app\common\model\system\DevCrontab;
use Cron\CronExpression;
use app\common\server\system\CrontabServer;

class CrontabLogic extends Logic
{
    public static function lists()
    {
        $lists = DevCrontab::field('id,name,type,type as type_desc,command,last_time as last_time_str,expression,parameter,status,error,time,max_time,system')
            ->order(['system' => 'desc'])->select()->toArray();

        return ['count' => count($lists), 'lists' => $lists];
    }

    /**
     * 获取接下来几次执行时间
     * @param $get
     * @return array
     */
    public static function expression($get)
    {
        if (CronExpression::isValidExpression($get['expression']) === false) {
            return [['time' => 0, 'date' => '规则设置错误']];
        }
        $cron_expression = CronExpression::factory($get['expression']);
        try {
            $res = $cron_expression->getMultipleRunDates(5);
        } catch (Exception $e) {
            return [['time' => 0, 'date' => '规则设置错误']];
        }
        $res = json_decode(json_encode($res), true);
        $lists = [];
        foreach ($res as $k => $v) {
            $lists[$k]['time'] = $k + 1;
            $lists[$k]['date'] = str_replace('.000000', '', $v['date']);
        }
        $lists[] = ['time' => 'x', 'date' => '……'];
        return $lists;
    }

    /**
     * 添加任务
     * @param $post
     * @return int|string
     */
    public static function add($post)
    {
        try{
            $data = [
                'name' => $post['name'],
                'type' => $post['type'],
                'remark' => $post['remark'],
                'command' => $post['command'],
                'parameter' => $post['parameter'],
                'status' => $post['status'],
                'expression' => $post['expression'],
                'create_time' => time()
            ];
            DevCrontab::create($data);

//            if ($post['status'] == 1) {
//                (new CrontabServer())->run(false);
//            }
            return true;
        }catch(\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    public static function info($id)
    {
        $info = DevCrontab::where(['id' => $id])->findOrEmpty();
        if($info->isEmpty()) {
            return [];
        }
        return $info->toArray();
    }

    public static function edit($post)
    {
        try{
            $data = [
                'name' => $post['name'],
                'type' => $post['type'],
                'remark' => $post['remark'],
                'command' => $post['command'],
                'parameter' => $post['parameter'],
                'status' => $post['status'],
                'expression' => $post['expression'],
                'update_time' => time()
            ];
            DevCrontab::where(['id' => $post['id']])->update($data);
//            if ($post['status'] == 1) {
//                (new CrontabServer())->run(false);
//            }
            return true;
        }catch(\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    public static function operation($operation, $id)
    {
        try {
            $cron = DevCrontab::where(['id' => $id])->findOrEmpty();
            if($cron->isEmpty()) {
                throw new \think\Exception('任务不存在');
            }
            if ($cron['type'] == 1 && CronExpression::isValidExpression($cron['expression']) === false) {
                throw  new  Exception("规则设置错误"); //定时任务运行规则错误，不执行
            }

            switch ($operation) {
                case  'start':
                case 'restart':
                    DevCrontab::where(['id' => $id])->update(['status' => 1]);
                    break;
                case 'stop':
                    DevCrontab::where(['id' => $id])->update(['status' => 2]);
                default;
            }

//            $count = DevCrontab::where(['status' => 1])->count();
//
//            $crontab_server = new CrontabServer();
//            if ($count == 0) {
//                $crontab_server->run(true);
//            } else {
//                $crontab_server->run(false);
//            }
            return true;
        } catch (Exception $e) {
            DevCrontab::where(['id' => $id])->update(['status' => 3, 'error' => $e->getMessage()]);
            self::$error = $e->getMessage();
            return false;
        }
    }

    public static function del($id)
    {
        try{
            $system = DevCrontab::where(['id' => $id])->value('system');
            if ($system === 1) { // 系统任务不允许删除
                return false;
            }
            DevCrontab::where(['id' => $id])->delete();

//            (new CrontabServer())->run(false);

            return true;
        }catch(\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }
}