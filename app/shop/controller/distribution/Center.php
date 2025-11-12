<?php
namespace app\shop\controller\distribution;

use app\common\basics\ShopBase;
use app\common\server\JsonServer;
use app\common\model\distribution\DistributionOrderGoods;
use app\shop\logic\distribution\CenterLogic;

class Center extends ShopBase
{
    public function data()
    {
        // 已结算: 已结算
        $settled = DistributionOrderGoods::where([
            ['status', '=', '2'],
            ['shop_id', '=', $this->shop_id]
        ])->sum('money');


        // 预估： 待返佣 + 已结算
        $estimate = DistributionOrderGoods::where([
            ['status', 'in', [1, 2]],
            ['shop_id', '=', $this->shop_id]
        ])->sum('money');
        return view('', [
            'settled' => $settled,
            'estimate' => $estimate
        ]);
    }

    /**
     * @notes 分销概况
     * @author Tab
     * @date 2021/9/3 15:53
     */
    public function center()
    {
        $data = CenterLogic::center($this->shop_id);
        return view('', ['data' => $data]);
    }
}