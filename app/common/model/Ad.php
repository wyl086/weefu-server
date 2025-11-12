<?php
namespace app\common\model;

use app\common\basics\Models;

class Ad extends Models
{
    const mobile = 1;
    const pc     = 2;


    public static function getAdTypeDesc($from = true){
        $desc = [
            self::mobile    => '移动端商城',
            self::pc        => 'pc端商城',
        ];
        if($from === true){
            return $desc;
        }
        return $desc[$from] ?? '';
    }

    public static function getLinkPage($type = true,$from = true){
        $page = [
            self::mobile    => [
                [
                    'name'      => '商品分类',
                    'path'      => '/pages/goods_cate/goods_cate',
                    'is_tab'    => 1,
                ],
                [
                    'name'      => '领券中心',
                    'path'      => '/bundle/pages/get_coupon/get_coupon',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '个人中心',
                    'path'      => '/pages/user/user',
                    'is_tab'    => 1,
                ],
            ],
            self::pc        => [
                [
                    'name'      => '商品分类',
                    'path'      => '/category',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '领券中心',
                    'path'      => '/get_coupons',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '购物车',
                    'path'      => '/shop_cart',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '我的订单',
                    'path'      => '/user/order',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '商家入驻',
                    'path'      => '/store_settled',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '帮助中心',
                    'path'      => '/help_center',
                    'is_tab'    => 0,
                ],
            ],
        ];
        if(true !== $type){
            $page = $page[$type] ?? [];
        }
        if(true === $from){
            return $page;
        }
        return $page[$from] ?? [];
    }


    public static function getGoodsPath($from = true){
        $desc = [
            self::mobile    => '/pages/goods_details/goods_details',
            self::pc        => '/goods_details',
        ];
        if(true === $from){
            return $desc;
        }
        return $desc[$from] ?? '';

    }
}