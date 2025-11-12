<?php
namespace app\admin\controller\distribution;

use app\admin\logic\distribution\DistributionGoodsLogic;
use app\common\basics\AdminBase;
use app\admin\logic\goods\CategoryLogic as MallCategoryLogic;
use app\common\server\JsonServer;
use app\shop\logic\goods\CategoryLogic as ShopCategoryLogic;

/**
 * 分销商品
 * Class DistributionGoodsLogic
 * @package app\admin\controller\distribution
 */
class DistributionGoods extends AdminBase
{
    /**
     * @notes 分销商品列表页
     * @return \think\response\View
     * @author Tab
     * @date 2021/9/2 17:30
     */
    public function index()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();
            $lists = DistributionGoodsLogic::lists($params);
            return JsonServer::success('', $lists);
        }
        // 显示分销商品列表页
        $cate_list = MallCategoryLogic::categoryTreeeTree();
        return view('', ['cate_list' => $cate_list]);
    }

    /**
     * @notes 查看商品佣金比例
     * @return \think\response\View
     * @author Tab
     * @date 2021/9/2 17:57
     */
    public function detail()
    {
        $params = $this->request->get();
        $detail = DistributionGoodsLogic::detail($params);
        return view('', ['detail' => $detail]);
    }
}