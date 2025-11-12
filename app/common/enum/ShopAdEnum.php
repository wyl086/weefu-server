<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\common\enum;

class ShopAdEnum
{
    // 移动端
    const TERMINAL_MOBILE    = 1;
    // PC端
    const TERMINAL_PC        = 2;
    
    // 位置 店铺主页
    const PLACE_SHOP_HOME = 1;
    
    static function getPlaceDesc($from = true)
    {
        $desc = [
            self::PLACE_SHOP_HOME    => '店铺主页',
        ];
        
        return $from === true ? $desc : ($desc[$from] ?? '');
    }
    
    static function getTerminal($from = true)
    {
        $desc = [
            self::TERMINAL_MOBILE    => '移动端',
            self::TERMINAL_PC        => 'PC端',
        ];
        
        return $from === true ? $desc : ($desc[$from] ?? '');
    }
    
    static function getLinkPage(): array
    {
        return [
            // [
            //     'name'  => '店铺信息',
            //     'list'  => [
            //         [
            //             'name'  => '店铺页面',
            //             'type'  => 'shop',
            //         ],
            //     ],
            // ],
            [
                'name'  => '商品信息',
                'list'  => [
                    [
                        'name'  => '商品列表',
                        'type'  => 'goods',
                    ],
                    // [
                    //     'name'  => '商品分类',
                    //     'type'  => 'goods_category',
                    // ],
                ],
            ],
        ];
    }
    
    static function getShopLinkPaths(): array
    {
        return [
            [
                'name'      => '店铺首页',
                'path'      => '/pages/store_index/store_index',
            ],
            [
                'name'      => '店铺信息',
                'path'      => '/pages/store_detail/store_detail',
            ],
        ];
    }
    
    static function getShopGoodsListPath(): string
    {
        return '/pages/goods_details/goods_details';
    }
    
    static function getShopGoodsCategoryPath(): string
    {
        return '/pages/store_index/store_index';
    }
}