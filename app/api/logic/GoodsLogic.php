<?php


namespace app\api\logic;

use app\common\model\distribution\Distribution;
use app\common\model\distribution\DistributionLevel;
use app\common\model\shop\ShopFollow;
use app\common\model\user\User;
use app\common\basics\Logic;
use app\common\enum\FootprintEnum;
use app\common\model\distribution\DistributionGoods;
use app\common\model\goods\Goods;
use app\common\model\goods\GoodsCollect;
use app\common\model\goods\GoodsClick;
use app\common\model\goods\GoodsSpec;
use app\common\model\goods\GoodsComment;
use app\common\model\goods\GoodsCommentImage;
use app\common\model\SearchRecord;
use app\common\enum\GoodsEnum;
use app\common\model\seckill\SeckillGoods;
use app\common\model\shop\Shop;
use app\common\model\team\TeamActivity;
use app\common\model\team\TeamFound;
use app\common\model\team\TeamGoods;
use app\common\model\user\UserLevel;
use app\common\server\ConfigServer;
use app\common\server\UrlServer;
use think\facade\Db;
use think\facade\Validate;

class GoodsLogic extends Logic
{
    /**
     * 商品详情
     */
    public static function getGoodsDetail($goodsId, $userId)
    {
        //获取用户折扣
        $discount = 10;
        if($userId){
            $user = User::where('id', $userId)->find();
            if($user && isset($user['level'])){
                $user_discount = UserLevel::where('id', $user['level'])->value('discount');
                if($user_discount && $user_discount > 0 && $user_discount <= 10){
                    $discount = $user_discount;
                }
            }
        }

        // 销售中商品：未删除/审核通过/已上架
        $onSaleWhere = [
            'del' => GoodsEnum::DEL_NORMAL, // 未删除
            'status' => GoodsEnum::STATUS_SHELVES, // 上架中
            'audit_status' => GoodsEnum::AUDIT_STATUS_OK, // 审核通过
        ];

        $goodsDetail = Goods::with(['goods_image', 'goods_item', 'shop'])
            ->field('id,type,name,image,video,remark,content,market_price,min_price,max_price,is_show_stock,stock,sales_actual,sales_virtual,clicks,clicks_virtual,shop_id,poster,delivery_type')
            ->where($onSaleWhere)
            ->where('id', $goodsId)
            ->findOrEmpty();
    
        if ($goodsDetail->isEmpty()) {
            self::$error = '商品已下架';
            return false;
        }

        //处理默认配送方式
        if ($goodsDetail['type'] == GoodsEnum::TYPE_VIRTUAL) {
            $goodsDetail['default_delivery_type'] = GoodsEnum::DELIVERY_VIRTUAL;
        } else {
            // 快递和自提
            $goodsDetail['default_delivery_type'] = (int)explode(',',$goodsDetail['delivery_type'])[0];
        }

        Db::startTrans();
        try{
            // 轮播图加域名
            foreach($goodsDetail['goods_image'] as &$item) {
                $item['uri'] = empty($item['uri']) ? '' : UrlServer::getFileUrl($item['uri']);
            }
            // 会员价
            $goodsDetail['member_price'] = 0;
            // 会员价数组
            $member_price = [];
            foreach ($goodsDetail['goods_item'] as &$goods_item) {
                $is_member = Goods::where('id',$goods_item['goods_id'])->value('is_member');
                $goods_item['is_member'] = $is_member;
                if($is_member == 1 && $discount && $userId){
                    $goods_item['member_price'] = round($goods_item['price']* $discount/10,2);
                    $goodsDetail['member_price'] =  round($goods_item['price']* $discount/10,2);
                    $member_price[] = $goodsDetail['member_price'];
                }
                // 规格图片处理
                $goods_item['image'] = empty($goods_item['image']) ? $goodsDetail['image'] : $goods_item['image'];
            }

            !empty($member_price) && $goodsDetail['member_price'] = min($member_price);

            // 增加点击量
            $goodsDetail->clicks += 1;
            $goodsDetail->save();

            // 转数组
            $goodsDetailArr = $goodsDetail->toArray();
            $goodsDetailArr['poster'] = !empty($goodsDetailArr['poster']) ? UrlServer::getFileUrl($goodsDetailArr['poster']) : '';

            // 新增点击记录
            GoodsClick::create([
                'shop_id' => $goodsDetailArr['shop_id'],
                'user_id' => $userId,
                'goods_id' => $goodsId,
                'create_time' => time()
            ]);
            //店铺信息
            switch ($goodsDetailArr['shop']['type']){
                case 1 :
                    $type_desc = '官方自营';
                    break;
                case 2 :
                    $type_desc = '入驻商家';
                    break;
                default :
                    $type_desc = '入驻商家';
                    break;
            }
            $follow = Db::name('shop_follow')->where(['shop_id' => $goodsDetailArr['shop_id'],'status' => 1])->count('id');
            $goodsDetailArr['shop']['type_desc'] = $type_desc; //商家类型
            $goodsDetailArr['shop']['follow_num'] = $follow; //收藏人数

            //客服二维码
            $customer_image = ConfigServer::get('shop_customer_service','image','',$goodsDetailArr['shop_id']);
            if($customer_image){
                $customer_image = UrlServer::getFileUrl($customer_image);
            }
            $goodsDetailArr['shop']['customer_image'] = $customer_image;
            // 用户是否关注店铺
            $goodsDetailArr['shop']['shop_follow_status'] = 0;
            if($userId) { // 用户已登录
                $shopFollow = ShopFollow::where(['user_id'=>$userId, 'shop_id'=>$goodsDetailArr['shop_id']])->findOrEmpty();
                if(!$shopFollow->isEmpty()) {
                    $goodsDetailArr['shop']['shop_follow_status'] = $shopFollow['status'];
                }
            }

            // 店铺在售商品数量
            $goodsDetailArr['shop']['goods_on_sale'] = Goods::where($onSaleWhere)
                ->where('shop_id', $goodsDetailArr['shop_id'])
                ->count();

            // 店铺推荐商品列表(9个)
            $goodsDetailArr['shop']['goods_list'] = Goods::field('id,name,image,market_price,min_price')
                ->where($onSaleWhere)
                ->where([
                    'shop_id' => $goodsDetailArr['shop_id'],
                    'is_recommend' => 1, // 推荐
                ])
                ->order([
                    'sales_actual' => 'desc',
                    'id' => 'desc'
                ])
                ->limit(9)
                ->select()
                ->toArray();

            // 总销量 = 实际销量 + 虚拟销量
            $goodsDetailArr['sales_sum'] = $goodsDetailArr['sales_actual'] + $goodsDetailArr['sales_virtual'];
            // 标识活动信息
            $goodsDetailArr['activity'] = [
                'type' => 0,
                'type_desc' => '普通商品'
            ];
            // 检查商品是否在参与活动，替换商品价格
            $goodsDetailArr = self::checkActivity($goodsDetailArr);
            // 是否收藏
            $goodsDetailArr['is_collect'] = 0;
            if($userId) { // 非游客
                $goodsCollect = GoodsCollect::where([
                    'user_id' => $userId,
                    'goods_id' => $goodsId
                ])->findOrEmpty();
                if(!$goodsCollect->isEmpty()) {
                    $goodsDetailArr['is_collect'] = $goodsCollect->status ? 1 : 0;
                }
            }
            // 规格项及规格值信息
            $goodsDetailArr['goods_spec'] = GoodsSpec::with('spec_value')
                ->where('goods_id', $goodsId)->select();
            // 商品评价
            $commentCategory = GoodsCommentLogic::category(['goods_id'=>$goodsId]);
            $goodsDetailArr['comment']['percent'] = $commentCategory['percent'];

            $all_comment = Db::name('goods_comment')->where(['goods_id' => $goodsId])->sum('goods_comment');
            $goods_comment_count = Db::name('goods_comment')->where(['goods_id' => $goodsId])->count('id');
            if($goods_comment_count){
                $goods_comment = round($all_comment / $goods_comment_count,2);
                $goodsDetailArr['comment']['goods_comment'] = $goods_comment;
            }else{
                $goodsDetailArr['comment']['goods_comment'] = 0;
            }
            // 最新一条评论
            $one = GoodsComment::alias('gc')
                ->field('gc.id,gc.goods_comment,gc.create_time,gc.comment,u.avatar,u.nickname,g.name as goods_name')
                ->leftJoin('user u', 'u.id=gc.user_id')
                ->leftJoin('goods g', 'g.id=gc.goods_id')
                ->where([
                    ['gc.goods_id', '=', $goodsId],
                    ['gc.del', '=', 0],
                    ['gc.status', '=', 1],
                ])
                ->order('create_time', 'desc')
                ->findOrEmpty();
            if($one->isEmpty()) {
                $one = [];
            }else {
                $one = $one->toArray();
                // 头像
                $one['avatar'] = UrlServer::getFileUrl($one['avatar']);
                // 图片评价
                $one['image'] = GoodsCommentImage::where('goods_comment_id', $one['id'])->column('uri');
                foreach($one['image'] as $subKey => $subItem) {
                    $one['image'][$subKey] = UrlServer::getFileUrl($subItem);
                }
            }
            $goodsDetailArr['comment']['one'] = $one;

            // 判断是否是拼团商品
            $teamActivity = (new TeamActivity())
                ->field(['id,people_num,team_max_price,team_min_price,sales_volume,activity_end_time,share_title,share_intro'])
                ->where([
                    ['goods_id', '=', $goodsId],
                    ['audit', '=', 1],
                    ['status', '=', 1],
                    ['del', '=', 0],
                    ['activity_start_time', '<=', time()],
                    ['activity_end_time', '>=', time()]
            ])->findOrEmpty()->toArray();

            if ($teamActivity) {
                $teamFound = (new TeamFound())->alias('TF')
                    ->field(['TF.*', 'U.nickname,U.avatar'])
                    ->limit(8)
                    ->order('id desc')
                    ->where('TF.team_activity_id', '=', $teamActivity['id'])
                    ->where('TF.people','exp',' > TF.join ')
                    ->where([
                        ['status', '=', 0],
                        ['invalid_time', '>=', time()]
                    ])->join('user U', 'U.id=TF.user_id')
                      ->select()->toArray();

                foreach ($teamFound as &$found) {
                    unset($found['shop_id']);
                    unset($found['team_sn']);
                    unset($found['goods_snap']);
                    unset($found['team_end_time']);
                    $found['avatar'] = UrlServer::getFileUrl($found['avatar']);
                    $found['surplus_time'] = intval($found['invalid_time'] - time());
                }

                $teamActivity['share_title'] = !empty($teamActivity['share_title']) ? $teamActivity['share_title'] : $goodsDetailArr['name'];
                $teamActivity['share_intro'] = !empty($teamActivity['share_intro']) ? $teamActivity['share_intro'] : $goodsDetailArr['remark'];

                $goodsDetailArr['activity'] = ['type'=>2, 'type_desc'=>'拼团商品', 'info'=>$teamActivity, 'found'=>$teamFound];
                $teamGoods = (new TeamGoods())->where(['team_id'=>$teamActivity['id']])->select()->toArray();
                foreach ($goodsDetailArr['goods_item'] as &$item) {
                    foreach ($teamGoods as $team) {
                        if ($item['id'] === $team['item_id']) {
                            $item['team_price'] = $team['team_price'];
                        }
                    }
                }
            }

            // 预估佣金(计算出最高可得佣金)
            $goodsDetailArr['distribution'] = self::getDistribution($goodsId, $userId);

            // 虚拟浏览量
            $goodsDetailArr['clicks'] += $goodsDetailArr['clicks_virtual'];

            // 记录访问足迹
            event('Footprint', [
                'type'    => FootprintEnum::BROWSE_GOODS,
                'user_id' => $userId,
                'foreign_id' => $goodsId
            ]);

            Db::commit();
            return $goodsDetailArr;
        }catch(\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * 热销榜单
     */
    public static  function getHotList($get)
    {
        // 销售中商品：未删除/审核通过/已上架
        $where = [
            ['del', '=', GoodsEnum::DEL_NORMAL],  // 未删除
            ['status', '=', GoodsEnum::STATUS_SHELVES], // 上架中
            ['audit_status', '=', GoodsEnum::AUDIT_STATUS_OK], // 审核通过
        ];
        $order = [
            'sales_total' => 'desc', // 实际销量+虚拟销量倒序
            'sales_actual' => 'desc', // 实际销量倒序
            'id' => 'desc'
        ];

        return self::getGoodsListTemplate($where, $order, $get);
    }

    /**
     * 商品列表
     */
    public static function getGoodsList($get)
    {
        // 销售中商品：未删除/审核通过/已上架
        $where = [
            ['del', '=', GoodsEnum::DEL_NORMAL],  // 未删除
            ['status', '=', GoodsEnum::STATUS_SHELVES], // 上架中
            ['audit_status', '=', GoodsEnum::AUDIT_STATUS_OK], // 审核通过
        ];
        $order = [
            'sort_weight' => 'asc', // 商品权重，数字越小权重越大
            'sort' => 'asc',
            'id' => 'desc'
        ];
        return self::getGoodsListTemplate($where, $order, $get);
    }

    /**
     * 商品列表模板
     * 作用：代码复用
     */
    public static function getGoodsListTemplate($where, $order, $get)
    {
        if (!empty(self::filterShopsIds())) {
            // 过滤已删除、已冻结、已暂停营业、已到期的店铺
            $where[] = ['shop_id', 'not in', self::filterShopsIds()];
        }

        // 平台分类
        if(isset($get['platform_cate_id']) && !empty($get['platform_cate_id']) && filter_var($get['platform_cate_id'], FILTER_VALIDATE_INT)) {
            $where[] = ['first_cate_id|second_cate_id|third_cate_id', '=', $get['platform_cate_id']];
        }

        // 品牌
        if(isset($get['brand_id']) && !empty($get['brand_id']) && filter_var($get['brand_id'], FILTER_VALIDATE_INT)) {
            $where[] = ['brand_id', '=', $get['brand_id']];
        }

        // 关键词
        if(isset($get['keyword']) && !empty($get['keyword'])) {
            $where[] = ['name', 'like', '%'.trim($get['keyword']).'%'];
            if($get['user_id']) { // 记录关键词
                self::recordKeyword(trim($get['keyword']), $get['user_id']);
            }
        }

        // 店铺id
        if(isset($get['shop_id']) && !empty($get['shop_id']) && filter_var($get['shop_id'], FILTER_VALIDATE_INT)) {
            $where[] = ['shop_id', '=', $get['shop_id']];
        }
        
        // 店铺推荐
        if (Validate::must($get['is_recommend'] ?? '')) {
            $where[] = [ 'is_recommend', '=', $get['is_recommend'] ];
        }

        // 店铺分类
        if(isset($get['shop_cate_id']) && !empty($get['shop_cate_id']) && filter_var($get['shop_cate_id'], FILTER_VALIDATE_INT)) {
            $where[] = ['shop_cate_id', '=', $get['shop_cate_id']];
        }

        // 销量排序(实际销量 + 虚拟销量)
        if(isset($get['sort_by_sales']) && !empty($get['sort_by_sales'])) {
            $elt = ['sales_total'=> trim($get['sort_by_sales'])];
            $order = array_merge($elt, $order);
        }

        // 价格排序
        if(isset($get['sort_by_price']) && !empty($get['sort_by_price'])) {
            $elt = ['min_price'=> trim($get['sort_by_price'])];
            $order = array_merge($elt, $order);
        }

        // 新品排序
        if(isset($get['sort_by_create']) && !empty($get['sort_by_create'])) {
            $elt = ['create_time'=> trim($get['sort_by_create'])];
            $order = array_merge($elt, $order);
        }

        $field = 'id,image,name,min_price,market_price,sales_actual,first_cate_id,
        second_cate_id,third_cate_id,sort_weight,brand_id,shop_id,sales_virtual,
        (sales_actual + sales_virtual) as sales_total';

        $list = Goods::with(['shop'])
            ->field($field)
            ->where($where)
            ->order($order)
            ->page($get['page_no'], $get['page_size'])
            ->select();

        foreach ($list as $item) {
            $item['shop_type'] = $item['shop']['type'];
            unset($item['shop']);
        }

        $count = Goods::where($where)->count();

        $list = $list ? $list->toArray() : [];

        $more = is_more($count, $get['page_no'], $get['page_size']);

        $data = [
            'lists'         => $list,
            'page_no'       => $get['page_no'],
            'page_size'     => $get['page_size'],
            'count'         => $count,
            'more'          => $more
        ];
        return $data;
    }

    /**
     * 根据商品栏目获取商品列表
     */
    public static function getGoodsListByColumnId($columnId, $page_no, $page_size)
    {
        // 销售中商品：未删除/审核通过/已上架
        $onSaleWhere = [
            ['del', '=', GoodsEnum::DEL_NORMAL],
            ['status', '=', GoodsEnum::STATUS_SHELVES],
            ['audit_status', '=', GoodsEnum::AUDIT_STATUS_OK],
        ];

        if (!empty(self::filterShopsIds())) {
            // 过滤已删除、已冻结、已暂停营业、已到期的店铺
            $onSaleWhere[] = ['shop_id', 'not in', self::filterShopsIds()];
        }

        $order = [
            'sort_weight' => 'asc', // 数字越小，权重越大
            'sales_actual' => 'desc',
            'id' => 'desc'
        ];

        $list = Goods::field('id,name,image,market_price,min_price,sales_actual,column_ids,sort_weight,sales_virtual,(sales_actual + sales_virtual) as sales_total')
            ->where($onSaleWhere)
            ->whereFindInSet('column_ids', $columnId)
            ->order($order)
            ->page($page_no, $page_size)
            ->select();

        $count = Goods::where($onSaleWhere)
            ->whereFindInSet('column_ids', $columnId)
            ->count();

        $list = $list ? $list->toArray() : [];

        $more = is_more($count, $page_no, $page_size);

        $data = [
            'lists'          => $list,
            'page_no'       => $page_no,
            'page_size'     => $page_size,
            'count'         => $count,
            'more'          => $more
        ];
        return $data;
    }

    /**
     * @notes 获取已删除、已冻结、已暂停营业、已到期店铺的id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/7/20 14:29
     */
    public static function filterShopsIds()
    {
        // 已删除、已冻结、已暂停营业的店铺
        $invalidShops = Shop::field('id,name')->whereOr([
            ['del', '=', 1], // 已删除
            ['is_freeze', '=', 1], // 已冻结
            ['is_run', '=', 0] // 暂停营业
        ])->select()->toArray();

        // 已过期的店铺
        $expiredShops = Shop::field('id,name')->where([
            ['expire_time', '<>', 0],
            ['expire_time', '<=', time()],
        ])->select()->toArray();

        $filterShops = array_merge($invalidShops, $expiredShops);
        $filterShopsIds = array_column($filterShops, 'id');
        return $filterShopsIds;
    }

    /**
     * 记录关键词
     */
    public static function recordKeyword($keyword, $user_id)
    {
        $record = SearchRecord::where(['user_id'=>$user_id,'keyword'=>$keyword,'del'=>0])->find();
        if($record){
            // 有该关键词记录, 更新
            return SearchRecord::where(['id'=>$record['id']])->update(['count'=>Db::raw('count+1'),'update_time'=>time()]);
        }
        // 无该关键词记录 > 新增
        return SearchRecord::create([
            'user_id'=>$user_id,
            'keyword'=>$keyword,
            'count' => 1,
            'update_time' => time(),
            'del' => 0
        ]);
    }

    //检查商品是否正在参加活动
    public static function checkActivity($goods){
        // 获取正在秒杀的时段
        $seckill_time = SeckillGoodsLogic::getSeckillTimeIng();
        
        if($seckill_time === false) {
            // 不在秒杀时段，直接返回
            return $goods;
        }
        
        // 判断是否是秒杀中的商品
        $seckill_goods = SeckillGoods::where([
            ['del', '=', 0],
            ['seckill_id', '=', $seckill_time['id']],
            ['goods_id', '=', $goods['id']],
            ['review_status', '=', 1],
        ])->select()->toArray();
        
        if(!$seckill_goods) {
            // 不是秒杀商品
            return $goods;
        }
        // 判断参与日期是否包含今天
        $flag = false;
        $now = time();
        foreach($seckill_goods as $item) {
            $start_date_time = strtotime($item['start_date'].' ' . $seckill_time['start_time']);
            $end_date_time = strtotime($item['end_date'].' ' . $seckill_time['end_time']);
            if($start_date_time < $now && $end_date_time > $now) {
                $flag = true;
                // 获取该商品的秒杀信息
                $seckill_goods_info = SeckillGoods::where([
                    'goods_id' => $goods['id'],
                    'seckill_id' => $seckill_time['id'],
                    'start_date' => $item['start_date'],
                    'end_date' => $item['end_date'],
                ])->column('goods_id,item_id,price', 'item_id');
                break;
            }
        }
        
        if($flag === false) {
            // 参与日期不在今天
            return $goods;
        }
        // 确定是秒杀中的商品
        // 先将商品市场价换成原SKU最小价
        $goods['market_price'] = $goods['min_price'];
        // 替换活动价
        foreach($goods['goods_item'] as &$item) {
            // 商品价格替换为最小的秒杀价
            if($goods['min_price'] > $seckill_goods_info[$item['id']]['price']) {
                $goods['min_price'] = $seckill_goods_info[$item['id']]['price'];
            }
            // 原市场价替换为原SKU售价
            $item['market_price'] = $item['price'];
            // SKU替换秒杀价
            $item['price'] = $seckill_goods_info[$item['id']]['price'];
        }
        $today_date = date('Y-m-d');
        $goods['activity'] = [
            'type' => 1,
            'type_desc' => '秒杀商品',
            'end_time' => strtotime($today_date.' '.$seckill_time['end_time'])
        ];
        return $goods;

    }

    /**
     * @notes 获取商品分销信息
     * @param $goodsId
     * @param $userId
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/9/6 18:48
     */
    public static function getDistribution($goodsId, $userId)
    {
        $earnings = 0;
        $goods = Goods::findOrEmpty($goodsId)->toArray();
        $distributionGoods = DistributionGoods::where('goods_id', $goodsId)->select()->toArray();
        if(!empty($distributionGoods) && $distributionGoods[0]['is_distribution'] && $distributionGoods[0]['rule'] == 2) {
            foreach($distributionGoods as $item) {
                $earnings = max($earnings, round($goods['max_price'] * $item['first_ratio'] / 100, 2));
                $earnings = max($earnings, round($goods['max_price'] * $item['second_ratio'] / 100, 2));
            }
        }
        if(!empty($distributionGoods) && $distributionGoods[0]['is_distribution'] && $distributionGoods[0]['rule'] == 1) {
            $levels = DistributionLevel::select()->toArray();
            foreach($levels as $item) {
                $earnings = max($earnings, round($goods['max_price'] * $item['first_ratio'] / 100, 2));
                $earnings = max($earnings, round($goods['max_price'] * $item['second_ratio'] / 100, 2));
            }
        }

        // 详情页是否显示佣金
        $isShow = ConfigServer::get('distribution', 'is_show_earnings', 0);
        // 系统总分销开关
        $distributionOpen = ConfigServer::get('distribution', 'is_open', 0);
        // 商家信息-获取商家是否被禁用分销功能(is_distribution)
        $shop = Shop::findOrEmpty($goods['shop_id'])->toArray();

        if ($distributionOpen && $shop['is_distribution'] && $isShow) {
            //详情页佣金可见用户 0-全部用户 1-分销商
            $scope = ConfigServer::get('distribution', 'show_earnings_scope', 0);
            $user = Distribution::where(['user_id' => $userId])->findOrEmpty()->toArray();
            if ($scope && empty($user['is_distribution'])) {
                $isShow = 0;
            }
        } else {
            $isShow = 0;
        }
        
        return [
            'is_show' => $isShow,
            'earnings' => $earnings
        ];
    }
}
