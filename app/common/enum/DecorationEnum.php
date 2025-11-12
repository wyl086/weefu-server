<?php
namespace app\common\enum;

class DecorationEnum
{
    // 商品分类页布局图片
    const CATEGORY_LAYOUT = [
        1 => '/static/common/image/default/category_layout1.png',
        2 => '/static/common/image/default/category_layout2.png',
        3 => '/static/common/image/default/category_layout3.png',
        4 => '/static/common/image/default/category_layout4.png'
    ];

    // 提示消息
    const CATEGORY_LAYOUT_TIPS = [
        1 => '一级布局，适合商品分类较少情形',
        2 => '一级布局，适合商品分类较少情形',
        3 => '二级布局，适合商品分类适中情形',
        4 => '三级布局，适合商品分类丰富情形'
    ];
}