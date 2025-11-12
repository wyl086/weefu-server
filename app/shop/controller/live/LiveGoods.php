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
use app\common\enum\LiveRoomEnum;
use app\common\server\JsonServer;
use app\shop\logic\live\LiveGoodsLogic;
use app\shop\validate\live\LiveGoodsValidate;


/**
 * 直播商品
 * Class LiveGoods
 * @package app\shop\controller\live
 */
class LiveGoods extends ShopBase
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
            $lists = LiveGoodsLogic::lists($get);
            return JsonServer::success('', $lists);
        }
        return view('', [
            'live_status' => LiveRoomEnum::getLiveStatusDesc()
        ]);
    }


    /**
     * @notes 添加直播商品
     * @return \think\response\Json|\think\response\View
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author 段誉
     * @date 2023/2/16 10:38
     */
    public function add()
    {
        if ($this->request->isAjax()) {
            $params = (new LiveGoodsValidate())->goCheck('add', ['shop_id' => $this->shop_id]);
            $result = LiveGoodsLogic::add($params);
            if ($result !== true) {
                return JsonServer::error(LiveGoodsLogic::getError());
            }
            return JsonServer::success('操作成功');
        }
        return view();
    }


    /**
     * @notes 直播商品详情
     * @return \think\response\View
     * @author 段誉
     * @date 2023/2/16 16:40
     */
    public function detail()
    {
        $params = (new LiveGoodsValidate())->goCheck('detail', ['shop_id' => $this->shop_id]);
        return view('', [
            'detail' => LiveGoodsLogic::detail($params),
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
            $params = (new LiveGoodsValidate())->goCheck('del', ['shop_id' => $this->shop_id]);
            $result = LiveGoodsLogic::del($params);
            if ($result !== true) {
                return JsonServer::error(LiveGoodsLogic::getError());
            }
            return JsonServer::success('操作成功');
        }
    }


}