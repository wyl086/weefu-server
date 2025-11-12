<?php


namespace app\shop\controller;


use think\facade\View;

class Dispatch
{
    /**
     * @Notes: 错误提示
     * @Author: 张无忌
     * @param $msg
     * @return \think\response\View
     */
    public static function dispatch_error($msg)
    {
        View::assign('msg', $msg);
        return view('dispatch_error');
    }
}