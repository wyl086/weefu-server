<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | gitee下载：https://gitee.com/multshop_gitee
// | github下载：https://github.com/multshop-github
// | 访问官网：https://www.multshop.cn
// | 访问社区：https://home.multshop.cn
// | 访问手册：http://doc.multshop.cn
// | 微信公众号：multshop技术社区
// | multshop系列产品在gitee、github等公开渠道开源版本可免费商用，未经许可不能去除前后端官方版权标识
// |  multshop系列产品收费版本务必购买商业授权，购买去版权授权后，方可去除前后端官方版权标识
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\shop\controller;


use app\shop\logic\AdminLogic;
use app\shop\validate\AdminPasswordValidate;
use app\shop\validate\AdminValidate;
use app\common\basics\ShopBase;
use app\common\model\shop\ShopRole;
use app\common\model\shop\ShopAdmin;
use app\common\server\JsonServer;

class Admin extends ShopBase
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
            return JsonServer::success('获取成功', AdminLogic::lists($get, $this->shop_id));
        }
        return view('', ['role_lists' => (new ShopRole())->getRoleLists()]);
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
            if (AdminLogic::addAdmin($this->shop_id, $post)) {
                return JsonServer::success('操作成功');
            }
            return JsonServer::error(AdminLogic::getError() ?: '操作失败');
        }
        return view('', [
            'role_lists' => (new ShopRole())->getRoleLists(['shop_id' => $this->shop_id])
        ]);
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
            if (AdminLogic::editAdmin($this->shop_id, $post)) {
                return JsonServer::success('操作成功');
            }
            return JsonServer::error(AdminLogic::getError() ?: '操作失败');
        }
        return view('', [
            'detail' => ShopAdmin::find($id),
            'role_lists' => (new ShopRole())->getRoleLists(['shop_id' => $this->shop_id])
        ]);
    }


    /**
     * Notes: 删除
     * @author 段誉(2021/4/14 15:25)
     * @return \think\response\Json
     */
    public function del()
    {
        if ($this->request->isAjax()) {
            $id = $this->request->post('admin_id');
            if (AdminLogic::delAdmin($this->shop_id, $id)) {
                return JsonServer::success('操作成功');
            }
            return JsonServer::error(AdminLogic::getError() ?: '操作失败');
        }
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
            $post['admin_id'] = $this->admin_id;
            (new AdminPasswordValidate())->goCheck('', $post);
            $res = AdminLogic::updatePassword($post['password'], $this->admin_id, $this->shop_id);
            if ($res) {
                return JsonServer::success('操作成功');
            }
            return JsonServer::error(AdminLogic::getError() ?: '系统错误');
        }
        return view('', [
            'account' => $this->shop['account']
        ]);
    }

}