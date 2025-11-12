<?php
// +----------------------------------------------------------------------
// | Multshop多商户商城系统
// +----------------------------------------------------------------------
// | author: multshopTeam
// +----------------------------------------------------------------------

namespace app\api\logic;

use app\common\basics\Logic;
use app\common\enum\GoodsEnum;
use app\common\enum\ShopEnum;
use app\common\model\content\Article;
use app\common\model\goods\Goods;
use app\common\server\ConfigServer;
use app\common\model\shop\ShopFollow;
use app\common\model\shop\ShopCategory;
use app\common\server\UrlServer;
use think\facade\Db;

class PcLogic extends Logic
{

    /**
     * Notes:pc端获取公共数据
     * @param $user_id int 用户id
     * @return array
     * @author:  2021/3/5 17:47
     */
    public static function commonData($user_id)
    {

        $article = Db::name('article')
            ->where(['del' => 0, 'is_notice' => 1, 'is_show' => 1])
            ->order('create_time desc')
            ->field('id,title')
            ->limit(3)
            ->select();
        $cart_num = 0;
        $coupon_num = 0;
        $nickname = '';

        if ($user_id) {
            $cart_num = Db::name('cart')->where(['user_id' => $user_id])->sum('goods_num');
            $coupon_num = Db::name('coupon_list')->where(['user_id' => $user_id, 'del' => 0, 'status' => 0])->count();
            $nickname = Db::name('user')->where(['id' => $user_id])->value('nickname');
        }

        return [
            'article' => $article,
            'logo' => UrlServer::getFileUrl(ConfigServer::get('website', 'pc_logo')),
            'name' => ConfigServer::get('website', 'name', ''),
            'cart_num' => $cart_num,
            'coupon_num' => $coupon_num,
            'nickname' => $nickname,
            'oa_qr_code' => UrlServer::getFileUrl(ConfigServer::get('oa', 'qr_code', '')),
            'mnp_qr_code' => UrlServer::getFileUrl(ConfigServer::get('mnp', 'qr_code', '')),
        ];
    }

    /**
     * Notes:获取商品列表
     * @param $page int 页码
     * @param $size int 每页数量
     * @param $name string 商品名称
     * @param $category_id int 分类id
     * @param $type int 类型：1-热销榜单；2-新品推荐；3-好物优选
     * @param $sort_type string 筛选类型:sales_sum-销量筛选；price-价格筛选
     * @param $sort string 排序方式：desc-降序；asc-升序
     * @return array
     * @author:  2021/3/6 9:57
     */
    public static function goodsList($page, $size, $name, $category_id,$shop_id, $type, $sort_type, $sort)
    {
        $where[] = ['del', '=', GoodsEnum::DEL_NORMAL];
        $where[] = ['status', '=', GoodsEnum::STATUS_SHELVES];
        $where[] = ['audit_status', '=', GoodsEnum::AUDIT_STATUS_OK];

        // 过滤已删除、已冻结、已暂停营业、已到期的店铺
        $banShopIds = GoodsLogic::filterShopsIds();
        if (!empty($banShopIds)) {
            $where[] = ['shop_id', 'not in', $banShopIds];
        }

        //按商品名称搜索
        if ($name) {
            $where[] = ['name', 'like', '%' . $name . '%'];
        }
        //按商品分类搜索
        if ($category_id) {
            $where[] = ['shop_cate_id', '=', $category_id];
        }
        //按商品名称搜索
        if ($shop_id) {
            $where[] = ['shop_id','=',$shop_id];
        }
        //按类型筛选
        if (1 != $type) {
            switch ($type) {
                case 2:
                    $where[] = ['is_new', '=', 1];
                    break;
                case 3:
                    $where[] = ['is_best', '=', 1];
                    break;
            }
        }
        //按排序条件显示
        $order = [];
        if ($sort_type && $sort) {
            $order = [$sort_type => $sort];
        }

        $goods = new Goods();

        $count = $goods
            ->where($where)
            ->count();

        $list = $goods
            ->where($where)
            ->field('id,name,image,min_price as price,market_price,sales_actual + sales_virtual as sales_sum')
            ->order($order)
            ->page($page, $size)
            ->select();

        $more = is_more($count, $page, $size);  //是否有下一页

        return [
            'list' => $list,
            'page' => $page,
            'size' => $size,
            'count' => $count,
            'more' => $more
        ];
    }

    /**
     * Notes:修改用户信息
     * @param $post array 用户信息
     * @return int|string
     * @author:  2021/3/8 19:07
     * @throws \think\exception\PDOException
     * @throws \think\Exception
     */
    public static function changeUserInfo($post)
    {

        $data = [
            'nickname' => $post['nickname'],
            'sex' => $post['sex'],
            'update_time' => time(),
        ];
        Db::name('user')->where(['id' => $post['user_id']])->update($data);
        return true;
    }


    public static function categoryThirdTree()
    {

//        $cache = Cache::get('goods_category_'.);
//        if ($cache) {
//            return $cache;
//        }
        $lists = Db::name('goods_category')->where(['is_show' => 1, 'del' => 0, 'level' => 1])->order('sort asc')->column('id,name,pid,image,level', 'id');
        $level2 = Db::name('goods_category')->where(['is_show' => 1, 'del' => 0, 'level' => 2])->order('sort asc')->column('id,name,pid,image,level', 'id');
        $level3 = Db::name('goods_category')->where(['is_show' => 1, 'del' => 0, 'level' => 3])->order('sort asc')->field('id,name,pid,image,level')->select();

        //挂载第二级
        foreach ($level3 as $list3) {
            if (isset($level2[$list3['pid']])) {
                $list3['image'] = UrlServer::getFileUrl($list3['image']);
                $list3['type'] = 1;
                $level2[$list3['pid']]['sons'][] = $list3;
            }
        }
        //挂载第一级、并移除没有下级的二级分类
        foreach ($level2 as $key2 => $list2) {
            if (isset($lists[$list2['pid']])) {
                $list2['type'] = 1;
                $list2['image'] = UrlServer::getFileUrl($list2['image']);
                $lists[$list2['pid']]['sons'][] = $list2;
            }
        }


        //移除没有完整的三级分类
        foreach ($lists as $key1 => $list1) {
            if (!isset($list1['sons'])) {
                $lists[$key1]['sons'] = [];
            }
            $lists[$key1]['image'] = UrlServer::getFileUrl($list1['image']);
            $lists[$key1]['type'] = 1;
        }


//        Cache::set('goods_category_pc'.$client, array_values($lists));
        return array_values($lists);
    }

    public static function articleDetail($id)
    {
        $article = Article::field('id,title,create_time,visit,content')
            ->where(['id' => $id])
            ->findOrEmpty();

        if($article->isEmpty()) {
            $article = [];
        } else {
            $article->visit = $article->visit + 1;
            $article->save();
            $article = $article->toArray();
        }

        $recommend_list = Db::name('article')
            ->where([['del','=','0'], ['id','<>',$id]])
            ->field('id,title,image,visit')
            ->order('visit desc')
            ->limit(5)
            ->select()
            ->toArray();
        foreach ($recommend_list as &$recommend){
            $recommend['image'] = UrlServer::getFileUrl($recommend['image']);
        }
        $article['recommend_list'] = $recommend_list;
        return $article;
    }

    /**
     * @notes PC我的店铺收藏列表
     * @param $get
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/10/28 5:09 下午
     */
    public static function shopFollowList($get)
    {
        $where = [
            'sf.user_id' => $get['user_id'],
            'sf.status' => 1
        ];

        $lists = ShopFollow::alias('sf')
            ->field('s.id,s.name,s.cid,s.type,s.logo,s.score,s.cover,sf.shop_id')
            ->leftJoin('shop s', 's.id=sf.shop_id')
            ->where($where)
            ->order('sf.update_time', 'desc')
            ->page($get['page_no'], $get['page_size'])
            ->select()
            ->toArray();

        $count = ShopFollow::alias('sf')->where($where)->count();

        $typeDesc = [1=>'官方自营', 2=>'入驻商家'];
        foreach($lists as &$item) {
            // 店铺推荐商品
            $goodsWhere = [
                ['del', '=', GoodsEnum::DEL_NORMAL],  // 未删除
                ['status', '=', GoodsEnum::STATUS_SHELVES], // 上架中
                ['audit_status', '=', GoodsEnum::AUDIT_STATUS_OK], // 审核通过
                ['is_recommend', '=', 1], // 推荐商品
                ['shop_id', '=', $item['id']]
            ];
            $item['goods_list'] = Goods::field('id,image,name,min_price,market_price')
                ->where($goodsWhere)
                ->order([
                    'sort_weight' => 'asc',
                    'id' => 'desc'
                ])
                ->limit(5)
                ->select()
                ->toArray();
            // 商家类型
            $item['type_desc'] = $typeDesc[$item['type']];
            // 主营类目
            $item['cid_desc'] = ShopCategory::where('id', $item['cid'])->value('name');
            // logo
            $item['logo'] = UrlServer::getFileUrl($item['logo']);
            $item['cover'] = $item['cover'] ? UrlServer::getFileUrl($item['cover']) : UrlServer::getFileUrl(ShopEnum::DEFAULT_COVER);
        }

        $data = [
            'lists' => $lists,
            'count' => $count,
            'more' => is_more($count, $get['page_no'], $get['page_size']),
            'page_no' => $get['page_no'],
            'page_size' => $get['page_size'],
        ];
        return $data;
    }
}