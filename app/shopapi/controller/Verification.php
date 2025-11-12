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

namespace app\shopapi\controller;


use app\common\basics\ShopApi;
use app\common\server\JsonServer;
use app\shopapi\logic\VerificationLogic;
use app\shopapi\validate\VerificationValidate;
use app\shopapi\logic\OrderLogic;

/**
 * 自提核销
 * Class Verification
 * @package app\shopapi\controller
 */
class Verification extends ShopApi
{

    /**
     * @notes 核销订单
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/11/2 17:13
     */
    public function lists()
    {
        $params = $this->request->get();
        $result = VerificationLogic::lists($params, $this->page_no, $this->page_size, $this->shop_id);
        return JsonServer::success('获取成功', $result);
    }


    /**
     * @notes 订单详情
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/11/2 18:08
     */
    public function detail()
    {
        $params = $this->request->get();
        (new VerificationValidate())->goCheck('detail', ['shop_id' => $this->shop_id]);
        $result = VerificationLogic::detail($params, $this->shop_id);
        return JsonServer::success('', $result);
    }


    /**
     * @notes 核销订单
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/11/2 17:11
     */
    public function confirm()
    {
        $params = $this->request->post();
        (new VerificationValidate())->goCheck('confirm', ['shop_id' => $this->shop_id]);
        $result = VerificationLogic::verification($params, $this->shop);
        if(false === $result) {
            return JsonServer::error(OrderLogic::getError() ?: '操作失败');
        }
        return JsonServer::success('操作成功');
    }

}