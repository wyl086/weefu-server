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


use app\admin\logic\AdminLogic;
use app\admin\logic\LoginLogic;
use app\admin\validate\AdminPasswordValidate;
use app\admin\validate\AdminValidate;
use app\common\basics\AdminBase;
use app\common\model\Role;
use app\common\model\Admin as AdminModel;
use app\common\server\JsonServer;

class Admin extends AdminBase
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
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            return JsonServer::success('获取成功', AdminLogic::lists($get));
        }
        return view('', ['role_lists' => (new Role())->getRoleLists()]);
    }


    /**
     * Notes: 添加
     * @author 段誉(2021/4/10 16:44)
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
            (new AdminValidate())->goCheck('add');
            if (AdminLogic::addAdmin($post)) {
                return JsonServer::success('操作成功');
            }
            return JsonServer::error(AdminLogic::getError() ?: '操作失败');
        }
        return view('', ['role_lists' => (new Role())->getRoleLists()]);
    }

    /**
     * Notes: 编辑
     * @author 段誉(2021/4/10 16:45)
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit()
    {
        $id = $this->request->get('admin_id');
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $post['disable'] = isset($post['disable']) && $post['disable'] == 'on' ? 0 : 1;
            (new AdminValidate())->goCheck('edit');
            if (AdminLogic::editAdmin($post)) {
                return JsonServer::success('操作成功');
            }
            return JsonServer::error(AdminLogic::getError() ?: '操作失败');
        }
        return view('',[
            'detail' => AdminModel::find($id),
            'role_lists' => (new Role())->getRoleLists()
        ]);
    }


    /**
     * Notes: 删除
     * @author 段誉(2021/4/30 11:31)
     * @return \think\response\Json
     */
    public function del()
    {
        if ($this->request->isAjax()) {
            $id = $this->request->post('admin_id');
            if (AdminLogic::delAdmin($id)) {
                return JsonServer::success('操作成功');
            }
        }
        return JsonServer::error('操作失败');
    }


    /**
     * Notes: 修改密码
     * @author 段誉(2021/4/30 12:03)
     * @return \think\response\Json|\think\response\View
     */
    public function password()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $post['admin_id'] = $this->adminId;
            (new AdminPasswordValidate())->goCheck('', $post);

            $res = AdminLogic::updatePassword($post['password'], $this->adminId);
            if ($res) {
                return JsonServer::success('操作成功');
            }
            return JsonServer::error(AdminLogic::getError() ?: '操作失败');
        }
        return view('', [
            'account' => $this->adminUser['account']
        ]);
    }

}