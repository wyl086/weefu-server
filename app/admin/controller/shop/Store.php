<?php


namespace app\admin\controller\shop;


use app\admin\logic\shop\CategoryLogic;
use app\admin\logic\shop\StoreLogic;
use app\admin\validate\shop\StoreLValidate;
use app\admin\validate\shop\StoreStatusValidate;
use app\common\basics\AdminBase;
use app\common\server\ConfigServer;
use app\common\server\JsonServer;
use think\facade\Log;

/**
 * 商家管理
 * Class Store
 * @package app\admin\controller\shop
 */
class Store extends AdminBase
{
    /**
     * NOTE: 商家列表
     * @author: 张无忌
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $lists = StoreLogic::lists($get);
            return JsonServer::success('获取成功', $lists);
        }

        return view('', [
            'category' => CategoryLogic::getCategory()
        ]);
    }

    /**
     * NOTE: 新增商家
     * @author: 张无忌
     */
    public function add()
    {
        if ($this->request->isAjax()) {
            (new StoreLValidate())->goCheck('add');
            $post = $this->request->post();
            $lists = StoreLogic::add($post);
            if ($lists === false) {
                $error = StoreLogic::getError() ?: '新增失败';
                return JsonServer::error($error);
            }
            return JsonServer::success('新增成功');
        }

        return view('', [
            'category' => CategoryLogic::getCategory(),
            'tx_map_key' => ConfigServer::get('map', 'tx_map_key')
        ]);
    }

    /**
     * NOTE: 编辑商家
     * @author: 张无忌
     */
    public function edit()
    {
        if ($this->request->isAjax()) {
            (new StoreLValidate())->goCheck('edit');
            $post = $this->request->post();
            if (!empty($post['password'])) {
                (new StoreLValidate())->goCheck('pwd');
            }

            $res = StoreLogic::edit($post);
            if ($res === false) {
                $error = StoreLogic::getError() ?: '编辑失败';
                return JsonServer::error($error);
            }
            return JsonServer::success('编辑成功');
        }

        $id = $this->request->get('id');
        return view('', [
            'detail'   => StoreLogic::detail($id),
            'category' => CategoryLogic::getCategory(),
            'tx_map_key' => ConfigServer::get('map', 'tx_map_key')
        ]);
    }

    /**
     * NOTE: 设置商家
     * @author: 张无忌
     */
    public function set()
    {
        if ($this->request->isAjax()) {
            (new StoreLValidate())->goCheck('set');
            $post = $this->request->post();
            $res = StoreLogic::set($post);

            if ($res === false) {
                $error = StoreLogic::getError() ?: '设置失败';
                return JsonServer::error($error);
            }
            return JsonServer::success('设置成功');
        }

        $id = $this->request->get('id');
        return view('', [
            'detail' => StoreLogic::detail($id)
        ]);
    }

    /**
     * NOTE: 编辑账号
     * @author: 张无忌
     */
    public function account()
    {
        if ($this->request->isAjax()) {
            (new StoreLValidate())->goCheck('account');
            $post = $this->request->post();
            if (!empty($post['password'])) {
                (new StoreLValidate())->goCheck('pwd');
            }

            $res = StoreLogic::account($post);
            if ($res === false) {
                $error = StoreLogic::getError() ?: '更新失败';
                return JsonServer::error($error);
            }
            return JsonServer::success('更新成功');
        }

        $id = $this->request->get('id');
        return view('', [
            'detail' => StoreLogic::getAccountInfo($id)
        ]);
    }

    /**
     * @notes 批量操作
     * @return \think\response\Json|void
     * @author 段誉
     * @date 2022/3/17 10:42
     */
    public function batchOperation()
    {
        if ($this->request->isAjax()) {
            (new StoreStatusValidate())->goCheck();
            $post = $this->request->post();
            $res = StoreLogic::batchOperation($post['ids'], $post['field'], $post['value']);
            if (false === $res) {
                $error = StoreLogic::getError() ?: '操作失败';
                return JsonServer::error($error);
            }
            return JsonServer::success('操作成功');
        }
    }
}