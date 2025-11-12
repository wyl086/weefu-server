<?php
namespace app\common\enum;


class BargainEnum
{
    const STATUS_NORMAL = 0;//进行中
    const STATUS_SUCCESS = 1;//成功
    const STATUS_FAIL = 2;//失败
    const STATUS_FINISH = 3;//提前结束

    const TO_BE_REVIEWED = 0; //待审核
    const AUDIT_PASS = 1; //审核通过
    const AUDIT_REFUND = 2; //审核拒绝
}