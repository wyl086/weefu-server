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


use app\admin\validate\ShopAuthValidate;
use app\admin\logic\shop\AuthLogic;
use app\common\basics\AdminBase;
use app\common\server\JsonServer;

/**
 * 商家菜单
 * Class Auth
 * @package app\admin\controller\shop
 */
class Auth extends AdminBase
{
    /**
     * Notes: 列表
     * @author 段誉(2021/4/10 16:44)
     * @return string|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function lists()
    {
        if($this->request->isAjax()) {
            $data = AuthLogic::lists();
            return json(['code' => 0, 'msg' => '列表', 'data' => json_encode($data)]);
        }
        return view();
    }


    /**
     * Notes: 添加
     * @author 段誉(2021/4/12 16:43)
     * @return string|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function add()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $post['disable'] = isset($post['disable']) && $post['disable'] == 'on' ? 0 : 1;
            (new ShopAuthValidate())->goCheck();
            $result = AuthLogic::addMenu($post);
            if (false === $result) {
                return JsonServer::error(AuthLogic::getError() ?: '操作失败');
            }
            return JsonServer::success('操作成功');
        }
        return view('', ['menu_lists' => AuthLogic::chooseMenu()]);
    }

    /**
     * Notes: 编辑
     * @author 段誉(2021/4/12 16:43)
     * @return string|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit()
    {
        $id = $this->request->get('id');
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $post['disable'] = isset($post['disable']) && $post['disable'] == 'on' ? 0 : 1;
            (new ShopAuthValidate())->goCheck();
            if (false ===  AuthLogic::editMenu($post)) {
                return JsonServer::error(AuthLogic::getError() ?: '操作失败');
            }
            return JsonServer::success('操作成功');
        }
        return view('', [
            'detail' => AuthLogic::detail($id),
            'menu_lists' => AuthLogic::chooseMenu()
        ]);
    }


    /**
     * Notes: 删除
     * @author 段誉(2021/4/12 16:43)
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function del()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            if (empty($post['ids'])) {
                return JsonServer::error(AuthLogic::getError() ?: '操作失败');
            }
            AuthLogic::delMenu($post['ids']);
            return JsonServer::success('操作成功');
        }
    }


    /**
     * Notes: 设置
     * @author 段誉(2021/4/12 16:43)
     */
    public function status()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            AuthLogic::setStatus($post);
            return JsonServer::success('操作成功');
        }
    }
}