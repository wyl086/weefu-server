<?php


namespace app\api\logic;


use app\common\basics\Logic;
use app\common\logic\CommunityArticleLogic;
use app\common\enum\{
    CommunityCommentEnum,
    GoodsEnum,
    OrderEnum,
    ShopEnum,
    CommunityArticleEnum,
    CommunityLikeEnum
};
use app\common\model\{
    goods\Goods,
    order\Order,
    order\OrderGoods,
    shop\Shop,
    user\User,
    community\CommunityArticle,
    community\CommunityArticleImage,
    community\CommunityCategory,
    community\CommunityComment,
    community\CommunityFollow,
    community\CommunityLike,
    community\CommunityTopic
};
use app\common\server\{
    ConfigServer,
    UrlServer
};
use think\facade\Db;


/**
 * 社区相关
 * Class CommunityArticleLogic
 * @package app\api\logic
 */
class CommunityLogic extends Logic
{

    /**
     * @notes 获取商品列表
     * @param $user_id
     * @param $params
     * @param $page
     * @param $size
     * @return array
     * @author 段誉
     * @date 2022/4/29 15:06
     */
    public static function getGoodsLists($user_id, $params, $page, $size)
    {
        $where = [
            ['del', '=', GoodsEnum::DEL_NORMAL],  // 未删除
            ['status', '=', GoodsEnum::STATUS_SHELVES], // 上架中
            ['audit_status', '=', GoodsEnum::AUDIT_STATUS_OK], // 审核通过
        ];

        $type = !empty($params['type']) ? $params['type'] : 'all';

        if ('buy' == $type) {
            $condition = [
                ['user_id', '=', $user_id],
                ['order_status', '>=', OrderEnum::ORDER_STATUS_NO_PAID],
            ];
            $order_id = Order::where($condition)->column('id');
            $goods_id = OrderGoods::whereIn('order_id', $order_id)->column('goods_id');
            $where[] = ['id', 'in', $goods_id];
        }

        if (!empty($params['keyword'])) {
            $where[] = ['name', 'like', '%' . $params['keyword'] . '%'];
        }

        $model = new Goods();
        $field = ['id' => 'goods_id', 'image', 'name' => 'goods_name', 'min_price' => 'goods_price', 'shop_id'];
        $goods = $model->field($field)->where($where)->select();
        $count = $model->where($where)->count();

        foreach ($goods as &$item) {
            $item['shop_name'] = $item->shop->name;
        }

        $goods->hidden(['shop']);

        return [
            'list' => $goods->toArray(),
            'page' => $page,
            'size' => $size,
            'count' => $count,
            'more' => is_more($count, $page, $size)
        ];
    }


    /**
     * @notes 已购买店铺或全部营业店铺
     * @param $user_id
     * @param $params
     * @param $page
     * @param $size
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/4/29 15:39
     */
    public static function getShopLists($user_id, $params, $page, $size)
    {
        $where = [
            ['is_freeze', '=', ShopEnum::SHOP_FREEZE_NORMAL], // 未冻结
            ['del', '=', 0], // 未删除
            ['is_run', '=', ShopEnum::SHOP_RUN_OPEN], // 未暂停营业
        ];

        $type = !empty($params['type']) ? $params['type'] : 'all';

        if ('buy' == $type) {
            $condition = [
                ['order_status', '>=', OrderEnum::ORDER_STATUS_NO_PAID],
                ['user_id', '=', $user_id]
            ];
            $shop_id = Order::where($condition)->column('shop_id');
            $where[] = ['id', 'in', $shop_id];
        }

        if (!empty($params['keyword'])) {
            $where[] = ['name', 'like', '%' . $params['keyword'] . '%'];
        }

        $whereRaw = 'expire_time =0 OR expire_time > '. time();

        $field = ['id', 'name', 'logo'];
        $lists = Shop::field($field)->where($where)->whereRaw($whereRaw)->select()->toArray();
        $count = Shop::where($where)->whereRaw($whereRaw)->count();

        return [
            'list' => $lists,
            'page' => $page,
            'size' => $size,
            'count' => $count,
            'more' => is_more($count, $page, $size)
        ];
    }


    /**
     * @notes 获取指定数量话题
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/4/29 15:59
     */
    public static function getRecommendTopic()
    {
        return CommunityTopic::field(['id', 'name', 'cid', 'image'])
            ->where(['is_show' => 1, 'del' => 0])
            ->order(['sort' => 'desc', 'id' => 'desc'])
            ->limit(3)
            ->select()->toArray();
    }


    /**
     * @notes 获取话题列表
     * @param $get
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/4/29 17:24
     */
    public static function getTopicLists($get)
    {
        $where[] = ['t.del', '=', 0];
        $where[] = ['t.is_show', '=', 1];
        if (!empty($get['name'])) {
            $where[] = ['t.name', 'like', '%' . $get['name'] . '%'];
        }

        $model = new CommunityCategory();
        $lists = $model->alias('c')
            ->field(['c.id, c.name'])
            ->with(['topic' => function ($query) use ($where) {
                $query->alias('t')->field(['id', 'cid', 'name', 'image', 'click'])
                    ->where($where)
                    ->order(['sort' => 'desc', 'id' => 'desc']);
            }])
            ->where($where)
            ->join('community_topic t', 't.cid = c.id')
            ->group('c.id')
            ->select()
            ->toArray();

        if (empty($get['name'])) {
            $recommend_topic = (new CommunityTopic())->field(['id', 'cid', 'name', 'image', 'click'])
                ->where(['del' => 0, 'is_show' => 1, 'is_recommend' => 1])
                ->select()
                ->toArray();
            $recommend = ['id' => 0, 'name' => '推荐', 'topic' => $recommend_topic];
            array_unshift($lists, $recommend);
        }

        return $lists;
    }


    /**
     * @notes 获取分类
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/4/29 17:49
     */
    public static function getCate()
    {
        $lists = CommunityCategory::field(['id', 'name'])
            ->where(['is_show' => 1, 'del' => 0])
            ->order(['sort' => 'asc', 'id' => 'desc'])
            ->select()->toArray();
        return $lists;
    }


    /**
     * @notes 获取文章列表
     * @param $get
     * @param $page
     * @param $size
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/4/29 18:09
     */
    public static function getArticleLists($get, $page, $size, $user_id = null)
    {
        $where[] = ['del', '=', 0];
        $where[] = ['status', '=', CommunityArticleEnum::STATUS_SUCCESS];
        if (!empty($get['cate_id'])) {
            $where[] = ['cate_id', '=', $get['cate_id']];
        }
        if (!empty($get['topic_id'])) {
            $where[] = ['topic_id', '=', $get['topic_id']];
        }
        if (!empty($get['keyword'])) {
            $where[] = ['content', 'like', '%' . trim($get['keyword']) . '%'];
            if (!is_null($user_id)) {
                // 记录关键词
                CommunitySearchRecordLogic::recordKeyword(trim($get['keyword']), $user_id);
            }
        }

        $sort = [];
        if (!empty($get['sort_hot'])) {
            $sort = ['like' => $get['sort_hot'], 'id' => 'desc'];
        }
        if (!empty($get['sort_new'])) {
            $sort = ['id' => $get['sort_new'], 'like' => 'desc'];
        }
        if (empty($sort)) {
            $sort = ['like' => 'desc', 'id' => 'desc'];
        }

        $model = new CommunityArticle();
        $count = $model->where($where)->count();
        $lists = $model
            ->with(['user' => function ($query) {
                $query->field(['id', 'nickname', 'avatar']);
            }])
            ->where($where)
            ->field(['id', 'user_id', 'cate_id', 'image', 'content', 'like', 'create_time'])
            ->page($page, $size)
            ->order($sort)
            ->select()
            ->bindAttr('user', ['nickname', 'avatar'])
            ->hidden(['user'])
            ->toArray();

        // 点赞的文章
        $likes_article = [];
        if (!is_null($user_id)) {
            // 点赞的文章
            $likes_article = CommunityLike::where([
                'user_id' => $user_id,
                'type' => CommunityLikeEnum::TYPE_ARTICLE
            ])->column('relation_id');
        }

        foreach ($lists as &$item) {
            $item['avatar'] = !empty($item['avatar']) ? UrlServer::getFileUrl($item['avatar']) : '';
            $item['is_like'] = in_array($item['id'], $likes_article) ? 1 : 0;
        }

        // 关注的人是否有新作品
        $has_new = CommunityArticleLogic::hasNew($user_id);

        return [
            'has_new' => $has_new,
            'list' => $lists,
            'page' => $page,
            'size' => $size,
            'count' => $count,
            'more' => is_more($count, $page, $size)
        ];
    }


    /**
     * @notes 发布文章
     * @param int $user_id
     * @param array $post
     * @return bool
     * @author 段誉
     * @date 2022/4/29 10:46
     */
    public static function addArticle(int $user_id, array $post): bool
    {
        Db::startTrans();
        try {
            // 处理数据
            $data = self::getEditArticleData($user_id, $post);

            // 新增文章信息
            $article = CommunityArticle::create($data);

            // 新增文章关联图片
            self::addArticleImage($post['image'], $article['id']);

            // 更新关联话题文章数量
            if (!empty($post['topic_id'])) {
                CommunityTopic::where(['id' => $post['topic_id']])->inc('article_num')->update();
            }

            // 通知粉丝有新作品
            CommunityArticleLogic::noticeFans($user_id, $article['status']);

            Db::commit();
            return true;

        } catch (\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }





    /**
     * @notes 编辑文章
     * @param int $user_id
     * @param array $post
     * @return bool
     * @author 段誉
     * @date 2022/5/7 9:42
     */
    public static function editArticle(int $user_id, array $post)
    {
        Db::startTrans();
        try {
            // 更新文章数据
            $data = self::getEditArticleData($user_id, $post);
            $article = CommunityArticle::findOrEmpty($post['id']);
            if ($article->isEmpty()) {
                throw new \Exception('信息缺失');
            }
            $article->save($data);

            // 删除旧的关联图片
            CommunityArticleImage::where(['article_id' => $post['id']])->delete();

            self::addArticleImage($post['image'], $post['id']);

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 删除文章
     * @param int $user_id
     * @param $post
     * @return bool
     * @author 段誉
     * @date 2022/5/10 16:16
     */
    public static function delArticle(int $user_id, $post)
    {
        Db::startTrans();
        try {
            // 删除文章
            $article = CommunityArticle::where(['user_id' => $user_id, 'id' => $post['id']])->find();
            $article->del = 1;
            $article->update_time = time();
            $article->save();

            if (!empty($article['topic_id'])) {
                // 更新话题文章数量
                CommunityTopic::decArticleNum($article['topic_id']);
            }

            Db::commit();
            return true;

        } catch (\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 文章详情
     * @param $user_id
     * @param $id
     * @return array
     * @author 段誉
     * @date 2022/5/6 18:08
     */
    public static function detail($user_id, $id)
    {
        $result = CommunityArticle::with([
            'images', 'user' => function ($query) {
                $query->field(['id', 'nickname', 'avatar']);
            },
            'topic' => function ($query) {
                $query->field(['id', 'name']);
            }])
            ->append(['status_desc', 'goods_data', 'shop_data'])
            ->findOrEmpty($id)
            ->toArray();
        if (empty($result['id'])) {
            return [];
        }

        // 是否已关注
        $is_follow = CommunityFollow::where([
            'user_id' => $user_id,
            'follow_id' => $result['user_id'],
            'status' => 1
        ])->findOrEmpty();

        // 是否已点赞
        $is_like = CommunityLike::where([
            'user_id' => $user_id,
            'relation_id' =>$id,
            'type' => CommunityLikeEnum::TYPE_ARTICLE
        ])->findOrEmpty();

        $comment_count = CommunityComment::where([
            'del' => 0,
            'article_id' => $id,
            'status' => CommunityCommentEnum::STATUS_SUCCESS
        ])->count();

        $result['is_follow'] = !$is_follow->isEmpty() ? 1 : 0;
        $result['is_like'] = !$is_like->isEmpty() ? 1 : 0;
        // 关联商品数量
        $result['total_goods'] = count($result['goods']);
        // 关联店铺数量
        $result['total_shop'] = count($result['shop']);
        // 评论数量
        $result['total_comment'] = $comment_count;
        // 当前用户是否为文章作者
        $result['is_author'] = ($user_id == $result['user_id']) ? 1 : 0;
        $result['user']['avatar'] = !empty($result['user']['avatar']) ? UrlServer::getFileUrl($result['user']['avatar']) : '';
        // 增加话题点击量
        CommunityTopic::where(['id' => $result['topic_id']])->inc('click')->update();
        // 审核状态描述
        $result['audit_remark_desc'] = CommunityArticleEnum::getStatusRemarkDesc($result);

        return $result;
    }


    /**
     * @notes 获取编辑文章数据
     * @param int $user_id
     * @param array $post
     * @return array
     * @throws \Exception
     * @author 段誉
     * @date 2022/5/7 9:52
     */
    public static function getEditArticleData(int $user_id, array $post)
    {
        $data = [
            'user_id' => $user_id,
            'content' => $post['content'],
            'image' => !empty($post['image']) ? reset($post['image']) : '',
            'goods' => !empty($post['goods']) ? array_unique(array_values($post['goods'])) : '',
            'shop' => !empty($post['shop']) ? array_unique(array_values($post['shop'])) : '',
            'topic_id' => 0
        ];

        if (!empty($post['topic_id'])) {
            $topic = CommunityTopic::where(['id' => $post['topic_id'], 'is_show' => 1])->findOrEmpty();
            if ($topic->isEmpty()) {
                throw new \Exception('所选话题不存在');
            }
            $data['cate_id'] = $topic['cid'];
            $data['topic_id'] = $post['topic_id'];
        }

        // 如果是无需审核的，状态直接为已审核
        $config = ConfigServer::get('community', 'audit_article', 1);
        if ($config == 0) {
            $data['status'] = CommunityArticleEnum::STATUS_SUCCESS;
            $data['audit_time'] = time();
        } else {
            $data['status'] = CommunityArticleEnum::STATUS_WAIT;
        }
        return $data;
    }


    /**
     * @notes 添加文章关联图片
     * @param $image
     * @param $article_id
     * @throws \Exception
     * @author 段誉
     * @date 2022/5/7 9:52
     */
    public static function addArticleImage($image, $article_id)
    {
        if (!empty($image)) {
            $images = [];
            foreach ($image as $item) {
                $images[] = [
                    'article_id' => $article_id,
                    'image' => $item,
                ];
            }
            (new CommunityArticleImage())->saveAll($images);
        }
    }


    /**
     * @notes 关注用户
     * @param $user_id
     * @param $post
     * @return bool
     * @author 段誉
     * @date 2022/5/5 15:44
     */
    public static function followRelation($user_id, $post)
    {
        try {
            if (!isset($post['follow_id']) || !isset($post['status'])) {
                throw new \Exception('参数缺失');
            }

            if ($user_id == $post['follow_id']) {
                throw new \Exception('不可关注自己喔');
            }

            // 要关注的用户是否存在
            $follow = User::where(['del' => 0, 'id' => $post['follow_id']])->findOrEmpty();
            if ($follow->isEmpty()) {
                throw new \Exception('该用户信息缺失');
            }

            // 是否已有关注记录
            $where = ['user_id' => $user_id, 'follow_id' => $post['follow_id']];
            $relation = CommunityFollow::where($where)->findOrEmpty();

            // 取消关注
            if ($relation->isEmpty()) {
                CommunityFollow::create([
                    'user_id' => $user_id,
                    'follow_id' => $post['follow_id'],
                    'status' => $post['status']
                ]);
            } else {
                CommunityFollow::where(['id' => $relation['id']])->update([
                    'status' => $post['status']
                ]);
            }

            return true;

        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 点赞
     * @param $user_id
     * @param $post
     * @return bool
     * @author 段誉
     * @date 2022/5/9 15:39
     */
    public static function giveLike($user_id, $post)
    {
        try {
            if (!isset($post['status']) || !isset($post['id'])) {
                throw new \Exception('参数缺失');
            }

            if (isset($post['type']) && !in_array($post['type'], CommunityLikeEnum::LIKE_TYPE)) {
                throw new \Exception('类型错误');
            }

            $type = $post['type'] ?? CommunityLikeEnum::TYPE_ARTICLE;

            $where = [
                'user_id' => $user_id,
                'relation_id' => $post['id'],
                'type' => $type
            ];

            // 点赞
            if ($post['status']) {
                $record = CommunityLike::where($where)->findOrEmpty();
                if (!$record->isEmpty()) {
                    return true;
                }
                CommunityLike::create([
                    'type' => $type,
                    'user_id' => $user_id,
                    'relation_id' => $post['id'],
                ]);
                if ($type == CommunityLikeEnum::TYPE_ARTICLE) {
                    CommunityArticle::incLike($post['id']);
                } else {
                    CommunityComment::incLike($post['id']);
                }
                return true;
            }

            // 取消点赞
            $res = CommunityLike::where($where)->delete();
            if ($res) {
                if ($type == CommunityLikeEnum::TYPE_ARTICLE) {
                    CommunityArticle::decLike($post['id']);
                } else {
                    CommunityComment::decLike($post['id']);
                }
            }
            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 获取关注的文章列表
     * @param $user_id
     * @param $page
     * @param $size
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/5/6 9:48
     */
    public static function getFollowArticle($user_id, $page, $size)
    {
        $follow_ids = CommunityFollow::where(['user_id' => $user_id, 'status' => 1])->column('follow_id');

        $lists = CommunityArticle::with([
            'images',
            'user' => function ($query) {
                $query->field(['id', 'nickname', 'avatar']);
            },
            'topic' => function ($query) {
                $query->field(['id', 'name']);
            }])
            ->where(['status' => CommunityArticleEnum::STATUS_SUCCESS, 'del' => 0])
            ->whereIn('user_id', $follow_ids)
            ->page($page, $size)
            ->order(['id' => 'desc', 'like' => 'desc'])
            ->append(['goods_data', 'shop_data'])
            ->select();

        $count = CommunityArticle::where(['status' => CommunityArticleEnum::STATUS_SUCCESS, 'del' => 0])
            ->whereIn('user_id', $follow_ids)
            ->count();

        $likes = CommunityLike::where([
            'user_id' => $user_id,
            'type' => CommunityLikeEnum::TYPE_ARTICLE
        ])->column('relation_id');

        foreach ($lists as $item) {
            $item['user']['avatar'] = UrlServer::getFileUrl($item['user']['avatar']);
            $item['create_time'] = friend_date(strtotime($item['create_time']));
            $item['total_goods'] = count($item['goods']);
            $item['total_shop'] = count($item['shop']);
            $item['total_comment'] = 0;
            $item['is_like'] = in_array($item['id'], $likes) ? 1 : 0;
        }

        // 清除未读缓存
        CommunityArticleLogic::delUnRead($user_id);

        $result = [
            'list' => $lists->toArray(),
            'page' => $page,
            'size' => $size,
            'count' => $count,
            'more' => is_more($count, $page, $size)
        ];
        return $result;
    }



    /**
     * @notes 文章关联商品或店铺
     * @param $get
     * @param string $type
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/5/10 17:05
     */
    public static function getRelationGoodsOrShop($get, $type)
    {
        if (empty($get['id'])) {
            return [];
        }
        $article = CommunityArticle::findOrEmpty($get['id']);
        if ($article->isEmpty() || $article['del'] == 1) {
            return [];
        }

        if ($type == 'goods') {
            $field = ['id', 'image', 'name', 'min_price' => 'goods_price', 'shop_id'];
            $lists = Goods::field($field)
                ->where('id', 'in', $article['goods'])
                ->select()
                ->toArray();
        } else {
            $field = ['id', 'name', 'logo'];
            $lists = Shop::field($field)
                ->where('id', 'in', $article['shop'])
                ->select()
                ->toArray();

            foreach ($lists as &$item) {
                $item['logo'] = UrlServer::getFileUrl($item['logo']);
            }
        }
        return $lists;
    }


    /**
     * @notes 获取作品列表
     * @param $user_id
     * @param $get
     * @param $page
     * @param $size
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/5/6 10:46
     */
    public static function getWorksLists($user_id, $get, $page, $size)
    {
        $field = ['id', 'image', 'content', 'like', 'status', 'create_time', 'audit_remark'];

        // 文章查询条件
        $where = [['user_id', '=', $user_id]];
        if (!empty($get['user_id'])) {
            $where = [['user_id', '=', $get['user_id']]];
            $where[] = ['status', '=', CommunityArticleEnum::STATUS_SUCCESS];
        }
        $where[] = ['del', '=', 0];

        $count = CommunityArticle::where($where)->count();
        $lists = CommunityArticle::field($field)
            ->where($where)
            ->page($page, $size)
            ->order(['id' => 'desc', 'like' => 'desc'])
            ->append(['status_desc'])
            ->select();

        $likes = CommunityLike::where([
            'user_id' => $user_id,
            'type' => CommunityLikeEnum::TYPE_ARTICLE
        ])->column('relation_id');

        foreach ($lists as $item) {
            $item['create_time'] = friend_date(strtotime($item['create_time']));
            $item['is_like'] = in_array($item['id'], $likes) ? 1 : 0;
            $item['audit_remark_desc'] = CommunityArticleEnum::getStatusRemarkDesc($item, false);
        }

        $result = [
            'list' => $lists->toArray(),
            'page' => $page,
            'size' => $size,
            'count' => $count,
            'more' => is_more($count, $page, $size)
        ];
        return $result;
    }


    /**
     * @notes 获取点赞的列表
     * @param $user_id
     * @param $get
     * @param $page
     * @param $size
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/5/6 14:21
     */
    public static function getLikeLists($user_id, $get, $page, $size)
    {
        $where = [['user_id', '=', $user_id]];
        if (!empty($get['user_id'])) {
            $where = [['user_id', '=', $get['user_id']]];
        }
        $article_ids = CommunityLike::where($where)
            ->where(['type' => CommunityLikeEnum::TYPE_ARTICLE])
            ->column('relation_id');

        $article_where[] = ['del', '=', 0];
        $article_where[] = ['status', '=', CommunityArticleEnum::STATUS_SUCCESS];
        $article_where[] = ['id', 'in', $article_ids];

        $field = ['id', 'image', 'content', 'like', 'status', 'create_time', 'user_id'];

        $count = CommunityArticle::where($article_where)->count();
        $lists = CommunityArticle::with(['user' => function($query) {
            $query->field(['id', 'nickname', 'avatar']);
        }])->field($field)
            ->where($article_where)
            ->page($page, $size)
            ->order(['id' => 'desc', 'like' => 'desc'])
            ->select()
            ->bindAttr('user', ['nickname', 'avatar'])
            ->hidden(['user']);

        foreach ($lists as $item) {
            $item['create_time'] = friend_date(strtotime($item['create_time']));
            $item['is_like'] = 1;
            $item['avatar'] = !empty($item['avatar']) ? UrlServer::getFileUrl($item['avatar']) : '';
        }

        $result = [
            'list' => $lists->toArray(),
            'page' => $page,
            'size' => $size,
            'count' => $count,
            'more' => is_more($count, $page, $size)
        ];
        return $result;
    }


    /**
     * @notes 话题关联文章
     * @param $get
     * @param $page
     * @param $size
     * @return array|false
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/5/6 16:22
     */
    public static function getTopicArticle($get, $page, $size)
    {
        $topic_id = $get['topic_id'] ?? 0;
        $topic = CommunityTopic::findOrEmpty($topic_id);

        if ($topic->isEmpty()) {
            self::$error = '话题信息不存在';
            return false;
        }

        $result = [
            'click' => $topic['click'],
            'lists' => self::getArticleLists($get, $page, $size),
        ];
        return $result;
    }


}