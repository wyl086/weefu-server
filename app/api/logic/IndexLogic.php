<?php
namespace app\api\logic;

use app\common\basics\Logic;
use app\common\enum\CommunityArticleEnum;
use app\common\enum\FootprintEnum;
use app\common\enum\ShopEnum;
use app\common\model\community\CommunityArticle;
use app\common\model\content\Article;
use app\common\model\DevRegion;
use app\common\model\goods\Goods;
use app\common\enum\GoodsEnum;
use app\common\model\shop\Shop;
use app\common\model\goods\GoodsCategory;
use app\common\server\ConfigServer;
use app\common\server\UrlServer;
use app\api\logic\SeckillGoodsLogic;
use think\facade\Db;
use app\common\model\activity_area\ActivityArea;
use think\facade\Event;


class IndexLogic extends Logic
{
    public static function index($user_id,$terminal,$city)
    {
        // 记录访问足迹
        event('Footprint', [
            'type'    => FootprintEnum::ENTER_MALL,
            'user_id' => $user_id
        ]);

        // 记录统计信息(用户访问量)
        Event::listen('UserStat', 'app\common\listener\UserStat');
        event('UserStat');

        // 商城头条
        $headlines = self::getHeadlines();

        // 热销榜单
        $hots = self::getHots();

        // 新品推荐
        $news = self::getNews();

        // 推荐店铺列表
        $shopLists = self::getShopList();
        // 精选推荐店铺
        $shopRecommend = self::getShopRecommend();

        // 秒杀商品
        $seckillTimes = SeckillGoodsLogic::seckillTime();
        $seckillGoods = [];
        foreach($seckillTimes as $item) {
            $item['goods'] = SeckillGoodsLogic::getSeckillGoodsTwo($item['id'],$terminal);
            $seckillGoods[] = $item;
        }
        //活动专区
        $activity_area = ActivityArea::field('id,name,synopsis as title,image')->where(['del'=>0,'status'=>1])->select();
        foreach ($activity_area as &$area_item){
            $area_item['image'] = UrlServer::getFileUrl($area_item['image']);
        }

        // 种草社区文章
        $communityArticle = self::getCommunityArticle();

        // 附近店铺
        $nearbyShops = empty($city) ? [] : self::getNearbyShops($city);

        return [
            'headlines' => $headlines,
            'hots' => $hots,
            'news' => $news,
            'activity_area' => $activity_area,
            'shop_lists' => $shopLists,
            'shop_recommend' => $shopRecommend,
            'seckill_goods' => $seckillGoods,
            'community_article' => $communityArticle,
            'nearby_shops' => $nearbyShops
        ];
    }

    /**
     * 获取商城头条
     */
    public static function getHeadlines()
    {
        $headlines = Article::field('id,title')
            ->where([
                'del' => 0,
                'is_show' => 1,
                'is_notice' => 1,  // 是否为商城公告
            ])
            ->order([
                'create_time' => 'desc',
                'id' => 'desc'
            ])
            ->limit(3)
            ->select()
            ->toArray();
        return $headlines;
    }

    /**
     * 获取热销榜单
     */
    public static function getHots()
    {
        // 销售中商品：未删除/审核通过/已上架
        $onSaleWhere = [
            'del' => GoodsEnum::DEL_NORMAL, // 未删除
            'status' => GoodsEnum::STATUS_SHELVES, // 上架中
            'audit_status' => GoodsEnum::AUDIT_STATUS_OK, // 审核通过
        ];
        $order = [
            'sales_total' => 'desc', // 实际销量+虚拟销量倒序
            'sales_actual' => 'desc', // 实际销量倒序
            'sort_weight' => 'asc', // 商品权重
            'id' => 'desc'
        ];
        $hots = Goods::field('id,name,image,min_price,market_price,sales_actual,create_time,sales_virtual,(sales_actual + sales_virtual) as sales_total')
            ->where($onSaleWhere)
            ->order($order)
            ->limit(9)
            ->select()
            ->toArray();

        return $hots;
    }

    /**
     * 获取新品推荐
     */
    public static function getNews()
    {
        // 销售中商品：未删除/审核通过/已上架
        $onSaleWhere = [
            'g.del' => GoodsEnum::DEL_NORMAL, // 未删除
            'g.status' => GoodsEnum::STATUS_SHELVES, // 上架中
            'g.audit_status' => GoodsEnum::AUDIT_STATUS_OK, // 审核通过
            's.del' => 0, // 店铺未删除
            's.is_freeze' => ShopEnum::SHOP_FREEZE_NORMAL, // 未冻结
            's.is_run' => ShopEnum::SHOP_RUN_OPEN, // 营业中
        ];
        $order = [
            'g.create_time' => 'desc', // 创建时间
            'sales_actual' => 'desc', // 实际销量
            'sort_weight' => 'asc', // 商品权重
            'g.id' => 'desc'
        ];

        $field = 'g.id,g.name,g.image,g.min_price,g.market_price,g.sales_actual,g.create_time,g.sales_virtual,(sales_actual + sales_virtual) as sales_total';

        $news = (new Goods)->alias('g')
            ->join('shop s', 's.id = g.shop_id')
            ->field($field)
            ->where($onSaleWhere)
            ->order($order)
            ->limit(3)
            ->select()
            ->toArray();

        return $news;
    }

    /**
     * 获取推荐店铺列表
     */
    public static function getShopList()
    {
        $where = [
            ['del', '=', 0],
            ['is_recommend', '=', 1],
            ['is_freeze', '=', 0],
            ['is_run', '=', 1],
        ];
        $order = [
            'weight' => 'asc',
            'id' => 'desc'
        ];
        $shopLists = Db::name('shop')
            ->field('id,name,logo,background,expire_time,cover,banner,visited_num')
            ->where($where)
            ->order($order)
            ->select()
            ->toArray();

        // 计算在线销售商品
        $goodsWhere = [
            'del' => GoodsEnum::DEL_NORMAL, // 未删除
            'status' => GoodsEnum::STATUS_SHELVES, // 上架中
            'audit_status' => GoodsEnum::AUDIT_STATUS_OK, // 审核通过
        ];
        foreach($shopLists as $key => &$shop) {
            $shop['expire'] = $shop['expire_time'];
            $shop['expire_time'] = self::getExpire($shop['expire_time']);
            if(!empty($shop['expire']) && $shop['expire'] <= time()) {
                // 去除到期店铺
                unset($shopLists[$key]);
                continue;
            }
            $goodsWhere['shop_id'] = $shop['id'];
            $shop['on_sales_count'] = Goods::where($goodsWhere)->count();

            // logo及背景图
            $shop['logo'] = $shop['logo'] ? UrlServer::getFileUrl($shop['logo']) : UrlServer::getFileUrl(ShopEnum::DEFAULT_LOGO);
            $shop['background'] = $shop['background'] ? UrlServer::getFileUrl($shop['background']) : UrlServer::getFileUrl(ShopEnum::DEFAULT_BG);
            $shop['cover'] = $shop['cover'] ? UrlServer::getFileUrl($shop['cover']) : UrlServer::getFileUrl(ShopEnum::DEFAULT_COVER);
            $shop['banner'] = $shop['banner'] ? UrlServer::getFileUrl($shop['banner']) : '';
        }
        return array_values($shopLists);
    }

    public static function getExpire($value)
    {
        return $value === 0 ? '无期限' : date('Y-m-d H:i:s', $value);
    }

    /**
     * 精选推荐店铺
     */
    public static function getShopRecommend()
    {
        $where = [
            ['del', '=', 0],
            ['is_recommend', '=', 1],
            ['is_freeze', '=', 0],
            ['is_run', '=', 1],
        ];
        $order = [
            'weight' => 'asc',
            'id' => 'desc'
        ];
        $shopLists = Db::name('shop')
            ->field('id,name,logo,background,expire_time,cover,banner,visited_num')
            ->where($where)
            ->order($order)
            ->select()
            ->toArray();
        // 去除过期店铺
        foreach($shopLists as $key => $shop) {
            $shop['expire'] = $shop['expire_time'];
            $shop['expire_time'] = self::getExpire($shop['expire_time']);
            if(!empty($shop['expire']) && $shop['expire'] <= time()) {
                // 去除到期店铺
                unset($shopLists[$key]);
                continue;
            }
        }
        // 取最前面的3家
        $shopLists = array_slice($shopLists, 0, 3);
        // 店铺信息
        foreach($shopLists as &$shop) {
            // 店铺推荐商品
            $goodsWhere = [
                ['del', '=', GoodsEnum::DEL_NORMAL],  // 未删除
                ['status', '=', GoodsEnum::STATUS_SHELVES], // 上架中
                ['audit_status', '=', GoodsEnum::AUDIT_STATUS_OK], // 审核通过
                ['is_recommend', '=', 1], // 推荐商品
                ['shop_id', '=', $shop['id']]
            ];
            $shop['goods_list'] = Goods::field('id,image,name,min_price,market_price')
                ->where($goodsWhere)
                ->order([
                    'sort_weight' => 'asc',
                    'id' => 'desc'
                ])
                ->limit(9)
                ->select()
                ->toArray();
            // logo及背景图
            $shop['logo'] = $shop['logo'] ? UrlServer::getFileUrl($shop['logo']) : UrlServer::getFileUrl(ShopEnum::DEFAULT_LOGO);
            $shop['background'] = $shop['background'] ? UrlServer::getFileUrl($shop['background']) : UrlServer::getFileUrl(ShopEnum::DEFAULT_BG);
            $shop['cover'] = $shop['cover'] ? UrlServer::getFileUrl($shop['cover']) : UrlServer::getFileUrl(ShopEnum::DEFAULT_COVER);
            $shop['banner'] = $shop['banner'] ? UrlServer::getFileUrl($shop['banner']) : '';
        }
        return $shopLists;
    }

    /**
     * @notes 附近店铺
     * @param $city
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2022/9/20 3:37 下午
     */
    public static function getNearbyShops($city)
    {
        $where[] = ['del', '=', 0];
        $where[] = ['is_freeze', '=', 0];
        $where[] = ['is_run', '=', 1];
        $where[] = ['city_id', '=', $city];

        $city = DevRegion::where('id',$city)->field('db09_lng,db09_lat')->findOrEmpty()->toArray();

        $shopLists = Db::name('shop')
            ->field('id,name,logo,background,expire_time,cover,banner,visited_num,longitude,latitude,round(st_distance_sphere(point('.$city['db09_lng'].','.$city['db09_lat'].'),point(longitude, latitude)),2) as distance')
            ->where($where)
            ->order('distance asc')
            ->limit(5)
            ->select()
            ->toArray();
        // 去除过期店铺
        foreach($shopLists as $key => $shop) {
            $shop['expire'] = $shop['expire_time'];
            $shop['expire_time'] = self::getExpire($shop['expire_time']);
            if(!empty($shop['expire']) && $shop['expire'] <= time()) {
                // 去除到期店铺
                unset($shopLists[$key]);
                continue;
            }
        }
        // 店铺信息
        foreach($shopLists as &$shop) {
            // 计算在线销售商品
            $goodsWhere = [
                'del' => GoodsEnum::DEL_NORMAL, // 未删除
                'status' => GoodsEnum::STATUS_SHELVES, // 上架中
                'audit_status' => GoodsEnum::AUDIT_STATUS_OK, // 审核通过
            ];
            $goodsWhere['shop_id'] = $shop['id'];
            $shop['on_sales_count'] = Goods::where($goodsWhere)->count();

            // logo及背景图
            $shop['logo'] = $shop['logo'] ? UrlServer::getFileUrl($shop['logo']) : UrlServer::getFileUrl(ShopEnum::DEFAULT_LOGO);
            $shop['background'] = $shop['background'] ? UrlServer::getFileUrl($shop['background']) : UrlServer::getFileUrl(ShopEnum::DEFAULT_BG);
            $shop['cover'] = $shop['cover'] ? UrlServer::getFileUrl($shop['cover']) : UrlServer::getFileUrl(ShopEnum::DEFAULT_COVER);
            $shop['banner'] = $shop['banner'] ? UrlServer::getFileUrl($shop['banner']) : '';
        }
        return $shopLists;
    }


    public static function indexCategory($platform_category_id)
    {
        // 二级分类
        $levelTwo = self::levelTwo($platform_category_id);
        // 品类热销
        $categoryHots = self::categoryHots($platform_category_id);
        // 品类推荐
        $categoryRecommend = self::categoryRecommend($platform_category_id);

        return [
            'level_two' => $levelTwo,
            'category_hots' => $categoryHots,
            'category_recommend' => $categoryRecommend
        ];
    }

    public static function levelTwo($platform_category_id)
    {
        $where = [
            'del' => 0,
            'is_show' => 1,
            'pid' => $platform_category_id
        ];
        $order = [
            'sort' => 'asc',
            'id' => 'desc'
        ];
        $levelTwo = GoodsCategory::field('id,name,image')
            ->where($where)
            ->order($order)
            ->select()
            ->toArray();
        return $levelTwo;
    }

    public static function categoryHots($platform_category_id)
    {
        // 销售中商品：未删除/审核通过/已上架
        $where = [
            ['del', '=', GoodsEnum::DEL_NORMAL],  // 未删除
            ['status', '=', GoodsEnum::STATUS_SHELVES], // 上架中
            ['audit_status', '=', GoodsEnum::AUDIT_STATUS_OK], // 审核通过
            ['first_cate_id|second_cate_id|third_cate_id', '=', $platform_category_id], // 分类id
        ];
        $order = [
            'sales_actual' => 'desc', // 实际销量倒序
            'id' => 'desc'
        ];

        $lists = Goods::field('id,name,image,min_price,market_price,sales_actual,(sales_actual + sales_virtual) as sales_total')
            ->where($where)
            ->order($order)
            ->limit(9)
            ->select()
            ->toArray();

        return $lists;
    }

    public static function categoryRecommend($platform_category_id)
    {
        // 销售中商品：未删除/审核通过/已上架
        $where = [
            ['del', '=', GoodsEnum::DEL_NORMAL],  // 未删除
            ['status', '=', GoodsEnum::STATUS_SHELVES], // 上架中
            ['audit_status', '=', GoodsEnum::AUDIT_STATUS_OK], // 审核通过
            ['first_cate_id|second_cate_id|third_cate_id', '=', $platform_category_id], // 分类id
            ['is_recommend', '=', 1] // 推荐商品
        ];
        $order = [
            'sales_actual' => 'desc', // 实际销量倒序
            'id' => 'desc'
        ];

        $lists = Goods::field('id,name,image,min_price,market_price,sales_actual,(sales_actual + sales_virtual) as sales_total')
            ->where($where)
            ->order($order)
            ->limit(3)
            ->select()
            ->toArray();

        return $lists;
    }

    public static function config()
    {
        $navigation = Db::name('dev_navigation')
          ->field('name,status,page_path,selected_icon,un_selected_icon')
          ->where('del', 0)
          ->order('id', 'desc')
          ->withAttr('selected_icon',function($value,$data){
            return UrlServer::getFileUrl($value);
          })
          ->withAttr('un_selected_icon',function($value,$data){
            return UrlServer::getFileUrl($value);
          })
          ->select();
        $share_h5 = ConfigServer::get('share', 'h5', [
            'h5_share_title' => '',
            'h5_share_intro' => '',
            'h5_share_image' => ''
        ]);
        if($share_h5['h5_share_image']){
            $share_h5['h5_share_image'] = UrlServer::getFileUrl($share_h5['h5_share_image']);
        }
        $share_mnp = ConfigServer::get('share', 'mnp', [
            'mnp_share_title' => '',
            'mnp_share_image' => ''
        ]);
        if (empty($share_mnp['mnp_share_image'])) {
            $share_mnp['mnp_share_image'] = '';
        } else {
            $share_mnp['mnp_share_image'] = UrlServer::getFileUrl($share_mnp['mnp_share_image']);
        }

        //首页顶部背景图
        $index_top_bg = ConfigServer::get('decoration_index', 'background_image', '');
        if (!empty($index_top_bg)) {
            $index_top_bg = UrlServer::getFileUrl($index_top_bg);
        }

        //个人中心背景图
        $center_top_bg = ConfigServer::get('decoration_center', 'background_image', '');
        if (!empty($center_top_bg)) {
            $center_top_bg = UrlServer::getFileUrl($center_top_bg);
        }

        $config = [
            'shop_hide_goods'  => ConfigServer::get('decoration', 'shop_hide_goods', 0), //商品详细是否显示店铺
            'shop_street_hide' => ConfigServer::get('decoration', 'shop_street_hide', 1), //是否显示店铺街
            'register_setting' => ConfigServer::get('register', 'captcha', 0),//注册设置-是否开启短信验证注册
            'app_wechat_login' => ConfigServer::get('app', 'wechat_login', 0),//APP是否允许微信授权登录
            'shop_login_logo'  => UrlServer::getFileUrl(ConfigServer::get('website', 'client_login_logo')),//移动端商城logo
            'pc_login_logo'    => UrlServer::getFileUrl(ConfigServer::get('website', 'pc_client_login_logo')), //pc登录封面
            'web_favicon'      => UrlServer::getFileUrl(ConfigServer::get('website', 'web_favicon')),//浏览器标签图标
            'name'             => ConfigServer::get('website', 'name'),//商城名称
            'copyright_info'   => ConfigServer::get('copyright', 'company_name'),//版权信息
            'icp_number'       => ConfigServer::get('copyright', 'number'),//ICP备案号
            'icp_link'         => ConfigServer::get('copyright', 'link'),//备案号链接
            'app_agreement'    => ConfigServer::get('app', 'agreement', 0),//app弹出协议
            'ios_download'     => ConfigServer::get('app', 'line_ios', ''),//ios_app下载链接
            'android_download' => ConfigServer::get('app', 'line_android', ''),//安卓下载链接
            'download_doc'     => ConfigServer::get('app', 'download_doc', ''),//app下载文案
            'cate_style'       => ConfigServer::get('decoration', 'layout_no', 1),//分类页面风格
            'index_setting' => [ // 首页设置
              // 热销榜单
              'host_show' => ConfigServer::get('decoration_index', 'host_show', 1),
              // 新品推荐
              'new_show' => ConfigServer::get('decoration_index', 'new_show', 1),
              // 推荐店铺
              'shop_show' => ConfigServer::get('decoration_index', 'shop_show', 1),
              // 种草推荐
              'community_show' => ConfigServer::get('decoration_index','community_show',1),
                // 直播间开关
                'live_room' => ConfigServer::get('decoration_index','live_room',1),
              // 顶部背景图
              'top_bg_image' => $index_top_bg
            ],
            'center_setting' => [ // 个人中心设置
              // 顶部背景图
              'top_bg_image' => $center_top_bg
            ],
            'navigation_setting' => [ // 底部导航设置
              // 未选中文字颜色
              'ust_color' => ConfigServer::get('decoration', 'navigation_setting_ust_color', '#000000'),
              // 选中文字颜色
              'st_color' => ConfigServer::get('decoration', 'navigation_setting_st_color', '#000000'),
            ],
            // 分享设置
            'share' => array_merge($share_h5,$share_mnp),
            // 首页底部导航菜单
            'navigation_menu' => $navigation,
            // 域名
            'base_domain' => UrlServer::getFileUrl(),
            // 微信访问H5时,是否自动授权登录,默认关闭-0
            'wechat_h5' => ConfigServer::get('login', 'wechat_h5', 0),
            // 客服请求域名
            'ws_domain' => env('project.ws_domain', 'ws:127.0.0.1'),
            // 附近店铺，默认关闭
            'is_open_nearby' => ConfigServer::get('map', 'is_open_nearby',0),
            // 种草社区，默认开启
            'is_open_community' => ConfigServer::get('community', 'status', 1),
            
            // 发货配置
            'mini_express_send_sync' => ConfigServer::get('mnp', 'express_send_sync', 1)
        ];
        return $config;
    }

    /**
     * @notes 版权资质
     * @return array|int|mixed|string|null
     * @author ljj
     * @date 2022/2/22 10:16 上午
     */
    public static function copyright($shop_id)
    {
        $other_qualifications = [];
        if (!$shop_id) {
            $business_license = ConfigServer::get('copyright', 'business_license');
            $other_qualifications = ConfigServer::get('copyright', 'other_qualifications',[]);
            if (!empty($business_license)) {
                array_unshift($other_qualifications,$business_license);
            }
            if (!empty($other_qualifications)) {
                foreach ($other_qualifications as &$val) {
                    $val = UrlServer::getFileUrl($val);
                }
            }
        }else {
            $result = Shop::where('id',$shop_id)->json(['other_qualifications'],true)->field('business_license,other_qualifications')->findOrEmpty()->toArray();
            $business_license = $result['business_license'] ? UrlServer::getFileUrl($result['business_license']) : '';
            if (!empty($result['other_qualifications'])) {
                foreach ($result['other_qualifications'] as &$val) {
                    $other_qualifications[] = UrlServer::getFileUrl($val);
                }
            }
            if (!empty($business_license)) {
                array_unshift($other_qualifications,$business_license);
            }
        }

        return $other_qualifications;
    }

    
    /**
     * @notes 首页社区文章
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/5/5 9:38
     */
    public static function getCommunityArticle()
    {
        // 种草总开关
        $isOpen = ConfigServer::get('community', 'status', 1);
        if (!$isOpen) {
            return [];
        }

        $lists = (new CommunityArticle())
            ->with(['user' => function($query) {
                $query->field(['id', 'nickname', 'avatar']);
            }])
            ->field(['id', 'content', 'image', 'user_id'])
            ->where(['del' => 0, 'status' => CommunityArticleEnum::STATUS_SUCCESS])
            ->order(['like' => 'desc', 'id' => 'desc'])
            ->limit(10)
            ->select()
            ->bindAttr('user', ['nickname', 'avatar'])
            ->hidden(['user'])
            ->toArray();

        foreach ($lists as $key => $item) {
            $lists[$key]['avatar'] = UrlServer::getFileUrl($item['avatar']);
        }

        return $lists;
    }


    /**
     * @notes 腾讯地图逆地址解析(坐标位置描述)
     * @param $get
     * @return mixed
     * @author ljj
     * @date 2022/9/21 2:37 下午
     * 经纬度到文字地址及相关位置信息的转换
     */
    public static function geocoder($get)
    {
        $get['key'] = ConfigServer::get('map', 'tx_map_key','');
        if ($get['key'] == '') {
            return ['status'=>1,'message'=>'腾讯地图开发密钥不能为空'];
        }

        $query = http_build_query($get);
        $url = 'https://apis.map.qq.com/ws/geocoder/v1/';
        $result =  json_decode(file_get_contents($url.'?'.$query),true);
        $result['city_id'] = isset($result['result']['ad_info']['city']) ? UserAddressLogic::handleRegionField( $result['result']['ad_info']['city'], 2) : 0;

        return $result;
    }
}
