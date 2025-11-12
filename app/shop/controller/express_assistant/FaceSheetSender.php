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
use app\shop\logic\express_assistant\FaceSheetSenderLogic;

/**
 * 发件人模板
 * Class FaceSheetSender
 * @package app\shop\controller\express_assistant
 */
class FaceSheetSender extends ShopBase
{

    /**
     * @notes 发件人列表
     * @return \think\response\Json|\think\response\View
     * @author 段誉
     * @date 2023/2/13 16:39
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $list = FaceSheetSenderLogic::lists($get, $this->shop_id);
            return JsonServer::success("", $list);
        }
        return view();
    }


    /**
     * @notes 新增发件人模板
     * @return \think\response\Json|\think\response\View
     * @author 段誉
     * @date 2023/2/13 16:39
     */
    public function add()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $result = FaceSheetSenderLogic::add($post, $this->shop_id);
            if ($result !== true) {
                return JsonServer::error($result);
            }
            return JsonServer::success("操作成功");
        }
        return view();
    }


    /**
     * @notes 编辑发件人
     * @return \think\response\Json|\think\response\View
     * @author 段誉
     * @date 2023/2/13 16:40
     */
    public function edit()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $result = FaceSheetSenderLogic::edit($post, $this->shop_id);
            if ($result !== true) {
                return JsonServer::error($result);
            }
            return JsonServer::success("操作成功");
        }

        $id = $this->request->get('id');
        return view('', [
            'detail' => FaceSheetSenderLogic::detail($id, $this->shop_id)
        ]);
    }


    /**
     * @notes 删除发件人
     * @return \think\response\Json|void
     * @author 段誉
     * @date 2023/2/13 16:40
     */
    public function del()
    {
        if ($this->request->isAjax()) {
            $id = $this->request->post('id');
            $result = FaceSheetSenderLogic::del($id, $this->shop_id);
            if ($result !== true) {
                return JsonServer::error($result);
            }
            return JsonServer::success("操作成功");
        }
    }
}