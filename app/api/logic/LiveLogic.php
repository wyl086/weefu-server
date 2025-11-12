<?php
// +----------------------------------------------------------------------
// | Multshop多商户商城系统
// +----------------------------------------------------------------------
// | author: multshopTeam
// +----------------------------------------------------------------------

namespace app\api\logic;

use app\common\basics\Logic;
use app\common\enum\LiveRoomEnum;
use app\common\model\live\LiveRoom;
use app\common\server\UrlServer;


/**
 * 直播
 * Class LiveLogic
 * @package app\api\logic
 */
class LiveLogic extends Logic
{

    /**
     * @notes 直播间列表
     * @param $page_no
     * @param $page_size
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2023/2/17 16:52
     */
    public static function lists($page_no, $page_size)
    {
        $count = LiveRoom::where(['del' => 0])->count();
        $lists = LiveRoom::with(['shop'])->where(['del' => 0])
            ->where('wx_room_id', '>', 0)
            ->order(['live_status' => 'asc', 'sort' => 'asc', 'id' => 'desc'])
            ->hidden(['share_img_id', 'feeds_img_id', 'cover_img_id'])
            ->select();

        foreach ($lists as &$item) {
            $item['share_img'] = UrlServer::getFileUrl($item['share_img']);
            $item['feeds_img'] = UrlServer::getFileUrl($item['feeds_img']);
            $item['cover_img'] = UrlServer::getFileUrl($item['cover_img']);
            $item['live_status_text'] = LiveRoomEnum::getLiveStatusDesc($item['live_status']);
            $item['start_time_tips'] = friend_date($item['start_time']);
            $item['start_time_text'] = date('y-m-d H:i', $item['start_time']);
            $item['end_time_text'] = date('y-m-d H:i', $item['end_time']);
        }

        return [
            'list' => $lists,
            'count' => $count,
            'more' => is_more($count, $page_no, $page_size),
            'page_no' => $page_no,
            'page_size' => $page_size
        ];
    }


    /**
     * @notes 商家直播
     * @param $shopId
     * @return array
     * @author 段誉
     * @date 2023/2/17 17:22
     */
    public static function shopLive($shopId)
    {
        $room = LiveRoom::with(['shop'])->where([
            'shop_id' => $shopId,
            'live_status' => LiveRoomEnum::LIVE_STATUS_ING,
            'del' => 0
        ])
            ->where('wx_room_id', '>', 0)
            ->order(['sort' => 'asc', 'id' => 'desc'])
            ->hidden(['share_img_id', 'feeds_img_id', 'cover_img_id'])
            ->findOrEmpty()
            ->toArray();

        if (empty($room)) {
            // 如没找到直播中，则找距离当前最近的一个未开播
            $room = LiveRoom::with(['shop'])->where([
                'shop_id' => $shopId,
                'live_status' => LiveRoomEnum::LIVE_STATUS_WAIT,
                'del' => 0
            ])
                ->where('wx_room_id', '>', 0)
                ->where('start_time', '>', time())
                ->order(['sort' => 'asc', 'id' => 'desc', 'start_time' => 'asc'])
                ->hidden(['share_img_id', 'feeds_img_id', 'cover_img_id'])
                ->findOrEmpty()
                ->toArray();
        }

        if (!empty($room)) {
            $room['share_img'] = UrlServer::getFileUrl($room['share_img']);
            $room['feeds_img'] = UrlServer::getFileUrl($room['feeds_img']);
            $room['cover_img'] = UrlServer::getFileUrl($room['cover_img']);
            $room['live_status_text'] = LiveRoomEnum::getLiveStatusDesc($room['live_status']);
            $room['start_time_tips'] = friend_date($room['start_time']);
            $room['start_time_text'] = date('y-m-d H:i', $room['start_time']);
            $room['end_time_text'] = date('y-m-d H:i', $room['end_time']);
        }
        return $room;
    }


}