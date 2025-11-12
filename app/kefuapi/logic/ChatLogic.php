<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | gitee下载：https://gitee.com/multshop_gitee
// | github下载：https://github.com/multshop-github
// | 访问官网：https://www.multshop.cn
// | 访问社区：https://home.multshop.cn
// | 访问手册：http://doc.multshop.cn
// | 微信公众号：multshop技术社区
// | multshop系列产品在gitee、github等公开渠道开源版本可免费商用，未经许可不能去除前后端官方版权标识
// |  multshop系列产品收费版本务必购买商业授权，购买去版权授权后，方可去除前后端官方版权标识
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\kefuapi\logic;

use app\common\basics\Logic;
use app\common\enum\ChatRecordEnum;
use app\common\model\kefu\ChatRecord;
USE app\common\logic\ChatLogic as CommonChatLogic;
use app\common\model\kefu\ChatRelation;
use app\common\model\kefu\Kefu;
use app\common\model\kefu\KefuLang;
use app\common\model\order\Order;
use app\common\model\user\User;
use app\common\server\ConfigServer;
use app\common\server\UrlServer;


class ChatLogic extends Logic
{

    /**
     * @notes 曾对话过的用户
     * @param $kefu_id
     * @param $shop_id
     * @param $get
     * @param $page
     * @param $size
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2021/12/14 12:11
     */
    public static function getChatUserList($kefu_id, $shop_id, $get, $page, $size)
    {
        $where[] = ['kefu_id', '=', $kefu_id];
        $where[] = ['shop_id', '=', $shop_id];

        if (isset($get['nickname']) && $get['nickname']) {
            $where[] = ['nickname', 'like', '%' . $get['nickname'] . '%'];
        }

        $online_user = CommonChatLogic::getOnlineUser();

        $exp = 'update_time desc';
        if (!empty($online_user)) {
            $user_id = implode(",", $online_user);
            $exp = "field(user_id," . $user_id . ") desc, update_time desc";
        }

        // 当前客服曾聊天的记录
        $lists = ChatRelation::where($where)
            ->page($page, $size)
            ->orderRaw($exp)
            ->select();

        $count = ChatRelation::where($where)->count();

        foreach ($lists as &$item) {
            $item['online'] = 0;
            if (in_array($item['user_id'], $online_user)) {
                $item['online'] = 1;
            }

            if (empty($item['msg'])) {
                $item['update_time'] = '';
            }
        }

        return [
            'list' => $lists->toArray(),
            'page' => $page,
            'size' => $size,
            'count' => $count,
            'more' => is_more($count, $page, $size)
        ];
    }


    /**
     * @notes 客服与用户的聊天记录
     * @param $kefu_id
     * @param $user_id
     * @param $shop_id
     * @param $page
     * @param $size
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2021/12/14 14:41
     */
    public static function getChatRecord($kefu_id, $user_id, $shop_id, $page, $size)
    {
        $map1 = [
            ['shop_id', '=', $shop_id],
            ['from_id', '=', $kefu_id],
            ['from_type', '=', 'kefu'],
            ['to_id', '=', $user_id],
            ['type', '=', ChatRecordEnum::TYPE_NORMAL]
        ];
        $map2 = [
            ['shop_id', '=', $shop_id],
            ['to_id', '=', $kefu_id],
            ['to_type', '=', 'kefu'],
            ['from_id', '=', $user_id],
            ['type', '=', ChatRecordEnum::TYPE_NORMAL]
        ];

        // 聊天记录
        $records = ChatRecord::whereOr([$map1, $map2])
            ->order('id desc')
            ->page($page, $size)
            ->select()->toArray();

        $count = ChatRecord::whereOr([$map1, $map2])->count();

        $records = CommonChatLogic::formatChatRecords($records, $count, $page, $size);

        return $records;
    }


    /**
     * @notes 获取在线客服
     * @param $shop_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2021/12/14 14:39
     */
    public static function getOnlineKefu($kefu_id, $shop_id)
    {
        $online = CommonChatLogic::getOnlineKefu($shop_id);

        if (empty($online)) {
            return [];
        }

        $map = [
            ['id', 'in', $online],
            ['id', '<>', $kefu_id],
            ['shop_id', '=', $shop_id]
        ];

        $lists = Kefu::where($map)
            ->field('id,nickname,avatar')
            ->select()
            ->toArray();

        foreach ($lists as &$item) {
            $item['avatar'] = UrlServer::getFileUrl($item['avatar']);
        }
        return $lists;
    }


    /**
     * @notes 快捷回复列表
     * @param $shop_id
     * @param $keyword
     * @param $page
     * @param $size
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2021/12/15 11:07
     */
    public static function getReplyLists($shop_id, $keyword, $page, $size)
    {
        $condition[] = ['title', 'like', '%' . $keyword . '%'];

        $lists = KefuLang::where(['shop_id' => $shop_id])
            ->where($condition)
            ->page($page, $size)
            ->order('sort')
            ->select();

        $count = KefuLang::where(['shop_id' => $shop_id])->count();

        return [
            'list' => $lists,
            'page' => $page,
            'size' => $size,
            'count' => $count,
            'more' => is_more($count, $page, $size)
        ];
    }


    /**
     * @notes 用户信息接口
     * @param $user_id
     * @return array|bool
     * @author 段誉
     * @date 2021/12/15 15:05
     */
    public static function getUserInfo($user_id)
    {
        try {
            $user = User::where(['id' => $user_id, 'del' => 0])->field([
                'id', 'sn', 'nickname', 'avatar',
                'level', 'mobile', 'total_order_amount',
                'birthday', 'client', 'create_time'
            ])->findOrEmpty()->append(['level_name', 'client_desc'])->toArray();

            if (empty($user)) {
                throw new \Exception('用户不存在');
            }

            $user['birthday'] = empty($user['birthday']) ? '-' : $user['birthday'];
            $user['avatar'] = empty($user['avatar']) ? '' : UrlServer::getFileUrl($user['avatar']);
            $user['mobile'] = empty($user['mobile']) ? '-' : substr_replace($user['mobile'],'****',3,4);

            return $user;

        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 订单列表
     * @param $get
     * @param $shop_id
     * @param $page
     * @param $size
     * @return array|bool
     * @author 段誉
     * @date 2021/12/15 16:04
     */
    public static function getOrderLists($get, $shop_id, $page, $size)
    {
        try{
            if (empty($get['user_id'])) {
                throw new \Exception('参数缺失');
            }

            $condition[] = ['user_id', '=', $get['user_id']];
            $condition[] = ['del', '=', 0];

            if ($shop_id > 0) {
                $condition[] = ['shop_id', '=', $shop_id];
            }

            if (isset($get['order_sn']) && $get['order_sn'] != '') {
                $condition[] = ['order_sn', 'like', '%' . $get['order_sn'] . '%'];
            }

            $order = new Order();

            $count = $order->with('order_goods')->where($condition)->count();

            $lists = $order
                ->where($condition)
                ->with('order_goods')
                ->field(['id', 'order_sn', 'order_type', 'order_status', 'order_amount', 'create_time'])
                ->append(['order_status_text', 'order_type_text'])
                ->page($page, $size)
                ->order('id desc')
                ->select()->toArray();

            return [
                'list' => $lists,
                'page' => $page,
                'size' => $size,
                'count' => $count,
                'more' => is_more($count, $page, $size)
            ];

        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 客服详情
     * @param $id
     * @return array
     * @author 段誉
     * @date 2021/12/15 17:34
     */
    public static function getKefuInfo($id)
    {
        $res = Kefu::where(['id' => $id])
            ->field(['id', 'shop_id', 'nickname', 'avatar'])
            ->findOrEmpty()
            ->toArray();
        $res['avatar'] = empty($res['avatar']) ? '' : UrlServer::getFileUrl($res['avatar']);

        $online = CommonChatLogic::getOnlineKefu($res['shop_id']);

        $res['online'] = 0;
        if(in_array($res['id'], $online)) {
            $res['online'] = 1;
        }

        return $res;
    }


    /**
     * @notes 上传文件域名
     * @return array
     * @author 段誉
     * @date 2021/12/16 17:05
     */
    public static function getConfig()
    {
        $web_favicon = ConfigServer::get('website', 'web_favicon');
        return [
            'base_domain' => UrlServer::getFileUrl(),
            'web_favicon' => !empty($web_favicon) ? UrlServer::getFileUrl($web_favicon) : $web_favicon,
            'company_name' => ConfigServer::get('copyright', 'company_name'),
            'ws_domain' => env('project.ws_domain', 'ws:127.0.0.1')
        ];
    }

}
