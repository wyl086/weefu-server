<?php
namespace app\admin\controller\setting;

use app\admin\logic\setting\MarketingConfigLogic;
use app\common\basics\AdminBase;
use app\common\enum\OrderEnum;
use app\common\server\ConfigServer;
use app\common\server\JsonServer;

/**
 * 营销设置
 * Class MarketingConfig
 * @package app\admin\controller\setting
 */
class MarketingConfig extends AdminBase
{
    /**
     * @notes 消费奖励
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2022/2/18 4:10 下午
     */
    public function orderAward()
    {
        if($this->request->isAjax()) {
            $post = $this->request->post();
            $result = MarketingConfigLogic::setOrderAward($post);
            if ($result !== true) {
                return JsonServer::error($result);
            }
            return JsonServer::success('设置成功');
        }

        return view('', [
            'award_event_lists' => OrderEnum::getOrderAward(true),
            'open_award' => ConfigServer::get('order_award','open_award',0),
            'award_event' => ConfigServer::get('order_award','award_event',0),
            'award_ratio' => ConfigServer::get('order_award','award_ratio'),
        ]);
    }

}