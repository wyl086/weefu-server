<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\api\controller;

use app\common\basics\Api;
use app\common\logic\SmsLogic;
use app\api\validate\SmsSend;
use app\common\server\JsonServer;

class Sms extends Api
{
    public $like_not_need_login = ['send'];

    /**
     * 发送短信
     */
    public function send()
    {
        $client = $this->client;
        $mobile = $this->request->post('mobile');
        $key = $this->request->post('key');
        (new SmsSend())->goCheck('', ['mobile' => $mobile, 'key' => $key,'client' => $client,'user_id' => $this->user_id]);
        $result = SmsLogic::send($mobile, $key, $this->user_id);
        if (true === $result) {
            return JsonServer::success('发送成功');
        }
        return JsonServer::error('发送失败');
    }
}