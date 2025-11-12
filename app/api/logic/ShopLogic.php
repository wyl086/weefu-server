<?php
namespace app\api\logic;

use app\common\basics\Logic;
use app\common\enum\GoodsEnum;
use app\common\enum\ShopAdEnum;
use app\common\enum\ShopEnum;
use app\common\logic\QrCodeLogic;
use app\common\model\dev\DevRegion;
use app\common\model\shop\ShopAd;
use app\common\server\ConfigServer;
use app\common\server\UrlServer;
use app\common\model\goods\Goods;
use app\common\model\shop\Shop;
use app\common\model\shop\ShopFollow;
use think\facade\Event;



class ShopLogic extends Logic
{
    /**
     * 获取店铺信息
     */
    public static function getShopInfo($shopId, $userId, $params = [])
    {
        // 记录统计信息(访问商铺用户量)
        Event::listen('ShopStat', 'app\common\listener\ShopStat');
        event('ShopStat', $shopId);

        $where = [
            'del' => 0,
            'id' => $shopId
        ];
        $field = [
            'id', 'create_time', 'name', 'logo', 'background',
            'type', 'score', 'star', 'intro',
            'visited_num', 'cover', 'banner', 'is_freeze',
            'is_run', 'expire_time',
            'province_id', 'city_id', 'district_id', 'address',
            'run_start_time', 'run_end_time', 'weekdays',
        ];
        $shop = Shop::field($field)
            ->where($where)
            ->append([ 'type_desc', 'is_expire' ])
            ->findOrEmpty();
        if($shop->isEmpty()) {
            return [];
        }else{
            $shop = $shop->toArray();
        }
        //
        $shop['logo']           = UrlServer::getFileUrl($shop['logo'] ? : ShopEnum::DEFAULT_LOGO);
        $shop['background']     = UrlServer::getFileUrl($shop['background'] ? : ShopEnum::DEFAULT_BG);
        $shop['cover']          = UrlServer::getFileUrl($shop['cover'] ? :ShopEnum::DEFAULT_COVER);
        $shop['banner']         = UrlServer::getFileUrl($shop['banner'] ? : ShopEnum::DEFAULT_BANNER);
        $shop['run_start_time'] = $shop['run_start_time'] ? date('H:i:s', $shop['run_start_time']) : '';
        $shop['run_end_time']   = $shop['run_end_time'] ? date('H:i:s', $shop['run_end_time']) : '';
        $shop['province']       = DevRegion::getAreaName($shop['province_id']);
        $shop['city']           = DevRegion::getAreaName($shop['city_id']);
        $shop['district']       = DevRegion::getAreaName($shop['district_id']);
        
        $shop['qr_code']        = (new QrCodeLogic)->shopQrCode($shop['id'], $params['terminal'] ?? '');
        
        // 在售商品
        // 销售中商品：未删除/审核通过/已上架
        $onSaleWhere = [
            ['del', '=', GoodsEnum::DEL_NORMAL],  // 未删除
            ['status', '=', GoodsEnum::STATUS_SHELVES], // 上架中
            ['audit_status', '=', GoodsEnum::AUDIT_STATUS_OK], // 审核通过
        ];
        $shop['on_sale_count'] = Goods::where($onSaleWhere)->where('shop_id', $shopId)->count();

        // 店铺推荐商品
        $shop['goods_list'] = Goods::field('id,image,name,min_price,market_price')
            ->where($onSaleWhere)
            ->where([
                'shop_id' => $shop['id'],
                'is_recommend' => 1, // 推荐商品
            ])
            ->limit(9)
            ->select()
            ->toArray();

        // 用户是否关注店铺
        $shop['shop_follow_status'] = 0;
        if($userId) { // 用户已登录
            $shopFollow = ShopFollow::where(['user_id'=>$userId, 'shop_id'=>$shopId])->findOrEmpty();
            if(!$shopFollow->isEmpty()) {
                $shop['shop_follow_status'] = $shopFollow['status'];
            }
        }
        $shop['follow_num']         = ShopFollow::where(['shop_id' => $shopId,'status' => 1])->count('id');
        $image                      = ConfigServer::get('shop_customer_service', 'image', '', $shopId);
        $shop['customer_image']     = $image ? UrlServer::getFileUrl($image) : '';
        $shop['customer_wechat']    = ConfigServer::get('shop_customer_service', 'wechat', '', $shopId);
        $shop['customer_phone']     = ConfigServer::get('shop_customer_service', 'phone', '', $shopId);
        
        // 店铺广告
        $adWhere = [
            [ 'shop_id', '=', $shopId ],
            [ 'status', '=', 1 ],
        ];
        $shop['ad'] = [
            'pc'        => ShopAd::where($adWhere)->where('terminal', ShopAdEnum::TERMINAL_PC)->append([ 'link_path', 'link_query' ])->order('sort desc,id desc')->select()->toArray(),
            'mobile'    => ShopAd::where($adWhere)->where('terminal', ShopAdEnum::TERMINAL_MOBILE)->append([ 'link_path', 'link_query' ])->order('sort desc,id desc')->select()->toArray(),
        ];
        
        return $shop;
    }

    /**
     * 店铺列表
     */
    public static function getShopList($get)
    {
        $where = [
            ['is_freeze', '=', 0], // 未冻结
            ['del', '=', 0], // 未删除
            ['is_run', '=', 1], // 未暂停营业
        ];

        // 店铺名称
        if(isset($get['name']) && !empty($get['name'])) {
            $where[] = ['name', 'like', '%'. trim($get['name']. '%')];
        }

        // 主营类目
        if(isset($get['shop_cate_id']) && !empty($get['shop_cate_id'])) {
            $where[] = ['cid', '=', $get['shop_cate_id']];
        }

        $order = [
            'weight' => 'asc',
            'score' => 'desc',
            'id' => 'desc'
        ];

        $list = Shop::field('id,type,name,logo,background,visited_num,cover,banner')
            ->where($where)
            // 无限期 或 未到期
            ->whereRaw('expire_time =0 OR expire_time > '. time())
            ->order($order)
            ->page($get['page_no'], $get['page_size'])
            ->select()
            ->toArray();

        $count = Shop::where($where)
            // 无限期 或 未到期
            ->whereRaw('expire_time =0 OR expire_time > '. time())
            ->count();

        $onSaleWhere = [
            ['del', '=', GoodsEnum::DEL_NORMAL],  // 未删除
            ['status', '=', GoodsEnum::STATUS_SHELVES], // 上架中
            ['audit_status', '=', GoodsEnum::AUDIT_STATUS_OK], // 审核通过
        ];
        foreach($list as &$shop) {
            $shop['goods_list'] = Goods::field('id,image,name,min_price,market_price')
                ->where($onSaleWhere)
                ->where([
                    'shop_id' => $shop['id'],
                ])
                ->limit(10)
                ->select()
                ->toArray();
            $shop['on_sale_goods'] = count($shop['goods_list']);
            // logo及背景图
            $shop['logo']       = $shop['logo'] ? UrlServer::getFileUrl($shop['logo']) : UrlServer::getFileUrl(ShopEnum::DEFAULT_LOGO);
            $shop['background'] = $shop['background'] ? UrlServer::getFileUrl($shop['background']) : UrlServer::getFileUrl(ShopEnum::DEFAULT_BG);
            $shop['cover']      = $shop['cover'] ? UrlServer::getFileUrl($shop['cover']) : UrlServer::getFileUrl(ShopEnum::DEFAULT_COVER);
            $shop['banner']     = $shop['banner'] ? UrlServer::getFileUrl($shop['banner']) : '';
        }

        $more = is_more($count, $get['page_no'], $get['page_size']);

        $data = [
            'list' => $list,
            'count' => $count,
            'more' => $more,
            'page_no' => $get['page_no'],
            'page_isze' => $get['page_size']
        ];

        return $data;
    }


    /**
     * @notes 附近店铺列表
     * @param $get
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2022/9/20 4:29 下午
     */
    public static function getNearbyShops($get)
    {
        $where = [
            ['is_freeze', '=', 0], // 未冻结
            ['del', '=', 0], // 未删除
            ['is_run', '=', 1], // 未暂停营业
            ['city_id', '=', $get['city_id']],
        ];

        // 店铺名称
        if(isset($get['name']) && !empty($get['name'])) {
            $where[] = ['name', 'like', '%'. trim($get['name']. '%')];
        }

        // 主营类目
        if(isset($get['shop_cate_id']) && !empty($get['shop_cate_id'])) {
            $where[] = ['cid', '=', $get['shop_cate_id']];
        }

        $city = DevRegion::where('id',$get['city_id'])->field('db09_lng,db09_lat')->findOrEmpty()->toArray();

        $list = Shop::field('id,name,logo,background,visited_num,cover,banner,st_distance_sphere(point('.$city['db09_lng'].','.$city['db09_lat'].'),point(longitude, latitude)) as distance')
            ->where($where)
            // 无限期 或 未到期
            ->whereRaw('expire_time =0 OR expire_time > '. time())
            ->order('distance asc')
            ->page($get['page_no'], $get['page_size'])
            ->select()
            ->toArray();

        $count = Shop::where($where)
            // 无限期 或 未到期
            ->whereRaw('expire_time =0 OR expire_time > '. time())
            ->count();

        $onSaleWhere = [
            ['del', '=', GoodsEnum::DEL_NORMAL],  // 未删除
            ['status', '=', GoodsEnum::STATUS_SHELVES], // 上架中
            ['audit_status', '=', GoodsEnum::AUDIT_STATUS_OK], // 审核通过
        ];
        foreach($list as &$shop) {
            $shop['goods_list'] = Goods::field('id,image,name,min_price,market_price')
                ->where($onSaleWhere)
                ->where([
                    'shop_id' => $shop['id'],
                ])
                ->select()
                ->toArray();
            $shop['on_sale_goods'] = count($shop['goods_list']);
            // logo及背景图
            $shop['logo'] = $shop['logo'] ? UrlServer::getFileUrl($shop['logo']) : UrlServer::getFileUrl(ShopEnum::DEFAULT_LOGO);
            $shop['background'] = $shop['background'] ? UrlServer::getFileUrl($shop['background']) : UrlServer::getFileUrl(ShopEnum::DEFAULT_BG);
            $shop['cover'] = $shop['cover'] ? UrlServer::getFileUrl($shop['cover']) : UrlServer::getFileUrl(ShopEnum::DEFAULT_COVER);
            $shop['banner'] = $shop['banner'] ? UrlServer::getFileUrl($shop['banner']) : '';

            //转换距离单位
            if ($shop['distance'] < 1000) {
                $shop['distance'] = round($shop['distance']).'m';
            }else {
                $shop['distance'] = round($shop['distance'] / 1000,2).'km';
            }
        }

        $more = is_more($count, $get['page_no'], $get['page_size']);

        $data = [
            'list' => $list,
            'count' => $count,
            'more' => $more,
            'page_no' => $get['page_no'],
            'page_isze' => $get['page_size']
        ];

        return $data;
    }
}