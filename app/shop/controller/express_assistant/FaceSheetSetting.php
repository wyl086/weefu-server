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

namespace app\shop\controller\express_assistant;


use app\common\basics\ShopBase;
use app\common\server\ConfigServer;
use app\common\server\JsonServer;


/**
 * 面单
 * Class FaceSheet
 * @package app\shop\controller\express_assistant
 */
class FaceSheetSetting extends ShopBase
{

    /**
     * @notes 面单设置
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2023/2/13 14:38
     */
    public function setting()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            ConfigServer::set('faceSheet', 'type', $post['type'], $this->shop_id);
            ConfigServer::set('kd100', 'kd100_key', $post['kd100_key'], $this->shop_id);
            ConfigServer::set('kd100', 'kd100_secret', $post['kd100_secret'], $this->shop_id);
            ConfigServer::set('kd100', 'kd100_siid', $post['kd100_siid'], $this->shop_id);
            return JsonServer::success('修改成功');
        }

        $faceSheet = ConfigServer::get('faceSheet', null,null, $this->shop_id);
        $detail = ConfigServer::get('kd100', null, null, $this->shop_id);
        return view('', [
            'detail' => $detail,
            'faceSheet' => $faceSheet
        ]);
    }


}