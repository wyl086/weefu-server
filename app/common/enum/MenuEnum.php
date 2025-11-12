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
class MenuEnum{
    //首页菜单
    const INDEX = [
        //限时秒杀
        [
            'index'         =>  100,
            'name'          => '限时秒杀',
            'link'          => '/bundle/pages/goods_seckill/goods_seckill',
            'is_tab'        => 0,
            'link_type'     => 1,
        ],
        //拼团活动
        [
            'index'         =>  101,
            'name'          => '拼团活动',
            'link'          => '/bundle/pages/goods_combination/goods_combination',
            'is_tab'        => 0,
            'link_type'     => 1,
        ],
        //热销榜单
        [
            'index'         => 102,
            'name'          => '热销榜单',
            'link'          => '/pages/active_list/active_list?type=hot',
            'is_tab'        => 0,
            'link_type'     => 1,
        ],
        //领券中心
        [
            'index'         => 103,
            'name'          => '领券中心',
            'link'          => '/bundle/pages/get_coupon/get_coupon',
            'is_tab'        => 0,
            'link_type'     => 1,
        ],
        //会员中心
        [
            'index'         => 105,
            'name'          => '会员中心',
            'link'          => '/bundle/pages/user_vip/user_vip',
            'is_tab'        => 0,
            'link_type'     => 1,
        ],
        //我的收藏
        [
            'index'         => 106,
            'name'          => '我的收藏',
            'link'          => '/bundle/pages/user_collection/user_collection',
            'is_tab'        => 0,
            'link_type'     => 1,
        ],
        //商城资讯
        [
            'index'         => 107,
            'name'          => '商城资讯',
            'link'          => '/pages/news_list/news_list',
            'is_tab'        => 0,
            'link_type'     => 1,
        ],
        //帮助中心
        [
            'index'         => 108,
            'name'          => '帮助中心',
            'link'          => '/pages/news_list/news_list?type=1',
            'is_tab'        => 0,
            'link_type'     => 1,
        ],
        //收货地址
        [
            'index'         => 109,
            'name'          => '收货地址',
            'link'          => '/bundle/pages/user_address/user_address',
            'is_tab'        => 0,
            'link_type'     => 1,
        ],
        //商品分类
        [
            'index'         => 110,
            'name'          => '商品分类',
            'link'          => '/pages/goods_cate/goods_cate',
            'is_tab'        => 1,
            'link_type'     => 1,
        ],
//        //积分抽奖
//        [
//            'index'         => 111,
//            'name'          => '积分抽奖',
//            'link'          => '/bundle/pages/luckly_wheel/luckly_wheel',
//            'is_tab'        => 0,
//            'link_type'     => 1,
//        ],
        //砍价活动
        [
            'index'         => 112,
            'name'          => '砍价活动',
            'link'          => '/bundle/pages/bargain/bargain',
            'is_tab'        => 0,
            'link_type'     => 1,
        ],
         //新品推荐
        [
            'index'         => 113,
            'name'          => '新品推荐',
            'link'          => '/pages/active_list/active_list?type=new',
            'is_tab'        => 0,
            'link_type'     => 1,
        ],
        //店铺街
        [
            'index'         => 114,
            'name'          => '店铺街',
            'link'          => '/pages/shop_street/shop_street',
            'is_tab'        => 0,
            'link_type'     => 1,
        ],
        //商家入驻
        [
            'index'         => 115,
            'name'          => '商家入驻',
            'link'          => '/bundle/pages/store_settled/store_settled',
            'is_tab'        => 0,
            'link_type'     => 1,
        ],
        //消息通知
        [
            'index'         => 116,
            'name'          => '消息通知',
            'link'          => '/pages/message_center/message_center',
            'is_tab'        => 0,
            'link_type'     => 1,
        ],
        //品牌
        [
            'index'         => 117,
            'name'          => '品牌',
            'link'          => '/pages/brand_list/brand_list',
            'is_tab'        => 0,
            'link_type'     => 1,
        ],
        //积分签到
        [
            'index'         => 118,
            'name'          => '积分签到',
            'link'          => '/bundle/pages/integral_sign/integral_sign',
            'is_tab'        => 0,
            'link_type'     => 1,
        ],
        //积分商城
        [
            'index'         => 119,
            'name'          => '积分商城',
            'link'          => '/bundle/pages/integral_mall/integral_mall',
            'is_tab'        => 0,
            'link_type'     => 1,
        ],
        //种草社区
        [
            'index'         => 120,
            'name'          => '种草社区',
            'link'          => '/pages/community/community',
            'is_tab'        => 1,
            'link_type'     => 1,
        ]
    ];

    //个人中心菜单
    const CENTRE = [
        [
            'index'         => 200,
            'name'          => '我的钱包',
            'link'          => '/bundle/pages/user_wallet/user_wallet',
            'is_tab'        => 0,
            'link_type'     => 1,
        ],
        [
            'index'         => 201,
            'name'          => '分销推广',
            'link'          => '/bundle/pages/user_spread/user_spread',
            'is_tab'        => 0,
            'link_type'     => 1,
        ],
        [
            'index'         => 202,
            'name'          => '我的优惠券',
            'link'          => '/bundle/pages/user_coupon/user_coupon',
            'is_tab'        => 0,
            'link_type'     => 1,
        ],
        [
            'index'         => 203,
            'name'          => '等级服务',
            'link'          => '/bundle/pages/user_vip/user_vip',
            'is_tab'        => 0,
            'link_type'     => 1,
        ],
        [
            'index'         => 204,
            'name'          => '帮助中心',
            'link'          => '/pages/news_list/news_list?type=1',
            'is_tab'        => 0,
            'link_type'     => 1,
        ],
        [
            'index'         => 205,
            'name'          => '收货地址',
            'link'          => '/bundle/pages/user_address/user_address',
            'is_tab'        => 0,
            'link_type'     => 1,
        ],
        [
            'index'         => 206,
            'name'          => '我的收藏',
            'link'          => '/bundle/pages/user_collection/user_collection',
            'is_tab'        => 0,
            'link_type'     => 1,
        ],
        [
            'index'         => 207,
            'name'          => '联系客服',
            'link'          => '/bundle/pages/chat/chat',
            'is_tab'        => 0,
            'link_type'     => 1,
            'menu_type'     => 1,
        ],
        [
            'index'         => 208,
            'name'          => '我的拼团',
            'link'          => '/bundle/pages/user_group/user_group',
            'is_tab'        => 0,
            'link_type'     => 1,
            'menu_type'     => 1,
        ],
        [
            'index'         => 209,
            'name'          => '砍价记录',
            'link'          => '/bundle/pages/bargain_code/bargain_code',
            'is_tab'        => 0,
            'link_type'     => 1,
            'menu_type'     => 1,
        ],
        [
            'index'         => 210,
            'name'          => '商家入驻',
            'link'          => '/bundle/pages/store_settled/store_settled',
            'is_tab'        => 0,
            'link_type'     => 1,
            'menu_type'     => 1,
        ],
        [
            'index'         => 211,
            'name'          => '消息通知',
            'link'          => '/pages/message_center/message_center',
            'is_tab'        => 0,
            'link_type'     => 1,
            'menu_type'     => 1,
        ],
        [
            'index'         => 212,
            'name'          => '邀请海报',
            'link'          => '/bundle/pages/invite_fans/invite_fans',
            'is_tab'        => 0,
            'link_type'     => 1,
            'menu_type'     => 1,
        ]
    ];

    /**
     * Notes:获取菜单列表
     * @param bool $scene 指定个人或首页菜单：true时返回所有菜单
     * @param bool $from 返回某个菜单:true返回个人菜单或首页菜单
     * @return array
     * @author: cjhao 2021/5/15 16:51
     * name         => 菜单名称
     * link         => 调整链接
     * is_tab       => 是否的tab页
     * link_type    => 菜单类型：1-跳转；2-web-view；3-按钮（微信小程序可调用客服）
     */
    public static function getMenu($scene = true,$from = true){
        //首页菜单
        $config_index = self::INDEX;
        //个人菜单
        $config_center = self::CENTRE;

        $config_name = 'config_'.$scene;
        $content = $$config_name;
        if(true === $scene){
            $content = array_merge($config_index,$config_center);
        }
        if(true === $from){
            return $content;
        }

        $menu_index = array_column($content,'index');
        $key = array_search($from,$menu_index);
        if(false !== $key){
            return $content[$key];
        }
        return [];
    }
}