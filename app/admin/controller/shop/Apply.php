<?php


namespace app\admin\controller\shop;


use app\admin\logic\shop\ApplyLogic;
use app\admin\validate\shop\ShopApplyValidate;
use app\common\basics\AdminBase;
use app\common\server\JsonServer;

/**
 * 商家入驻
 * Class Apply
 * @package app\admin\controller\shop
 */
class Apply extends AdminBase
{
    /**
     * NOTE: 申请列表
     * @author: 张无忌
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $lists = ApplyLogic::lists($get);
            return JsonServer::success('获取成功', $lists);
        }

        return view('', [
            'totalCount' => ApplyLogic::totalCount()
        ]);
    }

    /**
     * NOTE: 统计
     * @author: 张无忌
     */
    public function totalCount()
    {
        if ($this->request->isAjax()) {
            return JsonServer::success('获取成功', ApplyLogic::totalCount());
        }

        return JsonServer::error('请求异常');
    }

    /**
     * NOTE: 详细
     * @author: 张无忌
     */
    public function detail()
    {
        (new ShopApplyValidate())->goCheck('id');
        $id = $this->request->get('id');
        return view('', [
            'detail' => ApplyLogic::detail($id)
        ]);
    }

    /**
     * NOTE: 审核
     * @author: 张无忌
     */
    public function audit()
    {
        if ($this->request->isAjax()) {
            (new ShopApplyValidate())->goCheck('audit');
            $post = $this->request->post();
            $res = ApplyLogic::audit($post);
            if ($res) {
                return JsonServer::success('操作成功');
            }

            $error = ApplyLogic::getError() ?: '操作失败';
            return JsonServer::error($error);
        }

        return view();
    }

    /**
     * NOTE: 删除
     * @author: 张无忌
     */
    public function del()
    {
        if ($this->request->isAjax()) {
            (new ShopApplyValidate())->goCheck('id');
            $id = $this->request->post('id');
            $res = ApplyLogic::del($id);
            if ($res) {
                return JsonServer::success('删除成功');
            }

            $error = ApplyLogic::getError() ?: '删除失败';
            return JsonServer::error($error);
        }

        return JsonServer::error('请求异常');
    }
}