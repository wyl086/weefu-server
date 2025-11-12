<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\common\basics;


/**
 * 逻辑层基类
 * Class Logic
 * @Author FZR
 * @package app\common\basics
 */
abstract class Logic
{
    /**
     * 错误信息
     * @var string
     */
    protected static $error;

    /**
     * 返回错误信息
     * @access public
     * @return string|array
     */
    public static function getError()
    {
        return self::$error;
    }
}