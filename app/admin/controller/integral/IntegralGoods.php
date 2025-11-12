<?php

namespace app\admin\controller\integral;

use app\admin\logic\integral\IntegralGoodsLogic;
use app\admin\validate\integral\IntegralGoodsValidate;
use app\common\basics\AdminBase;
use app\common\server\JsonServer;

/**
 * 积分商城-积分商品
 * Class IntegralGoods
 * @package app\admin\controller\integral
 */
class IntegralGoods extends AdminBase
{

    /**
     * @notes 商品列表
     * @return \think\response\Json|\think\response\View
     * @author 段誉
     * @date 2022/2/25 18:28
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $lists = IntegralGoodsLogic::getLists($get);
            return JsonServer::success('获取成功', $lists);
        }
        return view();
    }


    /**
     * @notes 添加商品
     * @return \think\response\Json|\think\response\View
     * @author 段誉
     * @date 2022/2/25 18:27
     */
    public function add()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $post['status'] = isset($post['status']) && $post['status'] == 'on' ? 1 : 0;
            (new IntegralGoodsValidate())->goCheck('add', $post);
            $res = IntegralGoodsLogic::add($post);
            if (false === $res) {
                $error = IntegralGoodsLogic::getError() ?: '操作失败';
                return JsonServer::error($error);
            }
            return JsonServer::success('操作成功');
        }
        return view();
    }


    /**
     * @notes 编辑积分商品
     * @return \think\response\Json|\think\response\View
     * @author 段誉
     * @date 2022/3/1 15:40
     */
    public function edit()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $post['status'] = isset($post['status']) && $post['status'] == 'on' ? 1 : 0;
            (new IntegralGoodsValidate())->goCheck('edit', $post);
            $res = IntegralGoodsLogic::edit($post);
            if (false === $res) {
                $error = IntegralGoodsLogic::getError() ?: '操作失败';
                return JsonServer::error($error);
            }
            return JsonServer::success('操作成功');
        }
        $id = $this->request->get('id');
        return view('', [
            'detail'   => IntegralGoodsLogic::detail($id),
        ]);
    }


    /**
     * @notes 删除商品
     * @return \think\response\Json|void
     * @author 段誉
     * @date 2022/2/25 18:26
     */
    public function del()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            (new IntegralGoodsValidate())->goCheck('del');
            IntegralGoodsLogic::del($post);
            return JsonServer::success('操作成功');
        }
        return JsonServer::error('操作失败');
    }


    /**
     * @notes 切换状态
     * @return \think\response\Json|void
     * @author 段誉
     * @date 2022/2/25 18:26
     */
    public function switchStatus()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            IntegralGoodsLogic::switchStatus($post);
            return JsonServer::success('操作成功');
        }
        return JsonServer::error('操作失败');
    }

}