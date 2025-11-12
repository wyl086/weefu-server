<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\admin\controller\shop;


use app\admin\logic\shop\CategoryLogic;
use app\common\basics\AdminBase;
use app\common\server\JsonServer;

/**
 * 主营类目
 * Class Category
 * @package app\admin\controller\shop
 */
class Category extends AdminBase
{
    /**
     * NOTE: 主营类目列表
     * @author: 张无忌
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $lists = CategoryLogic::lists($get);
            return JsonServer::success('获取成功', $lists);
        }
        
        return view();
    }

    /**
     * NOTE: 新增主营类目
     * @author: 张无忌
     */
    public function add()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            if(!isset($post['image']) || empty($post['image'])) {
                return JsonServer::error('类目图标不能为空');
            }
            $res = CategoryLogic::add($post);
            if ($res === false) {
                $error = CategoryLogic::getError() ?: '新增失败';
                return JsonServer::error($error);
            }
            return JsonServer::success('新增成功');
        }

        return view();
    }

    /**
     * NOTE: 编辑主营类目
     * @author: 张无忌
     */
    public function edit()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            if(!isset($post['image']) || empty($post['image'])) {
                return JsonServer::error('类目图标不能为空');
            }
            $res = CategoryLogic::edit($post);
            if ($res === false) {
                $error = CategoryLogic::getError() ?: '编辑失败';
                return JsonServer::error($error);
            }
            return JsonServer::success('编辑成功');
        }

        $id = $this->request->get('id');
        return view('', [
            'detail' => CategoryLogic::detail($id)
        ]);
    }

    /**
     * NOTE: 删除主营类目
     * @author: 张无忌
     */
    public function del()
    {
        if ($this->request->isAjax()) {
            $id = $this->request->post('id');
            $res = CategoryLogic::del($id);
            if ($res === false) {
                $error = CategoryLogic::getError() ?: '删除失败';
                return JsonServer::error($error);
            }
            return JsonServer::success('删除成功');
        }

        return JsonServer::error('请求异常');
    }
}