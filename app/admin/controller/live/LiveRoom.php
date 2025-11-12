<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | multshop团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshopTeam
// +----------------------------------------------------------------------

namespace app\admin\controller\live;


use app\common\basics\AdminBase;
use app\common\enum\LiveRoomEnum;
use app\common\server\JsonServer;
use app\admin\logic\live\LiveRoomLogic;
use app\admin\validate\live\LiveRoomValidate;


/**
 * 直播间
 * Class LiveRoom
 * @package app\admin\controller\live
 */
class LiveRoom extends AdminBase
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
            $lists = LiveRoomLogic::lists($get);
            return JsonServer::success('', $lists);
        }
        return view('', [
            'live_status' => LiveRoomEnum::getLiveStatusDesc(),
            'shop' => LiveRoomLogic::shopLists(),
        ]);
    }


    /**
     * @notes 编辑直播间
     * @return \think\response\Json|\think\response\View
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author 段誉
     * @date 2023/2/16 10:38
     */
    public function audit()
    {
        if ($this->request->isAjax()) {
            $params = (new LiveRoomValidate())->goCheck('audit');
            $result = LiveRoomLogic::audit($params);
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
     * @notes 直播间详情
     * @return \think\response\View
     * @author 段誉
     * @date 2023/2/16 16:40
     */
    public function detail()
    {
        $id = $this->request->get('id');
        return view('', [
            'detail' => LiveRoomLogic::detail($id),
        ]);
    }


    /**
     * @notes 推荐值设置
     * @return \think\response\Json|\think\response\View
     * @author 段誉
     * @date 2023/2/16 16:56
     */
    public function recommend()
    {
        if ($this->request->isAjax()) {
            $params = (new LiveRoomValidate())->goCheck('recommend');
            $result = LiveRoomLogic::recommend($params);
            if ($result) {
                return JsonServer::success('操作成功');
            }
            return JsonServer::error(LiveRoomLogic::getError());
        }
        $id = $this->request->get('id');
        return view('', [
            'detail' => LiveRoomLogic::detail($id),
        ]);
    }


}