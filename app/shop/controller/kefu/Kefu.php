<?php

namespace app\shop\controller\kefu;


use app\common\basics\ShopBase;
use app\common\model\shop\ShopRole;
use app\shop\logic\kefu\KefuLogic;
use app\shop\validate\kefu\KefuValidate;
use app\common\server\JsonServer;
use app\shop\validate\kefu\LoginValidate;

/**
 * 客服管理控制器
 * Class Kefu
 * @package app\admin\controller\kefu
 */
class Kefu extends ShopBase
{

    /**
     * @notes 客服列表
     * @return \think\response\Json|\think\response\View
     * @author 段誉
     * @date 2021/11/26 18:40
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $lists = KefuLogic::getLists($get, $this->shop_id);
            return JsonServer::success('获取成功', $lists);
        }
        return view();
    }

    /**
     * @notes 添加客服
     * @return \think\response\Json|\think\response\View
     * @author 段誉
     * @date 2021/11/26 18:04
     */
    public function add()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $post['disable'] = isset($post['disable']) && $post['disable'] == 'on' ? 0 : 1;
            $post['shop_id'] = $this->shop_id;
            (new KefuValidate())->goCheck('add', $post);
            $res = KefuLogic::add($post, $this->shop_id);
            if (false === $res) {
                $error = KefuLogic::getError() ?: '操作失败';
                return JsonServer::error($error);
            }
            return JsonServer::success('操作成功');
        }
        return view();
    }


    /**
     * @notes 编辑客服
     * @return \think\response\Json|\think\response\View
     * @author 段誉
     * @date 2021/11/27 10:45
     */
    public function edit()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $post['disable'] = isset($post['disable']) && $post['disable'] == 'on' ? 0 : 1;
            (new KefuValidate())->goCheck('edit', $post);
            $res = KefuLogic::edit($post, $this->shop_id);
            if (false === $res) {
                $error = KefuLogic::getError() ?: '操作失败';
                return JsonServer::error($error);
            }
            return JsonServer::success('操作成功');
        }

        $id = $this->request->get('id');
        return view('', [
            'detail'   => KefuLogic::detail($id, $this->shop_id),
        ]);
    }



    /**
     * @notes 删除客服
     * @return \think\response\Json|void
     * @author 段誉
     * @date 2021/11/26 18:53
     */
    public function del()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            (new KefuValidate())->goCheck('del');
            KefuLogic::del($post, $this->shop_id);
            return JsonServer::success('操作成功');
        }
    }


    /**
     * @notes 管理员列表
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2021/11/26 18:01
     */
    public function adminLists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            return JsonServer::success('', KefuLogic::getAdminLists($get, $this->shop_id));
        }
        return view('', ['role_lists' => (new ShopRole())->getRoleLists(['shop_id' => $this->shop_id])]);
    }


    /**
     * @notes 设置状态
     * @return \think\response\Json|void
     * @author 段誉
     * @date 2021/11/26 18:40
     */
    public function status()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            KefuLogic::setStatus($post, $this->shop_id);
            return JsonServer::success('操作成功');
        }
    }


    /**
     * @notes 登录工作台
     * @return \think\response\Json|void
     * @author 段誉
     * @date 2021/12/20 10:46
     */
    public function login()
    {
        if ($this->request->isAjax()) {
            $id = $this->request->post('id/d');
            (new LoginValidate())->goCheck('', ['shop_id' =>$this->shop_id]);
            $res = KefuLogic::login($id, $this->shop_id);
            if (false === $res) {
                return JsonServer::error(KefuLogic::getError() ?: '系统错误');
            }
            return JsonServer::success('', ['url' => $res]);
        }
    }

}