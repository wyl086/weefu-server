<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Config;
use think\facade\Route;


// 手机h5页面路由
Route::rule('mobile/:any', function () {
    $isOpen = \app\common\server\ConfigServer::get('h5', 'is_open', 1);
    if(!$isOpen) {
        return '';
    }
    Config::set(['app_trace' => false]);
    return view(app()->getRootPath() . 'public/mobile/index.html');
})->pattern(['any' => '\w+']);


// PC商城端
Route::rule('pc/:any', function () {
    $isOpen = \app\common\server\ConfigServer::get('pc', 'is_open', 1);
    if(!$isOpen) {
        return '';
    }
    Config::set(['app_trace' => false]);
    return view(app()->getRootPath() . 'public/pc/index.html');
})->pattern(['any' => '\w+']);


// 商家移动端
Route::rule('business/:any', function () {
    Config::set(['app_trace' => false]);
    return view(app()->getRootPath() . 'public/business/index.html');
})->pattern(['any' => '\w+']);


// 客服
Route::rule('kefu/:any', function () {
    Config::set(['app_trace' => false]);
    return view(app()->getRootPath() . 'public/kefu/index.html');
})->pattern(['any' => '\w+']);

//定时任务
Route::rule('crontab', function () {
    \think\facade\Console::call('crontab');
});
