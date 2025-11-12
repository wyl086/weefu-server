<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | multshop团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshopTeam
// +----------------------------------------------------------------------
namespace app\admin\logic\live;

use app\common\basics\Logic;
use app\common\enum\LiveRoomEnum;
use app\common\exception\WechatException;
use app\common\model\live\LiveRoom;
use app\common\model\shop\Shop;
use app\common\server\UrlServer;
use app\common\server\WxMnpLiveServer;
use think\facade\Db;

/**
 * 直播间逻辑层
 * Class LiveRoomLogic
 * @package app\admin\logic\live
 */
class LiveRoomLogic extends Logic
{

    /**
     * @notes 列表条件
     * @param $params
     * @return array
     * @author 段誉
     * @date 2023/2/16 16:42
     */
    public static function listsQuery($params)
    {
        $where[] = ['del', '=', 0];
        if (!empty($params['shop_id'])) {
            $where[] = ['shop_id', '=', $params['shop_id']];
        }
        if (isset($params['status'])
            && $params['status'] != ''
            && in_array($params['status'], LiveRoomEnum::AUDIT_STATUS)) {
            $where[] = ['audit_status', '=', $params['status']];
        }
        if (!empty($params['live_info'])) {
            $where[] = ['name|anchor_name', 'like', '%' . $params['live_info'] . '%'];
        }
        if (!empty($params['live_status'])) {
            $where[] = ['live_status', '=', $params['live_status']];
        }
        // 创建时间
        if (isset($params['start_time']) && !empty($params['start_time'])) {
            $where[] = ['start_time', '>=', strtotime($params['start_time'])];
        }
        if (isset($params['end_time']) && !empty($params['end_time'])) {
            $where[] = ['end_time', '<=', strtotime($params['end_time'])];
        }
        return $where;
    }


    /**
     * @notes 直播间列表
     * @param $params
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2023/2/15 19:00
     */
    public static function lists($params)
    {
        $where = self::listsQuery($params);

        $count = LiveRoom::where($where)->count();
        $lists = LiveRoom::with(['shop'])->where($where)
            ->order(['id' => 'desc'])
            ->page($params['page'], $params['limit'])
            ->append(['live_time_text', 'live_status_text', 'audit_status_text'])
            ->select()->toArray();

        foreach ($lists as &$item) {
            $item['share_img'] = UrlServer::getFileUrl($item['share_img']);
            $item['feeds_img'] = UrlServer::getFileUrl($item['feeds_img']);
            $item['cover_img'] = UrlServer::getFileUrl($item['cover_img']);
        }
        return ['count' => $count, 'lists' => $lists];
    }



    /**
     * @notes 编辑
     * @param array $params
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author 段誉
     * @date 2023/2/16 10:34
     */
    public static function audit($params)
    {
        Db::startTrans();
        try {
            $room = LiveRoom::findOrEmpty($params['id']);
            if ($room['status'] > LiveRoomEnum::AUDIT_STATUS_WAIT) {
                throw new \Exception('该记录已审核');
            }

            if ($params['status'] == LiveRoomEnum::AUDIT_STATUS_FAIL && empty($params['audit_remark'])) {
                throw new \Exception('审核不通过请填写审核原因');
            }

            $update_data = [
                'audit_remark' => $params['audit_remark'] ?? '',
                'audit_status' => $params['status'],
            ];

            // 如果是审核通过,把直播间数据提交到微信并更新本地直播数据
            if ($params['status'] == LiveRoomEnum::AUDIT_STATUS_SUCCESS) {
                $room_id = self::createWxLiveRoom($room);
                $update_data['wx_room_id'] = $room_id;
            }

            // 直播间数据
            LiveRoom::where(['id' => $params['id']])->update($update_data);

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 创建直播间
     * @param $room
     * @return mixed
     * @throws WechatException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author 段誉
     * @date 2023/2/16 16:25
     */
    public static function createWxLiveRoom($room)
    {
        $data = [
            'name' => $room['name'],
            'startTime' => $room['start_time'],
            'endTime' => $room['end_time'],
            'anchorName' => $room['anchor_name'],
            'anchorWechat' => $room['anchor_wechat'],
            'createrWechat' => $room['anchor_wechat'],
            'shareImg' => $room['share_img_id'],
            'feedsImg' => $room['feeds_img_id'],
            'coverImg' => $room['cover_img_id'],
            'type' => $room['type'],
            'isFeedsPublic' => $room['is_feeds_public'],
            'closeLike' => $room['close_like'],
            'closeGoods' => $room['close_goods'],
            'closeComment' => $room['close_comment'],
            'closeReplay' => $room['close_replay'],
            'closeShare' => $room['close_share'],
            'closeKf' => $room['close_kf'],
        ];
        $result = (new WxMnpLiveServer())->handle('createLiveRoom', $data);
        return $result['roomId'];
    }


    /**
     * @notes 直播间详情
     * @param $id
     * @return array
     * @author 段誉
     * @date 2023/2/16 10:42
     */
    public static function detail($id)
    {
        $detail = LiveRoom::where(['id' => $id])
            ->append(['audit_status_text'])
            ->findOrEmpty()->toArray();
        $detail['start_time'] = date('Y-m-d H:i:s', $detail['start_time']);
        $detail['end_time'] = date('Y-m-d H:i:s', $detail['end_time']);
        $detail['share_img'] = UrlServer::getFileUrl($detail['share_img']);
        $detail['feeds_img'] = UrlServer::getFileUrl($detail['feeds_img']);
        $detail['cover_img'] = UrlServer::getFileUrl($detail['cover_img']);
        return $detail;
    }


    /**
     * @notes 推荐值排序
     * @param $params
     * @return LiveRoom
     * @author 段誉
     * @date 2023/2/16 16:44
     */
    public static function recommend($params)
    {
        return LiveRoom::update([
            'id' => $params['id'],
            'sort' => $params['sort'],
        ]);
    }


    /**
     * @notes 商家信息
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2023/2/16 14:10
     */
    public static function shopLists()
    {
        return Shop::field(['id', 'name'])
            ->where(['del' => 0])
            ->select()
            ->toArray();
    }


}