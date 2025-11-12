<?php


namespace app\admin\controller\community;


use app\admin\logic\community\CommunityArticleLogic;
use app\admin\validate\community\CommunityArticleValidate;
use app\common\basics\AdminBase;
use app\common\enum\CommunityArticleEnum;
use app\common\server\JsonServer;

/**
 * 种草社区文章
 * Class CommunityArticle
 * @package app\admin\controller\community
 */
class CommunityArticle extends AdminBase
{


    /**
     * @notes 文章列表
     * @return \think\response\Json|\think\response\View
     * @author 段誉
     * @date 2022/5/10 11:08
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $lists = CommunityArticleLogic::lists($get);
            return JsonServer::success("获取成功", $lists);
        }
        return view('', [
            'status' => CommunityArticleEnum::getStatusDesc()
        ]);
    }


    /**
     * @notes 审核文章
     * @return \think\response\Json|\think\response\View
     * @author 段誉
     * @date 2022/5/10 17:45
     */
    public function audit()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            (new CommunityArticleValidate())->goCheck('audit', $post);
            $result = CommunityArticleLogic::audit($post);
            if (false === $result) {
                return JsonServer::error(CommunityArticleLogic::getError() ?: '操作失败');
            }
            return JsonServer::success('编辑成功');
        }
        $id = $this->request->get('id');
        return view('', [
            'detail' => CommunityArticleLogic::detail($id)
        ]);
    }



    /**
     * @notes 文章详情
     * @return \think\response\Json|\think\response\View
     * @author 段誉
     * @date 2022/5/10 19:05
     */
    public function detail()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $result = CommunityArticleLogic::getRelationData($get);
            return JsonServer::success('', $result);
        }
        $id = $this->request->get('id');
        return view('', [
            'detail' => CommunityArticleLogic::detail($id)
        ]);
    }




    /**
     * @notes 删除文章
     * @return \think\response\Json|void
     * @author 段誉
     * @date 2022/5/10 16:46
     */
    public function del()
    {
        if ($this->request->isAjax()) {
            (new CommunityArticleValidate())->goCheck('id');
            $id = $this->request->post('id');
            $result = CommunityArticleLogic::del($id);
            if (false === $result) {
                return JsonServer::error(CommunityArticleLogic::getError() ?: '删除失败');
            }
            return JsonServer::success('删除成功');
        }
    }


}