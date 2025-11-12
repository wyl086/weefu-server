<?php
// 事件定义文件

return [
    'bind'      => [
    ],

    'listen'    => [
        'AppInit'   => [],
        'HttpRun'   => [],
        'HttpEnd'   => [],
        'LogLevel'  => [],
        'LogWrite'  => [],
        'UserStat'  => ['app\common\listener\UserStat'], // 记录统计信息(用户访问量)
        'ShopStat'  => ['app\common\listener\ShopStat'], // 记录统计信息(访问商铺用户量)
        'Notice'    => ['app\common\listener\Notice'], // 通知
        'Footprint' => ['app\common\listener\Footprint'], // 访问足迹
        'AfterCancelOrder' => ['app\common\listener\AfterCancelOrder'], //  取消订单

        // swoole 相关事件
        'swoole.start' => ['app\common\listener\websocket\Start'], // 开启
        'swoole.websocket.login' => ['app\common\listener\websocket\Login'], // 登录事件
        'swoole.websocket.chat' => ['app\common\listener\websocket\Chat'], // 对话事件
        'swoole.websocket.transfer' => ['app\common\listener\websocket\Transfer'], // 转接事件
        'swoole.websocket.close' => ['app\common\listener\websocket\Close'], // 关闭事件
        'swoole.websocket.user_online' => ['app\common\listener\websocket\UserOnline'], // 上线事件
        'swoole.websocket.read' => ['app\common\listener\websocket\Read'], // 已读事件

        // 订单打印
        'Printer' => ['app\common\listener\Printer'],
        // 商品下架或删除, 更新商品收藏状态
        'UpdateCollect' => ['app\common\listener\UpdateCollect'],

    ],

    'subscribe' => [
    ],
];
