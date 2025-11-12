<?php
namespace app\api\controller;

use app\common\basics\Api;
use app\common\server\JsonServer;
use app\api\validate\GoodsCommentValidate;
use think\exception\ValidateException;
use app\api\logic\GoodsCommentLogic;

class GoodsComment extends Api
{
    public $like_not_need_login = ['lists', 'category', 'getCommentPage'];

    /**
     * 商品评论分类
     */
    public function category()
    {
        $get = $this->request->get();
        $data = GoodsCommentLogic::category($get);
        return JsonServer::success('', $data);
    }

    /**
     * 商品评论列表
     */
    public function lists(){
        $get = $this->request->get();
        $get['page_no'] = $this->page_no;
        $get['page_size'] = $this->page_size;
        $data = GoodsCommentLogic::lists($get);
        return JsonServer::success('', $data);
    }

  /**
   * 添加商品评论
   */
    public function addGoodsComment()
    {
        $post = $this->request->post();
        $post['user_id'] = $this->user_id;
        try{
            validate(GoodsCommentValidate::class)->check($post);
        }catch(ValidateException $e) {
            return JsonServer::error($e->getError());
        }
        $result = GoodsCommentLogic::addGoodsComment($post);
        if($result === true) {
            return JsonServer::success('评论成功');
        }
        return JsonServer::error(GoodsCommentLogic::getError());
    }

    /**
     * 未评论订单
     */
    public function getUnCommentOrder(){
        $get['user_id'] = $this->user_id;
        $get['page_no'] = $this->page_no;
        $get['page_size'] = $this->page_size;
        $data = GoodsCommentLogic::getUnCommentOrder($get);
        return JsonServer::success('', $data);
    }

    /**
     * 已评论订单
     */
    public function getCommentOrder()
    {
        $get['user_id'] = $this->user_id;
        $get['page_no'] = $this->page_no;
        $get['page_size'] = $this->page_size;
        $data = GoodsCommentLogic::getCommentOrder($get);
        return JsonServer::success('', $data);
    }

    /**
     * 商品评价页面
     */
    public function getCommentPage()
    {
        $get = $this->request->get();
        $result = GoodsCommentLogic::getCommentPage($get);
        if($result !== false) {
            return JsonServer::success('', $result);
        }
        return JsonServer::error(GoodsCommentLogic::getError());
    }


    /**
     * @notes 校验商品
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/11/03 18:25
     */
    public function checkGoods()
    {
        $goodsId = $this->request->get('goods_id', 0);
        $result = GoodsCommentLogic::checkGoods($goodsId);
        if($result !== false) {
            return JsonServer::success('');
        }
        return JsonServer::error(GoodsCommentLogic::getError());
    }

}