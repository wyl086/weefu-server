<?php
namespace app\api\validate;

use think\Validate;

class GoodsCommentValidate extends Validate
{
    protected $rule = [
        'order_goods_id'        => 'require',
        'goods_comment'         =>'require',
        'description_comment'   =>'require',
        'service_comment'       =>'require',
        'express_comment'       =>'require',

    ];

    protected $message = [
        'order_goods_id.require'        =>'请传入子订单id',
        'shop_id.require'               =>'请传入店铺id',
        'goods_comment.require'         =>'请进行商品评价',
        'description_comment.require'   =>'请进行描述相符评价',
        'service_comment.require'       =>'请进行服务态度评价',
        'express_comment.require'       =>'请进行配送服务评价',
    ];
}