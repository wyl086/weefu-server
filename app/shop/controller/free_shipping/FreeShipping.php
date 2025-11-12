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

namespace app\shop\controller\free_shipping;

use app\common\basics\ShopBase;
use app\common\server\JsonServer;
use app\shop\logic\free_shipping\FreeShippingLogic;

class FreeShipping extends ShopBase
{
    public function index()
    {
        // 保存设置
        if ($this->request->isPost()) {
            $params = $this->request->post();
            $params['shop_id'] = $this->shop_id;
            $result = FreeShippingLogic::index($params);
            if ($result) {
                return JsonServer::success('保存成功');
            }
            return JsonServer::error(FreeShippingLogic::getError());
        }
        // 显示设置页
        $data = FreeShippingLogic::getData($this->shop_id);
        return view('', $data);
    }
}