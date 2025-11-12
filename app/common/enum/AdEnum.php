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
class AdEnum{
    const MOBILE    = 1;
    const PC        = 2;


    /**
     * Notes:获取终端
     * @param bool $from
     * @return array|mixed
     * @author: cjhao 2021/4/19 11:32
     */
    public static function getTerminal($from = true){
        $desc = [
            self::MOBILE    => '移动端商城',
            self::PC        => 'PC端商城',
        ];
        if(true === $from){
            return $desc;
        }
        return $desc[$from];
    }

    /**
     * Notes:商城页面路径
     * @param bool $type
     * @param bool $from
     * @return array|mixed
     * @author: cjhao 2021/4/20 11:55
     */
    public static function getLinkPage($type = true,$from = true){
        $page = [
            self::MOBILE    => [
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
                [
                    'name'      => '限时秒杀',
                    'path'      => '/bundle/pages/goods_seckill/goods_seckill',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '拼团活动',
                    'path'      => '/bundle/pages/goods_combination/goods_combination',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '热销榜单',
                    'path'      => '/pages/active_list/active_list?type=hot',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '会员中心',
                    'path'      => '/bundle/pages/user_vip/user_vip',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '商城首页',
                    'path'      => '/pages/index/index',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '砍价活动',
                    'path'      => '/bundle/pages/bargain/bargain',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '新品推荐',
                    'path'      => '/pages/active_list/active_list?type=new',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '店铺街',
                    'path'      => '/pages/shop_street/shop_street',
                    'is_tab'    => 1,
                ],
                [
                    'name'      => '商家入驻',
                    'path'      => '/bundle/pages/store_settled/store_settled',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '消息通知',
                    'path'      => '/pages/message_center/message_center',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '积分签到',
                    'path'      => '/bundle/pages/integral_sign/integral_sign',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '积分商城',
                    'path'      => '/bundle/pages/integral_mall/integral_mall',
                    'is_tab'    => 0,
                ],
            ],
            self::PC        => [
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
                    'path'      => '/get_cart',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '我的订单',
                    'path'      => '/get_order',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '商家入驻',
                    'path'      => '/shop',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '帮助中心',
                    'path'      => '/help',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '限时秒杀',
                    'path'      => '/seckill',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '热销榜单',
                    'path'      => '/goods_list/1',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '新品推荐',
                    'path'      => '/goods_list/2',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '店铺街',
                    'path'      => '/shop_street',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '商城资讯',
                    'path'      => '/news_list',
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

    /**
     * Notes:获取商品详情路径
     * @param bool $from
     * @return array|mixed|string
     * @author: cjhao 2021/4/20 14:06
     */
    public static function getGoodsPath($from = true){
        $desc = [
            self::MOBILE    => '/pages/goods_details/goods_details',
            self::PC        => '/goods_details',
        ];
        if(true === $from){
            return $desc;
        }
        return $desc[$from] ?? '';
    }
}