<?php
namespace app\api\controller;

use app\api\logic\CommunityCommentLogic;
use app\api\validate\CommunityCommentValidate;
use app\common\basics\Api;
use app\common\server\JsonServer;


/**
 * 种草社区评论
 * Class CommunityComment
 * @package app\api\controller
 */
class CommunityComment extends Api
{

    public $like_not_need_login = ['lists'];

    /**
     * @notes 评论列表
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/5/7 16:02
     */
    public function lists()
    {
        $get = (new CommunityCommentValidate())->goCheck('lists');
        $result = CommunityCommentLogic::getCommentLists($this->user_id, $get, $this->page_no, $this->page_size);
        return JsonServer::success('', $result);
    }



    /**
     * @notes 添加评论
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/5/7 11:53
     */
    public function add()
    {
        $post = $this->request->post();
        (new CommunityCommentValidate())->goCheck('add');
        $result = CommunityCommentLogic::addComment($this->user_id, $post);
        if(false === $result) {
            return JsonServer::error(CommunityCommentLogic::getError() ?: '评论失败');
        }
        return JsonServer::success($result['msg'], $result['data']);
    }


    /**
     * @notes 一级评论子级评论
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/5/9 14:34
     */
    public function commentChild()
    {
        $get = $this->request->get();
        $result = CommunityCommentLogic::getChildComment($this->user_id, $get, $this->page_no, $this->page_size);
        return JsonServer::success('', $result);
    }


}