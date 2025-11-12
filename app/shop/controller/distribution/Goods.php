<?php
namespace app\shop\controller\distribution;

use app\common\basics\ShopBase;
use app\admin\logic\goods\CategoryLogic as MallCategoryLogic;
use app\common\server\JsonServer;
use app\shop\logic\distribution\GoodsLogic;
use app\shop\logic\goods\CategoryLogic as ShopCategoryLogic;

class Goods extends ShopBase
{
    /**
     * @notes 分销商品列表页
     * @return \think\response\View
     * @author Tab
     * @date 2021/9/1 17:11
     */
    public function index()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();
            $params['shop_id'] = $this->shop_id;
            $lists = GoodsLogic::lists($params);
            return JsonServer::success('', $lists);
        }
        // 显示分销商品列表页
        $cate_list = MallCategoryLogic::categoryTreeeTree();
        $shop_cate_list = ShopCategoryLogic::listAll($this->shop_id);
        return view('', [
            'cate_list' => $cate_list,
            'shop_cate_list' => $shop_cate_list
        ]);
    }

    /**
     * @notes 设置佣金
     * @return \think\response\View
     * @author Tab
     * @date 2021/9/1 19:59
     */
    public function set()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();
            $params['shop_id'] = $this->shop_id;
            $result = GoodsLogic::set($params);
            if ($result) {
                return JsonServer::success('设置成功');
            }
            return JsonServer::error(GoodsLogic::getError());
        }
        $params = $this->request->get();
        $detail = GoodsLogic::detail($params);
        return view('', ['detail' => $detail]);
    }

    /**
     * @notes 参与分销/取消分销
     * @return \think\response\Json
     * @author Tab
     * @date 2021/9/2 10:03
     */
    public function isDistribution()
    {
        $params = $this->request->post();
        $params['shop_id'] = $this->shop_id;
        $result = GoodsLogic::isDistribution($params);
        if ($result) {
            return JsonServer::success('操作成功');
        }
        return JsonServer::error(GoodsLogic::getError());
    }
}