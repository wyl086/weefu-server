<?php


namespace app\shop\controller;


use app\common\basics\ShopBase;
use app\common\server\JsonServer;
use app\shop\logic\BankLogic;
use app\shop\validate\BankValidate;
use think\facade\View;

class Bank extends ShopBase
{
    /**
     * @Notes: 银行卡列表
     * @Author: 张无忌
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $lists = BankLogic::lists($get, $this->shop_id);
            return JsonServer::success('获取成功', $lists);
        }

        return view();
    }

    /**
     * @Notes: 账号详细
     * @Author: 张无忌
     */
    public function detail()
    {
        $id = $this->request->get('id');
        View::assign('detail', BankLogic::detail($id));
        return view();
    }

    /**
     * @Notes: 新增银行卡
     * @Author: 张无忌
     */
    public function add()
    {
        if ($this->request->isAjax()) {
            (new BankValidate())->goCheck('add');
            $post = $this->request->post();
            $res = BankLogic::add($post, $this->shop_id);
            if ($res === false) {
                $error = BankLogic::getError() ?: '新增失败';
                return JsonServer::error($error);
            }
            return JsonServer::success('新增成功');
        }

        return view();
    }

    /**
     * @Notes: 编辑银行卡
     * @Author: 张无忌
     */
    public function edit()
    {
        if ($this->request->isAjax()) {
            (new BankValidate())->goCheck('edit');
            $post = $this->request->post();
            $res = BankLogic::edit($post);
            if ($res === false) {
                $error = BankLogic::getError() ?: '编辑失败';
                return JsonServer::error($error);
            }
            return JsonServer::success('编辑成功');
        }

        $id = $this->request->get('id');
        return view('', [
            'detail' => BankLogic::detail($id)
        ]);
    }

    /**
     * @Notes: 删除银行卡
     * @Author: 张无忌
     */
    public function del()
    {
        if ($this->request->isAjax()) {
            (new BankValidate())->goCheck('id');
            $id = $this->request->post('id');
            $res = BankLogic::del($id);
            if ($res === false) {
                $error = BankLogic::getError() ?: '删除失败';
                return JsonServer::error($error);
            }
            return JsonServer::success('删除成功');
        }

        return JsonServer::error('异常');
    }
}