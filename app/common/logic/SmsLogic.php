<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\common\logic;

use app\common\basics\Logic;
use app\common\enum\NoticeEnum;
use app\common\model\SmsLog;

/**
 * 短信
 * Class SmsLogic
 * @package app\common\logic
 */
class SmsLogic extends Logic
{
    protected static $expire_time = 300; //验证码有效时间
    protected static $check_num = 5; //验证次数


    /**
     * Notes: 发送验证码
     * @param $mobile
     * @param $scene
     * @param int $user_id
     * @author 段誉(2021/6/23 7:17)
     * @return string
     */
    public static function send($mobile, $scene, $user_id = 0)
    {
        try {
            $code = create_sms_code(4);
            $send_data = [
                'scene' => NoticeEnum::SMS_SCENE[$scene],
                'mobile' => $mobile,
                'params' => ['code' => $code]
            ];

            if (!empty($user_id)) {
                $send_data['user_id'] = $user_id;
            }
            $res = event('Notice', $send_data);
            if (false === $res) {
                throw new \Exception('发送失败');
            }
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Notes: 验证短信验证码是否正确
     * @param $message_key
     * @param $mobile
     * @param int $code
     * @author 段誉(2021/6/23 2:53)
     * @return bool
     * @remark 有效时间,检测次数内短信验证码是否正确
     */
    public static function check($message_key, $mobile, $code = 0)
    {
        $log = SmsLog::where([
            'mobile' => $mobile,
            'message_key' => $message_key,
            'is_verify' => 0
        ])->order('id desc')->find();

        if (empty($log)) {
            self::$error = '验证码错误';
            return false;
        }

        $diff_time = time() - ($log->getData('create_time'));

        if ($diff_time < self::$expire_time && $log['check_num'] <= self::$check_num) {
            $check_num = $log['check_num'] + 1;
            if ($log['code'] == $code) {
                SmsLog::where(['id' => $log['id']])->update([
                    'is_verify' => 1,
                    'check_num' => $check_num,
                    'update_time' => time()
                ]);
                return true;
            }
            SmsLog::where(['id' => $log['id']])->update([
                'check_num' => $check_num,
                'update_time' => time()
            ]);
            self::$error = '验证码错误！';
            return false;
        }

        self::$error = '验证码错误或失败次数过多';
        return false;
    }
}