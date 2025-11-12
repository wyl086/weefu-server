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


use app\common\enum\LiveRoomEnum;
use app\common\model\live\LiveRoom;
use app\common\server\WxMnpLiveServer;
use think\console\Command;
use think\console\Output;
use think\console\Input;
use think\facade\Log;


class WechatLiveRoom extends Command
{
    // 直播间数据
    protected $roomData = [];

    // 获取次数限制
    protected $requestlimit = 5;


    protected function configure()
    {
        $this->setName('wechat_live_room')
            ->setDescription('微信小程序直播状态同步');
    }


    protected function execute(Input $input, Output $output)
    {
        try {
            $liveStatus = [
                LiveRoomEnum::LIVE_STATUS_WAIT,
                LiveRoomEnum::LIVE_STATUS_ING,
                LiveRoomEnum::LIVE_STATUS_STOP,
                LiveRoomEnum::LIVE_STATUS_ERROR,
            ];
            $localRooms = LiveRoom::where('wx_room_id', '>', 0)
                ->where(['audit_status' => LiveRoomEnum::AUDIT_STATUS_SUCCESS, 'del' => 0])
                ->whereIn('live_status', $liveStatus)
                ->select()->toArray();

            if (empty($localRooms)) {
                return true;
            }

            $wxRooms = $this->getRooms();
            if (empty($wxRooms)) {
                return true;
            }

            $updateData = [];
            $wxRooms = array_column($wxRooms, null, 'roomid');
            foreach ($localRooms as $localRoom) {
                $localRoomId = $localRoom['wx_room_id'];
                if (!isset($wxRooms[$localRoomId])) {
                    continue;
                }
                $wxRoomData = $wxRooms[$localRoomId];
                $updateData[] = [
                    'id' => $localRoom['id'],
                    'goods_num' => count($wxRoomData['goods']),
                    'live_status' => $wxRoomData['live_status'],
                ];
            }

            if (!empty($updateData)) {
                (new LiveRoom())->saveAll($updateData);
            }

            return true;
        } catch (\Exception $e) {
            Log::write('更新直播间信息失败:' . $e->getMessage());
            return false;
        }
    }


    /**
     * @notes 获取直播间
     * @param int $start
     * @param int $limit
     * @return array
     * @throws \Exception
     * @author 段誉
     * @date 2023/2/17 18:45
     */
    protected function getRooms($start = 0, $limit = 100)
    {
        $result = (new WxMnpLiveServer())->handle('getRooms', [
            'start' => $start,
            'limit' => $limit,
        ]);

        if (0 != $result['errcode']) {
            return [];
        }

        $this->requestlimit -= 1;
        $this->roomData = array_merge($result['room_info'], $this->roomData);

        if ($result['total'] == $limit && $this->requestlimit > 0) {
            return $this->getRooms($limit + 1, $limit);
        }

        return $this->roomData;
    }


}