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
namespace app\shop\logic\live;

use app\common\basics\Logic;
use app\common\enum\LiveRoomEnum;
use app\common\model\live\LiveGoods;
use app\common\model\live\LiveRoom;
use app\common\server\FileServer;
use app\common\server\UrlServer;
use app\common\server\WxMnpLiveServer;
use think\facade\Db;


/**
 * 直播间逻辑层
 * Class LiveRoomLogic
 * @package app\adminapi\logic\live
 */
class LiveRoomLogic extends Logic
{

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
        $where[] = ['del', '=', 0];
        $where[] = ['shop_id', '=', $params['shop_id']];
        if (isset($params['status']) && in_array($params['status'], LiveRoomEnum::AUDIT_STATUS)) {
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

        $count = LiveRoom::where($where)->count();
        $lists = LiveRoom::where($where)
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
     * @notes 添加直播间
     * @param array $params
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author 段誉
     * @date 2023/2/15 18:26
     */
    public static function add(array $params)
    {
        try {
            $params = self::formatParams($params);
            LiveRoom::create([
                'shop_id' => $params['shop_id'],
                'name' => $params['name'],
                'type' => $params['type'],
                'start_time' => $params['start_time'],
                'end_time' => $params['end_time'],
                'anchor_name' => $params['anchor_name'],
                'anchor_wechat' => $params['anchor_wechat'],
                'share_img' => UrlServer::setFileUrl($params['share_img']),
                'feeds_img' => UrlServer::setFileUrl($params['feeds_img']),
                'cover_img' => UrlServer::setFileUrl($params['cover_img']),
                'share_img_id' => FileServer::wechatLiveMaterial($params['share_img']),
                'feeds_img_id' => FileServer::wechatLiveMaterial($params['feeds_img']),
                'cover_img_id' => FileServer::wechatLiveMaterial($params['cover_img']),
                'is_feeds_public' => $params['is_feeds_public'],
                'close_like' => $params['close_like'],
                'close_goods' => $params['close_goods'],
                'close_comment' => $params['close_comment'],
                'close_replay' => $params['close_replay'],
                'close_share' => $params['close_share'],
                'close_kf' => $params['close_kf'],
            ]);
            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 编辑
     * @param array $params
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author 段誉
     * @date 2023/2/16 10:34
     */
    public static function edit(array $params)
    {
        Db::startTrans();
        try {
            $params = self::formatParams($params);
            $room = LiveRoom::findOrEmpty($params['id']);

            $updateData = [
                'name' => $params['name'],
                'type' => $params['type'],
                'start_time' => $params['start_time'],
                'end_time' => $params['end_time'],
                'anchor_name' => $params['anchor_name'],
                'anchor_wechat' => $params['anchor_wechat'],
                'share_img' => UrlServer::setFileUrl($params['share_img']),
                'feeds_img' => UrlServer::setFileUrl($params['feeds_img']),
                'cover_img' => UrlServer::setFileUrl($params['cover_img']),
                'share_img_id' => FileServer::wechatLiveMaterial($params['share_img']),
                'feeds_img_id' => FileServer::wechatLiveMaterial($params['feeds_img']),
                'cover_img_id' => FileServer::wechatLiveMaterial($params['cover_img']),
                'is_feeds_public' => $params['is_feeds_public'],
                'close_like' => $params['close_like'],
                'close_goods' => $params['close_goods'],
                'close_comment' => $params['close_comment'],
                'close_replay' => $params['close_replay'],
                'close_share' => $params['close_share'],
                'close_kf' => $params['close_kf'],
            ];

            if ($room['audit_status'] == LiveRoomEnum::AUDIT_STATUS_FAIL) {
                $updateData['audit_status'] = LiveRoomEnum::AUDIT_STATUS_WAIT;
            }

            LiveRoom::where(['id' => $params['id']])->update($updateData);

            if (!empty($room['wx_room_id'])) {
                (new WxMnpLiveServer())->handle('editRoom', [
                    'id' => $room['wx_room_id'],
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
                ]);
            }

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
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
        $detail = LiveRoom::where(['id' => $id])->findOrEmpty()->toArray();
        $detail['start_time'] = date('Y-m-d H:i:s', $detail['start_time']);
        $detail['end_time'] = date('Y-m-d H:i:s', $detail['end_time']);
        $detail['share_img'] = UrlServer::getFileUrl($detail['share_img']);
        $detail['feeds_img'] = UrlServer::getFileUrl($detail['feeds_img']);
        $detail['cover_img'] = UrlServer::getFileUrl($detail['cover_img']);
        return $detail;
    }


    /**
     * @notes 删除直播间
     * @param array $params
     * @return bool|string
     * @author 段誉
     * @date 2023/2/16 10:37
     */
    public static function del(array $params)
    {
        Db::startTrans();
        try {
            $where = [
                'id' => $params['id'],
                'shop_id' => $params['shop_id']
            ];
            LiveRoom::where($where)->update([
                'del' => 1,
                'update_time' => time()
            ]);

            $room = LiveRoom::findOrEmpty($params['id']);
            if (!empty($room['wx_room_id'])) {
                (new WxMnpLiveServer())->handle('delRoom', $room['wx_room_id']);
            }
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }


    /**
     * @notes 格式化参数
     * @param $params
     * @return mixed
     * @author 段誉
     * @date 2023/2/15 16:04
     */
    public static function formatParams($params)
    {
        if (!empty($params['start_time'])) {
            $params['start_time'] = strtotime($params['start_time']);
        }

        if (!empty($params['end_time'])) {
            $params['end_time'] = strtotime($params['end_time']);
        }

        $params['is_feeds_public'] = empty($params['is_feeds_public']) ? 0 : 1;
        $params['close_like'] = empty($params['close_like']) ? 1 : 0;
        $params['close_goods'] = empty($params['close_goods']) ? 1 : 0;
        $params['close_comment'] = empty($params['close_comment']) ? 1 : 0;
        $params['close_replay'] = empty($params['close_replay']) ? 1 : 0;
        $params['close_share'] = empty($params['close_share']) ? 1 : 0;
        $params['close_kf'] = empty($params['close_kf']) ? 1 : 0;

        return $params;
    }


    /**
     * @notes 导入商品
     * @param $params
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author 段誉
     * @date 2023/2/17 14:25
     */
    public static function importGoods($params)
    {
        try {
            if (empty($params['id'])) {
                throw new \Exception('直播间参数缺失');
            }

            if (empty($params['goods_ids'])) {
                throw new \Exception('请选择直播商品');
            }

            $room = LiveRoom::where(['id' => $params['id']])->findOrEmpty();
            if (empty($room['wx_room_id'])) {
                throw new  \Exception('当前直播间暂不可导入商品');
            }

            $goods_ids = LiveGoods::whereIn('id', $params['goods_ids'])
                ->column('wx_goods_id');

            //addGoods
            (new WxMnpLiveServer())->handle('importGoods', [
                'ids' => $goods_ids,
                'roomId' => $room['wx_room_id']
            ]);

            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

}