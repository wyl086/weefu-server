<?php


namespace app\api\controller;


use app\api\logic\CommunityLogic;
use app\api\validate\CommunityArticleValidate;
use app\common\basics\Api;
use app\common\server\JsonServer;

/**
 * 种草社区相关
 * Class CommunityArticle
 * @package app\api\controller
 */
class Community extends Api
{

    public $like_not_need_login = ['cate', 'articleLists', 'detail', 'relationGoods', 'relationShop', 'topicArticle'];

    /**
     * @notes 获取已购商品列表
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/4/29 15:07
     */
    public function goods()
    {
        $get = $this->request->get();
        $result = CommunityLogic::getGoodsLists($this->user_id, $get, $this->page_no, $this->page_size);
        return JsonServer::success('', $result);
    }


    /**
     * @notes 获取已购店铺列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/4/29 15:40
     */
    public function shop()
    {
        $get = $this->request->get();
        $result = CommunityLogic::getShopLists($this->user_id, $get, $this->page_no, $this->page_size);
        return JsonServer::success('', $result);
    }


    /**
     * @notes 获取推荐话题
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/4/29 16:00
     */
    public function recommendTopic()
    {
        $result = CommunityLogic::getRecommendTopic();
        return JsonServer::success('', $result);
    }


    /**
     * @notes 获取话题列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/4/29 17:25
     */
    public function topicLists()
    {
        $get = $this->request->get();
        $result = CommunityLogic::getTopicLists($get);
        return JsonServer::success('', $result);
    }


    /**
     * @notes 获取分类
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/4/29 17:50
     */
    public function cate()
    {
        $result = CommunityLogic::getCate();
        return JsonServer::success('', $result);
    }


    /**
     * @notes 获取发现页的文章列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/4/29 18:09
     */
    public function articleLists()
    {
        $get = $this->request->get();
        $result = CommunityLogic::getArticleLists($get, $this->page_no, $this->page_size, $this->user_id);
        return JsonServer::success('', $result);
    }


    /**
     * @notes 发布文章
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/4/29 10:48
     */
    public function addArticle()
    {
        $post = $this->request->post();
        (new CommunityArticleValidate)->gocheck('add');
        $result = CommunityLogic::addArticle($this->user_id, $post);
        if (false === $result) {
            $error = CommunityLogic::getError() ?: '发布失败';
            return JsonServer::error($error);
        }
        return JsonServer::success('发布成功，坐等点赞和关注吧~');
    }

    /**
     * @notes 编辑文章
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/4/29 10:48
     */
    public function editArticle()
    {
        $post = $this->request->post();
        (new CommunityArticleValidate)->gocheck('edit');
        $result = CommunityLogic::editArticle($this->user_id, $post);
        if (false === $result) {
            $error = CommunityLogic::getError() ?: '编辑失败';
            return JsonServer::error($error);
        }
        return JsonServer::success('编辑成功');
    }


    /**
     * @notes 删除文章
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/5/7 10:28
     */
    public function delArticle()
    {
        $post = $this->request->post();
        (new CommunityArticleValidate)->gocheck('del');
        $result = CommunityLogic::delArticle($this->user_id, $post);
        if (false === $result) {
            return JsonServer::error(CommunityLogic::getError() ?: '删除失败');
        }
        return JsonServer::success('删除成功');
    }


    /**
     * @notes 关注用户
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/5/5 15:45
     */
    public function follow()
    {
        $post = $this->request->post();
        $result = CommunityLogic::followRelation($this->user_id, $post);
        if (false === $result) {
            return JsonServer::error(CommunityLogic::getError() ?: '操作失败');
        }
        return JsonServer::success('操作成功');
    }


    /**
     * @notes 点赞/取消点赞文章
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/5/5 16:12
     */
    public function giveLike()
    {
        $post = $this->request->post();
        $result = CommunityLogic::giveLike($this->user_id, $post);
        if (true !== $result) {
            return JsonServer::error(CommunityLogic::getError() ?: '操作失败');
        }
        return JsonServer::success('操作成功');
    }


    /**
     * @notes 关注页的文章列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/5/6 9:52
     */
    public function followArticle()
    {
        $result = CommunityLogic::getFollowArticle($this->user_id, $this->page_no, $this->page_size);
        return JsonServer::success('', $result);
    }


    /**
     * @notes 获取文章中关联商品列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/5/6 14:57
     */
    public function relationGoods()
    {
        $get = $this->request->get();
        $result = CommunityLogic::getRelationGoodsOrShop($get, 'goods');
        return JsonServer::success('', $result);
    }


    /**
     * @notes 获取文章中关联店铺列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/5/10 17:06
     */
    public function relationShop()
    {
        $get = $this->request->get();
        $result = CommunityLogic::getRelationGoodsOrShop($get, 'shop');
        return JsonServer::success('', $result);
    }


    /**
     * @notes 作品列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/5/6 10:46
     */
    public function worksLists()
    {
        $get = $this->request->get();
        $result = CommunityLogic::getWorksLists($this->user_id, $get, $this->page_no, $this->page_size);
        return JsonServer::success('', $result);
    }


    /**
     * @notes 点赞的列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/5/6 14:22
     */
    public function likeLists()
    {
        $get = $this->request->get();
        $result = CommunityLogic::getLikeLists($this->user_id, $get, $this->page_no, $this->page_size);
        return JsonServer::success('', $result);
    }


    /**
     * @notes 话题关联文章
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/5/6 16:22
     */
    public function topicArticle()
    {
        $get = $this->request->get();
        $result = CommunityLogic::getTopicArticle($get, $this->page_no, $this->page_size);
        if (false === $result) {
            return JsonServer::error(CommunityLogic::getError() ?: '获取失败');
        }
        return JsonServer::success('', $result);
    }


    /**
     * @notes 获取文章详情
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/5/6 18:08
     */
    public function detail()
    {
        $result = CommunityLogic::detail($this->user_id, input('id/d'));
        return JsonServer::success('', $result);
    }

}