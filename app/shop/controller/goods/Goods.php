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


use app\common\basics\ShopBase;
use app\common\enum\GoodsEnum;
use app\common\model\goods\GoodsBrand;
use app\common\model\goods\GoodsUnit;
use app\common\model\Freight;
use app\common\model\goods\Supplier;
use app\common\model\goods\Goods as GoodsModel;
use app\common\logic\CommonLogic;
use app\common\server\ArrayServer;
use app\common\server\JsonServer;
use app\shop\logic\goods\GoodsLogic;
use app\shop\validate\goods\GoodsMoreSpec;
use app\shop\validate\goods\GoodsMoreSpecLists;
use app\shop\validate\goods\GoodsOneSpec;
use app\shop\validate\goods\GoodsStatusValidate;
use app\shop\validate\goods\GoodsValidate;
use app\admin\logic\goods\CategoryLogic as MallCategoryLogic;
use app\shop\logic\goods\CategoryLogic as ShopCategoryLogic;



/**
 * 商品管理
 * Class Goods
 * @package app\shop\controller\goods
 */
class Goods extends ShopBase
{
    /**
     * Notes: 列表
     * @author 段誉(2021/4/15 10:49)
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $get['shop_id'] = $this->shop_id;
            return JsonServer::success('', GoodsLogic::lists($get));
        }
        $cate_list = MallCategoryLogic::categoryTreeeTree();
        $shop_cate_list = ShopCategoryLogic::listAll($this->shop_id);
        $statistics = GoodsLogic::statistics($this->shop_id);
        return view('', [
            'statistics'        => $statistics,
            'cate_list'         => $cate_list, //平台分类
            'shop_cate_list'    => $shop_cate_list, //商家分类
            'goods_type'        => GoodsEnum::getTypeDesc()
        ]);
    }


    /**
     * Notes: 添加
     * @author 段誉(2021/4/15 10:49)
     */
    public function add()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $post['shop_id'] = $this->shop_id;

            //主表验证
            (new GoodsValidate())->goCheck('add', ['shop_id' => $this->shop_id]);

            //单规格验证
            if ($post['spec_type'] == 1) {
                (new GoodsOneSpec())->goCheck();
            }

            //多规格验证
            $spec_lists = [];
            if ($post['spec_type'] == 2) {
                $spec_lists = $post;
                unset($spec_lists['goods_image']);
                unset($spec_lists['spec_id']);
                unset($spec_lists['spec_name']);
                unset($spec_lists['spec_values']);
                unset($spec_lists['spec_value_ids']);
                unset($spec_lists['delivery_type']);

                $spec_lists = ArrayServer::form_to_linear($spec_lists);

                //规格验证
                if (empty($spec_lists)) {
                    return JsonServer::error('至少添加一个规格');
                }
                // 规格项及规格值是否重复验证
                (new GoodsMoreSpec())->goCheck();

                //规格商品列表验证
                foreach ($spec_lists as $v) {
                    (new GoodsMoreSpecLists())->goCheck('', $v);
                }
                // 校验规格
                $total_stock = array_sum(array_column($spec_lists, 'stock'));
                if ($total_stock <= 0) {
                    return JsonServer::error('至少有一个规格的库存大于0');
                }
            }

           // 添加商品
            $result = GoodsLogic::add($this->shop_id, $post, $spec_lists);

            if (true !== $result) {
                return JsonServer::error(GoodsLogic::getError() ?: '操作失败');
            }
            return JsonServer::success('添加成功');
        }

        return view('', [
            'category_lists' => json_encode(MallCategoryLogic::getAllTree(), JSON_UNESCAPED_UNICODE),
            'shop_category_lists' => json_encode(ShopCategoryLogic::listAll($this->shop_id), JSON_UNESCAPED_UNICODE),
            'brand_lists' => json_encode(GoodsBrand::getNameColumn(), JSON_UNESCAPED_UNICODE),
            'supplier_lists' => json_encode(Supplier::getNameColumn($this->shop_id), JSON_UNESCAPED_UNICODE),
            'unit_lists' => json_encode(GoodsUnit::getNameColumn(), JSON_UNESCAPED_UNICODE),
            'freight_lists' => json_encode(Freight::getNameColumn($this->shop_id), JSON_UNESCAPED_UNICODE),
        ]);
    }


    /**
     * @notes 编辑
     * @return \think\response\Json|\think\response\View
     * @author 段誉
     * @date 2021/4/15 10:49
     */
    public function edit()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $post['del'] = 0;
            $post['id'] = $post['goods_id'];

            //主表验证
            (new GoodsValidate())->goCheck(null, ['shop_id' => $this->shop_id]);

            //单规格验证
            if ($post['spec_type'] == 1) {
                (new GoodsOneSpec())->goCheck();
            }

            //多规格验证
            $spec_lists = [];
            if ($post['spec_type'] == 2) {
                $spec_lists = $post;
                unset($spec_lists['goods_image']);
                unset($spec_lists['spec_name']);
                unset($spec_lists['spec_values']);
                unset($spec_lists['spec_id']);
                unset($spec_lists['spec_value_ids']);
                unset($spec_lists['delivery_type']);
                $spec_lists = ArrayServer::form_to_linear($spec_lists);

                //规格验证
                if (empty($spec_lists)) {
                    return JsonServer::error('至少添加一个规格');
                }
                // 规格项验证
                (new GoodsMoreSpec())->goCheck();
                //规格商品列表验证
                foreach ($spec_lists as $v) {
                    (new GoodsMoreSpecLists())->goCheck('', $v);
                }
                // 校验规格
                $total_stock = array_sum(array_column($spec_lists, 'stock'));
                if ($total_stock <= 0) {
                    return JsonServer::error('至少有一个规格的库存大于0');
                }
            }
            $result = GoodsLogic::edit($post, $spec_lists);
            if (true !== $result) {
                return JsonServer::error(GoodsLogic::getError() ?: '操作失败');
            }
            return JsonServer::success(GoodsLogic::getError() ? : '编辑成功');
        }

        $goods_id = $this->request->get('goods_id');

        return view('goods/goods/add', [
            'category_lists' => json_encode(MallCategoryLogic::getAllTree(), JSON_UNESCAPED_UNICODE),
            'shop_category_lists' => json_encode(ShopCategoryLogic::listAll($this->shop_id), JSON_UNESCAPED_UNICODE),
            'brand_lists' => json_encode(GoodsBrand::getNameColumn(), JSON_UNESCAPED_UNICODE),
            'supplier_lists' => json_encode(Supplier::getNameColumn($this->shop_id), JSON_UNESCAPED_UNICODE),
            'unit_lists' => json_encode(GoodsUnit::getNameColumn(), JSON_UNESCAPED_UNICODE),
            'freight_lists' => json_encode(Freight::getNameColumn($this->shop_id), JSON_UNESCAPED_UNICODE),
            'info' => json_encode(GoodsLogic::info($goods_id),JSON_UNESCAPED_UNICODE)
        ]);
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
            (new GoodsValidate())->goCheck('del', ['goods_id' => $id, 'shop_id' => $this->shop_id]);
            $result = GoodsLogic::del($this->shop_id, $id);
            if($result) {
                return JsonServer::success('删除成功');
            }
            return JsonServer::error(GoodsLogic::getError());
        }
    }

    /**
     * Notes:修改商品字段（上下架、新品推荐、好物优选、猜你喜欢）
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function changeFields(){
        $table = 'goods';
        $pk_name = 'id';
        $pk_value = $this->request->post('id');
        $field = $this->request->post('field');
        $field_value = $this->request->post('value');

        // 库存校验
        $stock = GoodsModel::where('id', $pk_value)->value('stock');
        if($field_value == 1 && $stock <= 0) {
            return JsonServer::error('库存为0的商品不允许上架');
        }
        $result = CommonLogic::changeTableValue($table,$pk_name,$pk_value,$field,$field_value);
        if($result){
            event('UpdateCollect', ['goods_id' => $pk_value]);
            return JsonServer::success('操作成功');
        }
        return JsonServer::error('操作失败');
    }

    /**
     * 放回仓库
     */
    public function backToWarehouse(){
        $id = $this->request->post('id', '', 'intval');
        if(empty($id)) {
            return JsonServer::error('id不存在');
        }
        $result = GoodsLogic::backToWarehouse($id);
        if($result) {
            return JsonServer::success('操作成功');
        }
        return JsonServer::error('操作失败');
    }

    /**
     * @notes 获取统计数据
     * @author Tab
     * @date 2021/7/13 18:03
     */
    public function totalCount()
    {
        if ($this->request->isAjax()) {
            return JsonServer::success('获取成功', GoodsLogic::statistics($this->shop_id));
        }
    }


    /**
     * @notes 批量更新上下架状态
     * @return \think\response\Json|void
     * @author 段誉
     * @date 2022/3/17 11:53
     */
    public function setStatus()
    {
        if ($this->request->isAjax()) {
            $params = (new GoodsStatusValidate())->goCheck(null, ['shop_id' => $this->shop_id]);
            $result = GoodsLogic::setStatus($params['ids'], $params['status']);
            if($result) {
                return JsonServer::success('操作成功');
            }
            return JsonServer::error(GoodsLogic::getError());
        }
    }

}
