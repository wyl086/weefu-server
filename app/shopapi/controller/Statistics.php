<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 开源版本可自由商用，可去除界面版权logo
// | 商业版本务必购买商业授权，以免引起法律纠纷
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | gitee下载：https://gitee.com/multshop_gitee
// | github下载：https://github.com/multshop-github
// | 访问官网：https://www.multshop.cn
// | 访问社区：https://home.multshop.cn
// | 访问手册：http://doc.multshop.cn
// | 微信公众号：multshop技术社区
// | multshop团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshopTeam
// +----------------------------------------------------------------------
namespace app\shopapi\controller;
use app\common\basics\ShopApi;
use app\common\server\JsonServer;
use app\shopapi\logic\StatisticsLogic;

/**
 * 数据逻辑层
 * Class Statistics
 * @package app\shopapi\controller
 */
class Statistics extends ShopApi{

    /**
     * @notes 工作台
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/11/11 15:14
     */
    public function workbench(){
        $data = (new StatisticsLogic)->workbench($this->shop_id);
        return JsonServer::success('',$data);
    }


    /**
     * @notes 交易分析
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/11/11 14:37
     */
    public function trading(){
        $data = (new StatisticsLogic)->trading($this->shop_id);
        return JsonServer::success('',$data);
    }

    /**
     * @notes 商品分析接口
     * @param int $shop_id
     * @return array
     * @author cjhao
     * @date 2021/11/11 14:38
     */
    public function goodslist()
    {
        $data = (new StatisticsLogic())->goodslist($this->shop_id);
        return JsonServer::success('',$data);
    }


    /**
     * @notes 访问分析
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/11/11 14:43
     */
    public function visit()
    {
        $data = (new StatisticsLogic())->visit($this->shop_id);
        return JsonServer::success('',$data);

    }
}