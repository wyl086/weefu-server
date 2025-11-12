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
use app\shopapi\logic\IndexLogic;
use app\common\server\JsonServer;

/**
 * 商家移动端账号登录
 * Class Account
 * @package app\shopapi\controller
 */
class Index extends ShopApi
{

    public $like_not_need_login = ['config','copyright'];


    /**
     * @notes 基础配置
     * @return \think\response\Json
     * @author 段誉
     * @date 2021/11/13 17:12
     */
    public function config()
    {
        return JsonServer::success('', IndexLogic::config());
    }

    /**
     * @notes 版权资质
     * @return \think\response\Json
     * @author ljj
     * @date 2022/2/22 11:10 上午
     */
    public function copyright()
    {
        $shop_id = $this->shop_id;
        $result = IndexLogic::copyright($shop_id);
        return JsonServer::success('',$result);

    }
}