<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\common\command;


use app\common\enum\LiveGoodsEnum;
use app\common\model\live\LiveGoods;
use app\common\server\WxMnpLiveServer;
use think\console\Command;
use think\console\Output;
use think\console\Input;
use think\facade\Log;


class WechatLiveGoods extends Command
{

    protected function configure()
    {
        $this->setName('wechat_live_goods')
            ->setDescription('微信小程序直播商品状态同步');
    }


    protected function execute(Input $input, Output $output)
    {
        try {
            $liveGoodsModel = new LiveGoods();
            // 系统待微信审核的商品
            $localGoods = $liveGoodsModel->where([
                'del' => 0,
                'sys_audit_status' => LiveGoodsEnum::SYS_AUDIT_STATUS_WAIT_WECHAT
            ])
                ->select()->toArray();

            if (empty($localGoods)) {
                return true;
            }

            // 切分为20个一组，获取每组的商品状态更新
            $localGoods = array_chunk($localGoods, 20);
            foreach ($localGoods as $localGoodsItem) {
                $goodsIds = array_column($localGoodsItem, 'wx_goods_id');
                $wxGoodsData = $this->getGoods($goodsIds);
                $wxGoodsData = $wxGoodsData['goods'] ?? [];
                $wxGoodsDataColumn = array_column($wxGoodsData, null,'goods_id');

                $updateData = [];
                foreach ($localGoodsItem as $goods) {
                    $wxGoodsId = $goods['wx_goods_id'];
                    if (!isset($wxGoodsDataColumn[$wxGoodsId])) {
                        continue;
                    }
                    $wxGoods = $wxGoodsDataColumn[$wxGoodsId];
                    $data = [
                        'id' => $goods['id'],
                        'wx_audit_status' => $wxGoods['audit_status'],
                        'audit_remark' => LiveGoodsEnum::getWxAuditStatusDesc($wxGoods['audit_status']),
                    ];
                    if ($wxGoods['audit_status'] == LiveGoodsEnum::WX_AUDIT_STATUS_SUCCESS) {
                        $data['sys_audit_status'] = LiveGoodsEnum::SYS_AUDIT_STATUS_SUCCESS;
                    }
                    if ($wxGoods['audit_status'] == LiveGoodsEnum::WX_AUDIT_STATUS_FAIL) {
                        $data['sys_audit_status'] = LiveGoodsEnum::SYS_AUDIT_STATUS_FAIL;
                    }
                    $updateData[] = $data;
                }

                if (!empty($updateData)) {
                    $liveGoodsModel->saveAll($updateData);
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::write('更新直播商品状态失败:' . $e->getMessage());
            return false;
        }
    }


    /**
     * @notes 获取商品
     * @param $goodsIds
     * @return array
     * @throws \Exception
     * @author 段誉
     * @date 2023/2/17 19:24
     */
    protected function getGoods($goodsIds)
    {
        $result = (new WxMnpLiveServer())->handle('getGoodsWarehouse', $goodsIds);
        if (0 != $result['errcode']) {
            return [];
        }
        return $result;
    }

}