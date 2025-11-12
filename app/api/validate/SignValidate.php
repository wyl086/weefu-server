<?php

namespace app\api\validate;

use app\common\basics\Validate;
use app\common\model\sign_daily\UserSign;

/**
 * 签到验证
 * Class Sign
 * @package app\api\validate
 */
class SignValidate extends Validate
{
    protected $rule = [
        'user_id' => 'checkSign',
    ];

    public function checkSign($value, $data, $rule)
    {
        $today = UserSign::where(['del' => 0, 'user_id' => $value])
            ->whereTime('sign_time', 'today')
            ->findOrEmpty();

        if (!$today->isEmpty()) {
            return '您今天已签到过了';
        }

        return true;
    }
}