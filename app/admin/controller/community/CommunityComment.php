<?php


namespace app\admin\controller\community;


use app\admin\logic\community\CommunityCommentLogic;
use app\admin\validate\community\CommunityCommentValidate;
use app\common\basics\AdminBase;
use app\common\enum\CommunityCommentEnum;
use app\common\server\JsonServer;


/**
 * 种草社区评论
 * Class CommunityComment
 * @package app\admin\controller\community
 */
class CommunityComment extends AdminBase
{

    /**
     * @notes 评论列表
     * @return \think\response\Json|\think\response\View
     * @author 段誉
     * @date 2022/5/10 12:05
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $lists = CommunityCommentLogic::lists($get);
            return JsonServer::success("获取成功", $lists);
        }
        return view('', [
            'status' => CommunityCommentEnum::getStatusDesc()
        ]);
    }


    /**
     * @notes 审核评论
     * @return \think\response\Json|\think\response\View
     * @author 段誉
     * @date 2022/5/10 15:12
     */
    public function audit()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            (new CommunityCommentValidate())->goCheck('audit', $post);
            CommunityCommentLogic::audit($post);
            return JsonServer::success('审核成功');
        }
        $id = $this->request->get('id');
        return view('', [
            'detail' => CommunityCommentLogic::detail($id)
        ]);
    }


    /**
     * @notes 删除评论
     * @return \think\response\Json|void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/5/10 15:42
     */
    public function del()
    {
        if ($this->request->isAjax()) {
            (new CommunityCommentValidate())->goCheck('id');
            $id = $this->request->post('id/d');
            CommunityCommentLogic::del($id);
            return JsonServer::success('删除成功');
        }
    }


}