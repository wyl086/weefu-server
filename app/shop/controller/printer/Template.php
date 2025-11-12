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
use app\shop\logic\printer\TemplateLogic;
use app\shop\validate\printer\TemplateValidate;
use app\common\server\JsonServer;

/**
 * 小票模板控制器
 * Class Template
 * @package app\admin\controller\printer
 */
class Template extends ShopBase
{
    /**
     * @notes 编辑小票模板
     * @return \think\response\Json|\think\response\View
     * @author 段誉
     * @date 2022/1/19 16:45
     */
    public function edit()
    {
        if($this->request->isAjax()){
            $post = $this->request->post();
            (new TemplateValidate())->goCheck();
            $res = TemplateLogic::edit($post, $this->shop_id);
            if (true === $res) {
                return JsonServer::success('操作成功');
            }
            return JsonServer::error($res);
        }
        return view('', [
            'detail' => TemplateLogic::getDetail($this->shop_id),
        ]);
    }


}