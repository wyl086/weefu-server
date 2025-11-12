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
namespace app\shop\controller\decoration;

use app\common\basics\ShopBase;
use app\common\enum\ShopAdEnum;
use app\common\model\shop\ShopAd;
use app\common\model\shop\ShopCategory;
use app\common\model\shop\ShopGoodsCategory;
use app\common\server\JsonServer;
use app\shop\logic\decoration\ShopAdLogic;
use think\response\Json;
use think\response\View;

class Ad extends ShopBase
{
    /**
     * @notes 广告列表
     * @return Json|View
     * @author lbzy
     * @datetime 2023-12-05 10:06:46
     */
    function lists()
    {
        if ($this->request->isAjax()) {
            return JsonServer::success('', ShopAdLogic::lists($this->request->get(), $this->shop_id));
        }
        
        return view();
    }
    
    /**
     * @notes 新增广告
     * @return Json|View
     * @author lbzy
     * @datetime 2023-12-05 11:58:07
     */
    function add()
    {
        if ($this->request->isAjax()) {
            $result = ShopAdLogic::add(input(), $this->shop_id);
            if ($result) {
                return JsonServer::success('添加成功');
            }
            
            return JsonServer::error(ShopAdLogic::getError() ? : '添加失败');
        }
        
        return view('', [
            'placeList'     => ShopAdEnum::getPlaceDesc(),
            'terminalList'  => ShopAdEnum::getTerminal(),
        ]);
    }
    
    /**
     * @notes 编辑广告
     * @return Json|View
     * @author lbzy
     * @datetime 2023-12-05 11:58:17
     */
    function edit()
    {
        
        if ($this->request->isAjax()) {
            $result = ShopAdLogic::edit(input(), $this->shop_id);
            if ($result) {
                return JsonServer::success('编辑成功');
            }
        
            return JsonServer::error(ShopAdLogic::getError() ? : '编辑失败');
        }
        
        
        return view('', [
            'info'          => ShopAd::where('id', input('id/d'))->where('shop_id', $this->shop_id)->findOrEmpty()->toArray(),
            'placeList'     => ShopAdEnum::getPlaceDesc(),
            'terminalList'  => ShopAdEnum::getTerminal(),
        ]);
    }
    
    /**
     * @notes 设置状态
     * @return Json
     * @author lbzy
     * @datetime 2023-12-05 11:59:01
     */
    function status()
    {
        if ($this->request->isAjax()) {
            ShopAdLogic::status(input(), $this->shop_id);
            return JsonServer::success('成功');
        }
    }
    
    /**
     * @notes 删除广告
     * @return Json
     * @author lbzy
     * @datetime 2023-12-05 11:58:34
     */
    function delete()
    {
        if ($this->request->isAjax()) {
            ShopAdLogic::delete(input(), $this->shop_id);
            return JsonServer::success('成功');
        }
    }
    
    /**
     * @notes 选择链接
     * @return Json|View
     * @author lbzy
     * @datetime 2023-12-05 18:33:00
     */
    function select_link()
    {
        if ($this->request->isAjax()) {
            
            return JsonServer::success('');
        }
        
        return view('', [
            'links'             => ShopAdEnum::getLinkPage(),
            'shopLinkPaths'     => ShopAdEnum::getShopLinkPaths(),
            'goodsCategoryList' => ShopGoodsCategory::where('shop_id', $this->shop_id)->where('is_show', 1)->select()->toArray(),
            'select_link'       => input('link/s', ''),
            'getShopGoodsListPath'      => ShopAdEnum::getShopGoodsListPath(),
            'getShopGoodsCategoryPath'  => ShopAdEnum::getShopGoodsCategoryPath(),
        ]);
    }
}