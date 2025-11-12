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

use app\common\basics\AdminBase;
use app\common\enum\GoodsEnum;
use app\common\model\goods\GoodsBrand;
use app\common\model\goods\GoodsUnit;
use app\common\model\Freight;
use app\common\model\goods\Supplier;
use app\common\model\goods\Goods as GoodsModel;
use app\common\server\JsonServer;
use app\admin\logic\goods\CategoryLogic as MallCategoryLogic;
use app\admin\logic\goods\GoodsLogic;
use app\admin\logic\goods\ColumnLogic;
use app\shop\logic\goods\CategoryLogic as ShopCategoryLogic;
use think\exception\ValidateException;
use app\admin\validate\goods\GoodsValidate;

/**
 * 商品管理
 * Class Goods
 */
class Goods extends AdminBase
{
    /**
     * Notes: 列表
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            return JsonServer::success('', GoodsLogic::lists($get));
        }

        $cate_list = MallCategoryLogic::categoryTreeeTree();
        $statistics = GoodsLogic::statistics();
        $column_list = ColumnLogic::getList();
        return view('', [
            'statistics' => $statistics,
            'cate_list' => $cate_list,
            'column_list' => $column_list,
            'goods_type'        => GoodsEnum::getTypeDesc()
        ]);
    }

    /**
     * 查看
     */
    public function view()
    {
        $goods_id = $this->request->get('goods_id');
        $shop_id = GoodsModel::where('id', $goods_id)->value('shop_id');
        return view('goods/goods/add', [
            'category_lists' => json_encode(MallCategoryLogic::getAllTree(), JSON_UNESCAPED_UNICODE),
            'shop_category_lists' => json_encode(ShopCategoryLogic::listAll($shop_id), JSON_UNESCAPED_UNICODE),
            'brand_lists' => json_encode(GoodsBrand::getNameColumn(), JSON_UNESCAPED_UNICODE),
            'supplier_lists' => json_encode(Supplier::getNameColumn(), JSON_UNESCAPED_UNICODE),
            'unit_lists' => json_encode(GoodsUnit::getNameColumn(), JSON_UNESCAPED_UNICODE),
            'freight_lists' => json_encode(Freight::getNameColumn($shop_id), JSON_UNESCAPED_UNICODE),
            'info' => json_encode(GoodsLogic::info($goods_id), JSON_UNESCAPED_UNICODE)
        ]);
    }

    /**
     * 违规重审
     */
    public function reAudit()
    {
        if ($this->request->isAjax()) {
            try {
                $params = $this->request->post();
                validate(GoodsValidate::class)->scene('re_audit')->check($params);
            } catch (ValidateException $e) {
                return JsonServer::error($e->getMessage());
            }
            $result = GoodsLogic::reAudit($params);
            if ($result) {
                return JsonServer::success('保存成功');
            }
            return JsonServer::error('保存失败');
        }

        $goods_id = $this->request->get('goods_id', '', 'intval');
        return view('re_audit', [
            'goods_id' => $goods_id
        ]);
    }

    /**
     * 商品设置
     */
    public function setInfo()
    {
        if ($this->request->isAjax()) {
            try {
                $params = $this->request->post();
                validate(GoodsValidate::class)->scene('set_info')->check($params);
            } catch (ValidateException $e) {
                return JsonServer::error($e->getMessage());
            }
            $result = GoodsLogic::setInfo($params);
            if ($result) {
                return JsonServer::success('设置成功');
            }
            return JsonServer::error('设置失败');
        }
        $goods_id = $this->request->get('goods_id', '', 'intval');
        $goods_detail = GoodsModel::find($goods_id);
        $goods_detail['column_ids'] = $goods_detail['column_ids'] ? explode(',', $goods_detail['column_ids']) : [];
        $goods_detail['column_ids'] = json_encode($goods_detail['column_ids']);
        $column_list = ColumnLogic::getList();
        return view('set_info', [
            'goods_id' => $goods_id,
            'column_list' => json_encode($column_list),
            'goods_detail' => $goods_detail
        ]);
    }

    /**
     * 审核
     */
    public function audit()
    {
        if ($this->request->isAjax()) {
            try {
                $params = $this->request->post();
                validate(GoodsValidate::class)->scene('audit')->check($params);
            } catch (ValidateException $e) {
                return JsonServer::error($e->getMessage());
            }
            $result = GoodsLogic::audit($params);
            if ($result) {
                return JsonServer::success('操作完成');
            }
            return JsonServer::error('操作失败');
        }
        $goods_id = $this->request->get('goods_id', '', 'intval');
        return view('audit', [
            'goods_id' => $goods_id
        ]);
    }

    public function totalCount()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            return JsonServer::success('获取成功', GoodsLogic::statistics($get));
        }
    }


    /**
     * @notes 批量下架
     * @return \think\response\Json|\think\response\View
     * @author ljj
     * @date 2022/9/20 6:21 下午
     */
    public function moreLower()
    {
        if ($this->request->isAjax()) {
            try {
                $params = $this->request->post();
                validate(GoodsValidate::class)->scene('moreLower')->check($params);
            } catch (ValidateException $e) {
                return JsonServer::error($e->getMessage());
            }
            $result = GoodsLogic::moreLower($params);
            if (false === $result) {
                return JsonServer::error(GoodsLogic::getError());
            }
            return JsonServer::success('操作成功');
        }

        $ids = $this->request->get('ids');
        return view('more_lower', [
            'ids' => $ids
        ]);
    }

    /**
     * @notes 批量审核
     * @return \think\response\Json|\think\response\View
     * @author ljj
     * @date 2022/9/20 6:38 下午
     */
    public function moreAudit()
    {
        if ($this->request->isAjax()) {
            try {
                $params = $this->request->post();
                validate(GoodsValidate::class)->scene('moreAudit')->check($params);
            } catch (ValidateException $e) {
                return JsonServer::error($e->getMessage());
            }
            $result = GoodsLogic::moreAudit($params);
            if (false === $result) {
                return JsonServer::error(GoodsLogic::getError());
            }
            return JsonServer::success('操作成功');
        }

        $ids = $this->request->get('ids');
        return view('more_audit', [
            'ids' => $ids
        ]);
    }
}