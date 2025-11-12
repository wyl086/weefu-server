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
namespace app\shop\controller\activity_area;

use app\common\basics\ShopBase;
use app\common\server\JsonServer;
use think\facade\View;
use app\shop\logic\activity_area\AreaLogic;
use app\shop\logic\activity_area\GoodsLogic;
use app\shop\validate\activity_area\ActivityGoodsValidate;

/**
 * Class Goods
 * @package app\shop\controller\activity_area
 */
class Goods extends ShopBase
{

    /**
     * @notes 活动专区商品列表
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/14 10:12 上午
     */
    public function lists()
    {

        $shop_id = $this->shop_id;
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $get['shop_id'] = $shop_id;
            $list = GoodsLogic::lists($get);
            return JsonServer::success('获取成功', $list);
        }
        $activity_area = AreaLogic::getActivityAreaAll();
        $num = GoodsLogic::getNum($shop_id);
        View::assign('num', $num);
        View::assign('activity_area', $activity_area);
        return View();
    }

    /**
     * @notes 新增活动专区商品
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/14 10:12 上午
     */
    public function add()
    {

        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $shop_id = $this->shop_id;
            $post['shop_id'] = $shop_id;
            $post['del'] = 0;

            (new ActivityGoodsValidate())->goCheck('add', $post);
            GoodsLogic::add($post);
            return JsonServer::success('添加成功');
        }
        $activity_area = AreaLogic::getActivityAreaAll();
        View::assign('activity_area', $activity_area);
        return View();
    }

    /**
     * @notes 编辑活动商品
     * @return \think\response\Json|\think\response\View
     * @author suny
     * @date 2021/7/14 10:12 上午
     */
    public function edit()
    {

        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $post['del'] = 0;
            (new ActivityGoodsValidate())->goCheck('edit', $post);
            $result = GoodsLogic::edit($post);
            if ($result) {
                return JsonServer::success('编辑成功');
            }
            return JsonServer::error('编辑失败');
        }
        $goods_id = $this->request->get('goods_id');
        $activity_id = $this->request->get('activity_id');
        View::assign('activity_list', GoodsLogic::getActivityList());
        View::assign('info', GoodsLogic::getActivityGoods($goods_id, $activity_id));
        return View();
    }

    /**
     * @notes 删除活动商品
     * @return \think\response\Json
     * @author suny
     * @date 2021/7/14 10:12 上午
     */
    public function del()
    {

        $id = $this->request->post('id', '', 'intval');
        $result = GoodsLogic::del($id);
        if ($result == true) {
            return JsonServer::success('删除成功');
        }
        return JsonServer::error('删除失败');
    }

}
