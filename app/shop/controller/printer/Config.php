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

namespace app\shop\controller\printer;

use app\common\basics\ShopBase;
use app\shop\logic\printer\ConfigLogic;
use app\shop\validate\printer\ConfigValidate;
use app\common\server\JsonServer;

/**
 * 打印设置控制器
 * Class Config
 * @package app\admin\controller\printer
 */
class Config extends ShopBase
{

    /**
     * @notes 打印机配置列表
     * @return \think\response\Json|\think\response\View
     * @author 段誉
     * @date 2022/1/19 17:09
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $result = ConfigLogic::lists($this->shop_id);
            return JsonServer::success('',  $result);
        }
        return view();
    }

    /**
     * @notes 编辑打印机配置
     * @return \think\response\Json|\think\response\View
     * @author 段誉
     * @date 2022/1/19 17:09
     */
    public function edit()
    {
        $id = $this->request->get('id/d');
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            (new ConfigValidate())->goCheck();
            ConfigLogic::editConfig($post, $this->shop_id);
            return JsonServer::success('修改成功');
        }
        return view('', [
            'detail' => ConfigLogic::getDetail($id, $this->shop_id),
        ]);
    }

}