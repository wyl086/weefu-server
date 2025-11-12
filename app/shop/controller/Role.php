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

use app\shop\logic\RoleLogic;
use app\shop\validate\RoleValidate;
use app\common\basics\ShopBase;
use app\common\server\JsonServer;

class Role extends ShopBase
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
            return JsonServer::success('', RoleLogic::lists($this->shop_id, $get));
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
            $result = RoleLogic::addRole($this->shop_id, $post);
            if ($result !== true) {
                return JsonServer::error(RoleLogic::getError() ?: '操作失败');
            }
            return JsonServer::success('操作成功');
        }
        return view('', [
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
            $result = RoleLogic::editRole($this->shop_id, $post);
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
            RoleLogic::delRole($this->shop_id, $id);
            return JsonServer::success('删除成功');
        }
    }
}