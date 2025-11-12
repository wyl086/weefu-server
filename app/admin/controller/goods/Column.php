<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\admin\controller\goods;


use app\admin\logic\goods\ColumnLogic;
use app\admin\validate\goods\ColumnValidate;
use app\common\basics\AdminBase;
use app\common\model\goods\GoodsColumn as GoodsColumnModel;
use app\common\server\JsonServer;


/**
 * 商品栏目
 * Class GoodsColumn
 * @package app\admin\controller
 */
class Column extends AdminBase
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
            return JsonServer::success('获取成功', ColumnLogic::lists($get));
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
            (new ColumnValidate())->goCheck('add');
            if (ColumnLogic::add($post)) {
                return JsonServer::success('操作成功');
            }
            return JsonServer::error(ColumnLogic::getError() ?: '操作失败');
        }
        return view();
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
            (new ColumnValidate())->goCheck('edit');
            if (ColumnLogic::edit($post)) {
                return JsonServer::success('操作成功');
            }
            return JsonServer::error(ColumnLogic::getError() ?: '操作失败');
        }
        return view('', ['detail' => GoodsColumnModel::find($id)]);
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
            (new ColumnValidate())->goCheck('del');
            if (ColumnLogic::del($id)) {
                return JsonServer::success('操作成功');
            }
            return JsonServer::error(ColumnLogic::getError() ?: '操作失败');
        }
    }


    /**
     * Notes: 切换状态
     * @author 段誉(2021/4/15 15:17)
     * @return \think\response\Json
     */
    public function switchStatus()
    {
        $post = $this->request->post();
        GoodsColumnModel::update(['status' => $post['status']],['id' => $post['id']]);
        return JsonServer::success('操作成功');
    }
}