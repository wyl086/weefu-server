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


namespace app\shop\controller\goods;


use app\shop\logic\goods\SupplierLogic;
use app\shop\validate\goods\SupplierValidate;
use app\common\basics\ShopBase;
use app\common\model\goods\Supplier as SupplierModel;
use app\common\server\JsonServer;


/**
 * 供应商
 * Class GoodsBrand
 * @package app\admin\controller
 */
class Supplier extends ShopBase
{
    /**
     * Notes: 列表
     * @author 段誉(2021/4/15 10:49)
     * @return string|\think\response\Json
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            return JsonServer::success('获取成功', SupplierLogic::lists($this->shop_id, $get));
        }
        return view();
    }


    /**
     * Notes: 添加
     * @author 段誉(2021/4/15 10:49)
     * @return string|\think\response\Json
     */
    public function add()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            (new SupplierValidate())->goCheck('add');
            if (SupplierLogic::add($this->shop_id, $post)) {
                return JsonServer::success('操作成功');
            }
            return JsonServer::error(SupplierLogic::getError() ?: '操作失败');
        }
        return view('', ['capital' => getCapital()]);
    }

    /**
     * Notes: 编辑
     * @author 段誉(2021/4/15 10:49)
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
            (new SupplierValidate())->goCheck('edit');
            if (SupplierLogic::edit($this->shop_id, $post)) {
                return JsonServer::success('操作成功');
            }
            return JsonServer::error(SupplierLogic::getError() ?: '操作失败');
        }
        return view('', ['detail' => SupplierModel::find($id)]);
    }

    /**
     * Notes: 删除
     * @author 段誉(2021/4/15 10:49)
     * @return \think\response\Json
     */
    public function del()
    {
        if ($this->request->isAjax()) {
            $id = $this->request->post('id');
            (new SupplierValidate())->goCheck('del');
            if (SupplierLogic::del($this->shop_id, $id)) {
                return JsonServer::success('操作成功');
            }
            return JsonServer::error(SupplierLogic::getError() ?: '操作失败');
        }
    }
}