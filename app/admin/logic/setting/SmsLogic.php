<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\admin\logic\setting;

use app\common\basics\Logic;
use app\common\enum\NoticeEnum;
use app\common\model\SmsConfig;
use app\common\model\SmsLog;
use app\common\server\ConfigServer;


class SmsLogic extends Logic
{

    /**
     * Notes: 短信配置列表
     * @author 段誉(2021/6/7 15:54)
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function configLists()
    {
        $default = ConfigServer::get('sms_driver', 'default', '');
        $lists = [
            [
                'name'   => '阿里云短信',
                'path'   => '存储在本地服务器',
                'engine' => 'ali',
                'status' => $default == 'ali' ? 1 : 0
            ],
            [
                'name'   => '腾讯云短信',
                'path'   => '存储在七牛云，请前往七牛云开通存储服务',
                'engine' => 'tc',
                'status' => $default == 'tc' ? 1 : 0
            ]
        ];
        return ['count' => count($lists), 'lists' => $lists];
    }


    /**
     * Notes: 设置短信配置
     * @param $post
     * @author 段誉(2021/6/21 23:33)
     * @return bool
     */
    public static function setConfig($post)
    {
        $engine = $post['engine'] ?? '';
        try{
            if ($engine == 'ali') {
                ConfigServer::set('sms_engine', 'ali', [
                    'sign'          => $post['sign'],
                    'app_key'       => $post['app_key'],
                    'secret_key'    => $post['secret_key'],
                ]);
            } elseif ($engine == 'tc') {
                ConfigServer::set('sms_engine', 'tc', [
                    'sign'          => $post['sign'],
                    'app_id'        => $post['app_id'],
                    'app_key'       => $post['app_key'],
                    'secret_key'    => $post['secret_key'],
                ]);
            } else {
                throw new \Exception('设置短信渠道不存在');
            }

            self::setDefaultByStatus($post['status'] ?? 'off', $engine);
            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * Notes: 根据原来的短信引擎更新短信默认
     * @param $status
     * @param $now_engine
     * @author 段誉(2021/6/22 0:22)
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function setDefaultByStatus($status, $now_engine)
    {
        $last_engine = ConfigServer::get('sms_driver', 'default', '');

        if ($status == 'on') {
            ConfigServer::set('sms_driver', 'default', $now_engine);
        }

        if ($status != 'on' && $last_engine == $now_engine) {
            ConfigServer::set('sms_driver', 'default', '');
        }
    }


    /**
     * Notes: 获取短信配置
     * @param $engine
     * @author 段誉(2021/6/22 0:10)
     * @return array|bool|mixed|null
     */
    public static function getConfigInfo($engine)
    {
        switch ($engine) {
            case 'ali':
                $info = ConfigServer::get('sms_engine', 'ali', [
                    'sign'          => '',
                    'app_key'       => '',
                    'secret_key'    => '',
                ]);
                break;
            case 'tc':
                $info = ConfigServer::get('sms_engine', 'tc', [
                    'sign'          => '',
                    'app_id'        => '',
                    'app_key'       => '',
                    'secret_key'    => '',
                ]);
                break;
            default:
                $info = [];
        }

        if (empty($info)) {
            return false;
        }
        $info['default_engine'] = ConfigServer::get('sms_driver', 'default', '');
        return $info;
    }


    //************************************************短信发送记录*************************************************************


    /**
     * Notes: 日志列表
     * @param $get
     * @author 段誉(2021/6/7 15:54)
     * @return array
     * @throws \think\db\exception\DbException
     */
    public static function logLists($get)
    {
        $where = [
            ['message_key', 'in', NoticeEnum::SMS_SCENE]
        ];
        if (isset($get['name']) && $get['name']) {
            $where[] = ['d.name', 'like', '%' . $get['name'] . '%'];
        }
        if (isset($get['mobile']) && $get['mobile']) {
            $where[] = ['mobile', 'like', '%' . $get['mobile'] . '%'];
        }
        if (isset($get['send_status']) && $get['send_status'] != '') {
            $where[] = ['send_status', '=', $get['send_status']];
        }
        if (isset($get['start_time']) && $get['start_time']) {
            $where[] = ['create_time', '>=', strtotime($get['start_time'])];
        }
        if (isset($get['end_time']) && $get['end_time']) {
            $where[] = ['create_time', '<=', strtotime($get['end_time'])];
        }

        $lists = SmsLog::where($where)
            ->order('id desc')
            ->paginate([
                'page'      => $get['page'],
                'list_rows' => $get['limit'],
                'var_page' => 'page'
            ])->toArray();

        return ['count' => $lists['total'], 'lists' => $lists['data']];
    }

    /**
     * Notes: 短信详情
     * @param $id
     * @author 段誉(2021/6/7 15:53)
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function detail($id)
    {
        $log = SmsLog::where([
            ['message_key', 'in',NoticeEnum::SMS_SCENE],
            ['id', '=', $id]
        ])->withAttr('name', function ($value, $data){
            return NoticeEnum::getSceneDesc($data['message_key']);
        })->find();
        return $log;
    }

}