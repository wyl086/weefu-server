<?php


namespace app\admin\controller\community;


use app\admin\logic\community\CommunityCategoryLogic;
use app\admin\logic\community\CommunityTopicLogic;
use app\admin\validate\community\CommunityTopicValidate;
use app\common\basics\AdminBase;
use app\common\server\JsonServer;

/**
 * 种草社区话题
 * Class CommunityTopic
 * @package app\admin\controller\content
 */
class CommunityTopic extends AdminBase
{

    /**
     * @notes 获取话题列表
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DbException
     * @author 段誉
     * @date 2022/4/28 10:41
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $lists = CommunityTopicLogic::lists($get);
            return JsonServer::success("获取成功", $lists);
        }
        return view('', ['cate' => CommunityCategoryLogic::getCategory()]);
    }


    /**
     * @notes 新增话题
     * @return \think\response\Json|\think\response\View
     * @author 段誉
     * @date 2022/4/28 10:39
     */
    public function add()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $post['del'] = 0;
            (new CommunityTopicValidate())->goCheck('add', $post);
            CommunityTopicLogic::add($post);
            return JsonServer::success('新增成功');
        }
        return view('', ['cate' => CommunityCategoryLogic::getCategory()]);
    }



    /**
     * @notes 编辑话题
     * @return \think\response\Json|\think\response\View
     * @author 段誉
     * @date 2022/4/28 10:40
     */
    public function edit()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $post['del'] = 0;
            (new CommunityTopicValidate())->goCheck('edit', $post);
            CommunityTopicLogic::edit($post);
            return JsonServer::success('编辑成功');
        }

        $id = $this->request->get('id');
        return view('', [
            'detail' => CommunityTopicLogic::detail($id),
            'cate' => CommunityCategoryLogic::getCategory()
        ]);
    }



    /**
     * @notes 删除话题
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/4/28 10:40
     */
    public function del()
    {
        if ($this->request->isAjax()) {
            (new CommunityTopicValidate())->goCheck('id');
            $id = $this->request->post('id');
            $result = CommunityTopicLogic::del($id);
            if (false === $result) {
                return JsonServer::error(CommunityTopicLogic::getError() ?: '删除失败');
            }
            return JsonServer::success('删除成功');
        }
    }


    /**
     * @notes 设置显示z状态
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/4/28 10:41
     */
    public function status()
    {
        if ($this->request->isAjax()) {
            (new CommunityTopicValidate())->goCheck('status');
            $post = $this->request->post();
            CommunityTopicLogic::setStatus($post);
            return JsonServer::success('操作成功');
        }
        return JsonServer::success('异常');
    }
}