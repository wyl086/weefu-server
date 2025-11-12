<?php
namespace app\admin\controller\distribution;

use app\admin\logic\distribution\CenterLogic;
use app\common\basics\AdminBase;
use app\common\model\distribution\Distribution;
use app\common\model\distribution\DistributionLevel;
use app\common\model\user\User;
use app\common\server\JsonServer;
use app\common\model\distribution\DistributionOrderGoods;

class Center extends AdminBase
{
    public function data()
    {
        // 已结算: 已结算
        $settled = DistributionOrderGoods::where(['status'=>2])->sum('money');
        // 预估： 待返佣 + 已结算
        $estimate = DistributionOrderGoods::where('status', 'in', [1, 2])->sum('money');
        return view('', [
            'settled' => $settled,
            'estimate' => $estimate
        ]);
    }

    /**
     * @notes 数据概览
     * @return \think\response\Json
     * @author Tab
     * @date 2021/9/6 14:35
     */
    public function center()
    {
        $data = CenterLogic::center();
        return view('', ['data' => $data]);
    }

    /**
     * @notes 分销初始化数据
     * @return \think\response\Json
     * @author Tab
     * @date 2021/9/6 14:26
     */
    public function updateTable()
    {
        try {
            $defaultLevel = DistributionLevel::where('is_default', 1)->findOrEmpty()->toArray();
            if (empty($defaultLevel)) {
                // 没有默认等级，初始化
                DistributionLevel::create([
                    'name' => '默认等级',
                    'weights' => '1',
                    'is_default' => '1',
                    'remark' => '默认等级',
                    'update_relation' => '1'
                ]);
            }
            // 默认分销会员等级
            $defaultLevelId = DistributionLevel::where('is_default', 1)->value('id');
            // 生成分销基础信息表
            $users = User::field('id,is_distribution')
                ->where(['del' => 0])
                ->select()
                ->toArray();
            $distribution = Distribution::column('user_id');
            $addData = [];
            foreach($users as $item) {
                if (in_array($item['id'], $distribution)) {
                    // 已有基础分销记录，跳过
                    continue;
                }
                $data = [
                    'user_id' => $item['id'],
                    'level_id' => $defaultLevelId,
                    'is_distribution' => $item['is_distribution'],
                    'is_freeze' => 0,
                    'remark' => '',
                ];
                $addData[] = $data;
            }
            $distributionModel = new Distribution();
            $distributionModel->saveAll($addData);

            return JsonServer::success('初始化数据完成');
        } catch(\Exception $e) {
            return JsonServer::error($e->getMessage());
        }
    }
}