<?php


namespace app\admin\controller\community;


use app\admin\logic\community\CommunityCategoryLogic;
use app\admin\validate\community\CommunityCategoryValidate;
use app\common\basics\AdminBase;
use app\common\server\JsonServer;

/**
 * 种草社区分类
 * Class CommunityCategory
 * @package app\admin\controller\content
 */
class CommunityCategory extends AdminBase
{


    /**
     * @notes 获取分类列表
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DbException
     * @author 段誉
     * @date 2022/4/28 10:41
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $lists = CommunityCategoryLogic::lists($get);
            return JsonServer::success("获取成功", $lists);
        }
        return view();
    }


    /**
     * @notes 新增分类
     * @return \think\response\Json|\think\response\View
     * @author 段誉
     * @date 2022/4/28 10:39
     */
    public function add()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $post['del'] = 0;
            (new CommunityCategoryValidate())->goCheck('add', $post);
            CommunityCategoryLogic::add($post);
            return JsonServer::success('新增成功');
        }
        return view();
    }



    /**
     * @notes 编辑分类
     * @return \think\response\Json|\think\response\View
     * @author 段誉
     * @date 2022/4/28 10:40
     */
    public function edit()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $post['del'] = 0;
            (new CommunityCategoryValidate())->goCheck('edit', $post);
            CommunityCategoryLogic::edit($post);
            return JsonServer::success('编辑成功');
        }

        $id = $this->request->get('id');
        return view('', [
            'detail' => CommunityCategoryLogic::detail($id)
        ]);
    }



    /**
     * @notes 删除分类
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/4/28 10:40
     */
    public function del()
    {
        if ($this->request->isAjax()) {
            (new CommunityCategoryValidate())->goCheck('id');
            $id = $this->request->post('id');
            $result = CommunityCategoryLogic::del($id);
            if (false === $result) {
                return JsonServer::error(CommunityCategoryLogic::getError() ?: '删除失败');
            }
            return JsonServer::success('删除成功');
        }
        return JsonServer::error('异常');
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
            (new CommunityCategoryValidate())->goCheck('status');
            $post = $this->request->post();
            CommunityCategoryLogic::setShowStatus($post);
            return JsonServer::success('操作成功');
        }
        return JsonServer::success('异常');
    }
}