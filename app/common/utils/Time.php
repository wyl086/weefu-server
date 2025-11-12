<?php
namespace app\common\utils;

class Time
{
    /**
     * 获取常用时间段
     * 昨天、今天、最近7天、最近30天
     */
    public static function getTime(){
        $yesterday_date = date('Y-m-d', strtotime('-1 day'));
        $yesterday_start = $yesterday_date . ' 00:00:00';
        $yesterday_end = $yesterday_date . ' 23:59:59';
        $yesterday = [$yesterday_start, $yesterday_end];

        $today_date = date('Y-m-d', time());
        $today_start = $today_date.' 00:00:00';
        $today_end = $today_date.' 23:59:59';
        $today = [$today_start, $today_end];

        $ago7_date = date('Y-m-d', strtotime('-6 day'));
        $ago7_start = $ago7_date . ' 00:00:00';
        $ago7 = [$ago7_start, $today_end];

        $ago30_date = date('Y-m-d', strtotime('-29 day'));
        $ago30_start = $ago30_date . ' 00:00:00';
        $ago30 = [$ago30_start, $today_end];

        $time = [
            'yesterday'     => $yesterday,
            'today'         => $today,
            'days_ago7'     => $ago7,
            'days_ago30'    => $ago30,
        ];

        return $time;
    }
}