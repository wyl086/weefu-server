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
use app\common\server\JsonServer;
use app\shop\logic\express_assistant\FaceSheetOrderLogic;
use app\shop\logic\express_assistant\FaceSheetSenderLogic;
use app\shop\logic\express_assistant\FaceSheetTplLogic;
use app\shop\validate\express_assistant\FaceSheetOrderValidate;

/**
 * 打印订单
 * Class FaceSheetOrder
 * @package app\shop\controller\express_assistant
 */
class FaceSheetOrder extends ShopBase
{

    /**
     * @notes 获取待发货订单列表
     * @return \think\response\Json|\think\response\View
     * @author 段誉
     * @date 2023/2/13 16:56
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $lists = FaceSheetOrderLogic::lists($get, $this->shop_id);
            return JsonServer::success("", $lists);
        }
        return view();
    }


    /**
     * @notes 选择打印模板
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2023/2/13 16:55
     */
    public function select()
    {
        return view("", [
            'template' => FaceSheetTplLogic::allTpl($this->shop_id),
            'sender' => FaceSheetSenderLogic::allSender($this->shop_id),
        ]);
    }


    /**
     * @notes 打印
     * @return \think\response\Json
     * @author 段誉
     * @date 2023/2/13 16:56
     */
    public function print()
    {
        if (!$this->request->isAjax()) {
            return JsonServer::error('请求异常');
        }
        $params = (new FaceSheetOrderValidate())->goCheck(null, [
            'shop_id' => $this->shop_id,
            'admin_id' => $this->admin_id
        ]);
        $result = FaceSheetOrderLogic::print($params);
        if ($result !== true) {
            return JsonServer::error(FaceSheetOrderLogic::getError());
        }
        return JsonServer::success('操作成功');

    }
}