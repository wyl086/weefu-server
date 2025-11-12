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

namespace app\shop\controller\live;


use app\common\basics\ShopBase;
use app\common\enum\LiveGoodsEnum;
use app\common\enum\LiveRoomEnum;
use app\common\server\JsonServer;
use app\shop\logic\live\LiveGoodsLogic;
use app\shop\logic\live\LiveRoomLogic;
use app\shop\validate\live\LiveRoomValidate;


/**
 * 直播间
 * Class LiveRoom
 * @package app\shop\controller\live
 */
class LiveRoom extends ShopBase
{

    /**
     * @notes 直播间列表
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2023/2/16 10:38
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $get['shop_id'] = $this->shop_id;
            $lists = LiveRoomLogic::lists($get);
            return JsonServer::success('', $lists);
        }
        return view('', [
            'live_status' => LiveRoomEnum::getLiveStatusDesc()
        ]);
    }


    /**
     * @notes 添加直播间
     * @return \think\response\Json|\think\response\View
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author 段誉
     * @date 2023/2/16 10:38
     */
    public function add()
    {
        if ($this->request->isAjax()) {
            $params = (new LiveRoomValidate())->goCheck('add', ['shop_id' => $this->shop_id]);
            $result = LiveRoomLogic::add($params);
            if ($result !== true) {
                return JsonServer::error(LiveRoomLogic::getError());
            }
            return JsonServer::success('操作成功');
        }
        return view();
    }


    /**
     * @notes 编辑直播间
     * @return \think\response\Json|\think\response\View
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author 段誉
     * @date 2023/2/16 10:38
     */
    public function edit()
    {
        if ($this->request->isAjax()) {
            $params = (new LiveRoomValidate())->goCheck('edit', ['shop_id' => $this->shop_id]);
            $result = LiveRoomLogic::edit($params);
            if ($result !== true) {
                return JsonServer::error(LiveRoomLogic::getError());
            }
            return JsonServer::success('操作成功');
        }
        $id = $this->request->get('id');
        return view('', [
            'detail' => LiveRoomLogic::detail($id),
        ]);
    }


    /**
     * @notes 删除直播间
     * @return \think\response\Json|void
     * @author 段誉
     * @date 2023/2/16 10:38
     */
    public function del()
    {
        if ($this->request->isAjax()) {
            $params = (new LiveRoomValidate())->goCheck('del', ['shop_id' => $this->shop_id]);
            $result = LiveRoomLogic::del($params);
            if ($result !== true) {
                return JsonServer::error(LiveRoomLogic::getError());
            }
            return JsonServer::success('操作成功');
        }
    }


    /**
     * @notes 导入商品选择
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2023/2/17 12:08
     */
    public function selectGoods()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $get['shop_id'] = $this->shop_id;
            $get['status'] = 'success';
            $list = LiveGoodsLogic::lists($get);
            return JsonServer::success('',$list);
        }
        return view();
    }


    /**
     * @notes 导入直播商品
     * @return \think\response\Json|void
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author 段誉
     * @date 2023/2/17 14:26
     */
    public function importGoods()
    {
        if ($this->request->isAjax()) {
            $params = $this->request->post();
            $result = LiveRoomLogic::importGoods($params);
            if ($result !== true) {
                return JsonServer::error(LiveRoomLogic::getError());
            }
            return JsonServer::success('操作成功');
        }
    }


}