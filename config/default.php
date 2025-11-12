<?php
return [
    'copyright' => [
        'company_name' => 'Copyright © 2019-2021 多商户商城系统',
        'number' => '京ICP证000000号',
        'link' => 'http://www.beian.gov.cn/portal/index.do'
    ],
    'website' => [
        'name' => 'multshop',
        'web_favicon' => '/static/common/ico/default.png', //浏览器图标

        'user_image' => '/static/common/image/default/user.png',//用户默认头像
        'goods_image' => '/static/common/image/default/main.png',//商品主图
        'system_notice' => '/static/common/image/default/system_notice.png',//系统通知图标
        'earning_notice' => '/static/common/image/default/earning_notice.png',//收益通知图标
        'client_login_logo' => '/static/common/image/default/client_login_logo.png',//移动端登录logo

        'pc_client_login_logo' => '/static/common/image/default/pc_login.jpg',//PC端登录logo
    ],
    //平台后台设置
    'website_platform' => [
        'platform_admin_logo' => '/static/common/image/default/platform_admin_logo.png',//主页左上角logo
        'platform_login_image' => '/static/common/image/default/login_form_img.png',
        'platform_login_title' => 'multshop管理后台',
        'platform_login_logo' => '/static/common/image/default/platform_login_logo.png',
    ],
    //商家后台设置
    'website_shop' => [
        'shop_admin_logo' => '/static/common/image/default/shop_admin_logo.png',//主页左上角logo
        'shop_login_image' => '/static/common/image/default/login_form_img.png',
        'shop_login_title' => 'multshop管理后台',
        'shop_login_logo' => '/static/common/image/default/shop_login_logo.png',
    ],
    'decoration' => [ // 装修
        // 商品分类页布局图片
        'category_layout' => [
            1 => '/static/common/image/default/category_layout1.png',
            2 => '/static/common/image/default/category_layout2.png',
            3 => '/static/common/image/default/category_layout3.png',
            4 => '/static/common/image/default/category_layout4.png'
        ],
        'category_layout_tips' => [
            1 => '一级布局，适合商品分类较少情形',
            2 => '一级布局，适合商品分类较少情形',
            3 => '二级布局，适合商品分类适中情形',
            4 => '三级布局，适合商品分类丰富情形'
        ]
    ],
    'user_level' => [
        'intro' => "1、会员可通过注册或消费获得成长值\n2、不同会员等级可享受不同的折扣权益\n3、平台拥有活动最终解释权"
    ],
    // 在线客服前缀
    'websocket_prefix' => 'socket_',
    // 客服默认头像
    'kefu_avatar' => '/static/common/image/default/kefu.png',
    // 种草社区
    'community' => [
        'user_bg' => '/static/common/image/default/community_user_bg.png',
    ]

];