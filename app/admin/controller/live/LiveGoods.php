<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | multshop团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshopTeam
// +----------------------------------------------------------------------

namespace app\admin\controller\live;


use app\admin\logic\live\LiveRoomLogic;
use app\common\basics\AdminBase;
use app\common\server\JsonServer;
use app\admin\logic\live\LiveGoodsLogic;
use app\admin\validate\live\LiveGoodsValidate;


/**
 * 直播商品
 * Class LiveGoods
 * @package app\admin\controller\live
 */
class LiveGoods extends AdminBase
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
            $lists = LiveGoodsLogic::lists($get);
            return JsonServer::success('', $lists);
        }
        return view('', [
            'shop' => LiveRoomLogic::shopLists()
        ]);
    }


    /**
     * @notes 添加直播商品
     * @return \think\response\Json|\think\response\View
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author 段誉
     * @date 2023/2/16 10:38
     */
    public function audit()
    {
        if ($this->request->isAjax()) {
            $params = (new LiveGoodsValidate())->goCheck('audit');
            $result = LiveGoodsLogic::audit($params);
            if ($result !== true) {
                return JsonServer::error(LiveGoodsLogic::getError());
            }
            return JsonServer::success('操作成功');
        }
        $id = $this->request->get('id');
        return view('', [
            'detail' => LiveGoodsLogic::detail($id),
        ]);
    }



    /**
     * @notes 直播商品详情
     * @return \think\response\View
     * @author 段誉
     * @date 2023/2/16 16:40
     */
    public function detail()
    {
        $params = (new LiveGoodsValidate())->goCheck('detail');
        return view('', [
            'detail' => LiveGoodsLogic::detail($params),
        ]);
    }


    /**
     * @notes 删除直播商品
     * @return \think\response\Json|void
     * @author 段誉
     * @date 2023/2/17 10:20
     */
    public function del()
    {
        if ($this->request->isAjax()) {
            $params = (new LiveGoodsValidate())->goCheck('del');
            $result = LiveGoodsLogic::del($params);
            if ($result !== true) {
                return JsonServer::error(LiveGoodsLogic::getError());
            }
            return JsonServer::success('操作成功');
        }
    }

}