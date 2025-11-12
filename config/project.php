<?php
return [
    'file_domain' => env('project.file_domain', 'www.multshop.com'),
    'sms' => env('project.sms', true),
    'version' => env('project.version', '2.5.5.20240430'),
    'front_version' => env('project.version', '2.5.5.20240430'),
    // 缓存过期时间 7天 24*60*60 = 86400 秒
    'token_expire_time' => 86400,

    // 文件上传限制 (图片)
    'file_image' => [
        'jpg', 'png', 'gif', 'jpeg', 'webp', 'ico',
    ],

    // 文件上传限制 (视频)
    'file_video' => [
        'wmv', 'avi', 'mpg', 'mpeg', '3gp', 'mov', 'mp4', 'flv', 'f4v', 'rmvb', 'mkv'
    ],
];