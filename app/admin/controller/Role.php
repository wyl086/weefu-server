<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\admin\controller;


use app\admin\logic\RoleLogic;
use app\admin\validate\RoleValidate;
use app\common\basics\AdminBase;
use app\common\server\JsonServer;

class Role extends AdminBase
{
    /**
     * Notes: 列表
     * @author 段誉(2021/4/13 10:34)
     * @return string|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            return JsonServer::success('', RoleLogic::lists($get));
        }
        return view();
    }


    /**
     * Notes: 添加
     * @author 段誉(2021/4/13 10:34)
     * @return string|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function add()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            (new RoleValidate())->goCheck('add');
            $result = RoleLogic::addRole($post);
            if ($result !== true) {
                return JsonServer::error(RoleLogic::getError() ?: '操作失败');
            }
            return JsonServer::success('操作成功');
        }
        return view('',[
            'auth_tree' => json_encode(RoleLogic::authTree(), true)
        ]);
    }


    /**
     * Notes: 编辑
     * @param string $role_id
     * @author 段誉(2021/4/13 10:34)
     * @return string|\think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit($role_id = '')
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            (new RoleValidate())->goCheck('edit');
            $result = RoleLogic::editRole($post);
            if ($result !== true) {
                return JsonServer::error(RoleLogic::getError() ?: '操作失败');
            }
            return JsonServer::success('操作成功');
        }
        $auth_tree = RoleLogic::authTree($role_id);

        return view('', [
            'info' => RoleLogic::roleInfo($role_id),
            'auth_tree' => json_encode($auth_tree),
        ]);
    }

    /**
     * Notes: 删除
     * @param $role_id
     * @author 段誉(2021/4/13 10:35)
     * @return \think\response\Json
     * @throws \think\Exception
     */
    public function del($id)
    {
        if ($this->request->isAjax()) {
            (new RoleValidate())->goCheck('del');
            RoleLogic::delRole($id);
            return JsonServer::success('删除成功');
        }
    }
}