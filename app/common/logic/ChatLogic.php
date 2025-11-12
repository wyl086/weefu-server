<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\common\logic;


use app\common\basics\Logic;
use app\common\enum\ChatMsgEnum;
use app\common\model\kefu\ChatRelation;
use app\common\model\kefu\Kefu;
use app\common\model\user\User;
use app\common\server\ConfigServer;
use app\common\server\UrlServer;
use app\common\utils\Redis;

class ChatLogic extends Logic
{

    /**
     * @notes
     * @param $shop_id
     * @return array|bool
     * @author 段誉
     * @date 2021/12/14 12:07
     */
    public static function getOnlineKefu($shop_id)
    {
        $key = self::getChatPrefix() . 'shop_' . $shop_id . '_kefu';
        return (new Redis())->getSmembersArray($key);
    }


    /**
     * @notes 在线用户
     * @return array|bool
     * @author 段誉
     * @date 2021/12/14 12:11
     */
    public static function getOnlineUser()
    {
        $key = self::getChatPrefix() . 'user';
        return (new Redis())->getSmembersArray($key);
    }


    /**
     * @notes 格式化聊天记录
     * @param $records
     * @param $count
     * @param $page
     * @param $size
     * @return array
     * @author 段誉
     * @date 2021/12/13 10:20
     */
    public static function formatChatRecords($records, $count, $page, $size)
    {
        if (empty($records)) {
            return [
                'list' => $records,
                'page' => $page,
                'size' => $size,
                'count' => $count,
                'more' => is_more($count, $page, $size)
            ];
        }

        $kefu = [];
        $user = [];

        // 获取到客服和用户不同的两组id
        foreach ($records as $item) {
            if ($item['from_type'] == 'kefu') {
                $kefu[] = $item['from_id'];
            } else {
                $user[] = $item['from_id'];
            }
        }

        $kefu = array_unique($kefu);
        $user = array_unique($user);


        $kefu = Kefu::where('id', 'in', $kefu)->column('nickname, avatar', 'id');
        $user = User::where('id', 'in', $user)->column('nickname, avatar', 'id');


        foreach ($records as &$item) {
            $item['from_nickname'] = '';
            $item['from_avatar'] = '';

            if ($item['from_type'] == 'kefu') {
                $kefu_id = $item['from_id'];
                if (isset($kefu[$kefu_id])) {
                    $item['from_nickname'] = $kefu[$kefu_id]['nickname'] ?? '';
                    $item['from_avatar'] = $kefu[$kefu_id]['avatar'] ?? '';
                }
            }

            if ($item['from_type'] == 'user') {
                $user_id = $item['from_id'];
                if (isset($user[$user_id])) {
                    $item['from_nickname'] = $user[$user_id]['nickname'] ?? '';
                    $item['from_avatar'] = $user[$user_id]['avatar'] ?? '';
                }
            }


            $item['goods'] = [];
            if ($item['msg_type'] == ChatMsgEnum::TYPE_GOODS) {
                $item['goods'] = json_decode($item['msg'], true);
            }

            $item['create_time_stamp'] = strtotime($item['create_time']);
        }

        $records = array_reverse($records);

        return [
            'list' => $records,
            'page' => $page,
            'size' => $size,
            'count' => $count,
            'more' => is_more($count, $page, $size)
        ];
    }


    /**
     * @notes 配置
     * @param $shop_id
     * @return array
     * @author 段誉
     * @date 2021/12/17 11:24
     * @remark code => 0时显示人工客服页,code => 1时显示在线客服页
     */
    public static function getConfig($shop_id)
    {
        // 后台客服配置 1->人工客服; 2->在线客服
        if (self::getConfigSetting($shop_id) == 1) {
            return ['code' => 0, 'msg' => ''];
        }

        // 缓存配置
        if ('redis' != self::getCacheDrive()) {
            return ['code' => 0, 'msg' => '请参考部署文档配置在线客服'];
        }

        // 当前在线客服
        $online = self::getOnlineKefu($shop_id);
        if (empty($online)) {
            return ['code' => 0, 'msg' => '当前客服不在线,有问题请联系人工客服'];
        }

        return ['code' => 1, 'msg' => ''];
    }


    /**
     * @notes 检查配置
     * @param int $shop_id
     * @return bool
     * @author 段誉
     * @date 2021/12/20 14:11
     */
    public static function checkConfig(int $shop_id = 0)
    {
        try {
            if (self::getConfigSetting($shop_id) == 1) {
                throw new \Exception('请联系管理员开启在线客服');
            }
            if ('redis' != self::getCacheDrive()) {
                throw new \Exception('请参考部署文档配置在线客服');
            }
            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 绑定关系
     * @param $user_id
     * @param $kefu_id
     * @param $shop_id
     * @param $data
     * @author 段誉
     * @date 2021/12/17 15:52
     */
    public static function bindRelation($user_id, $kefu_id, $shop_id, $data, $is_read = 0)
    {
        $relation = ChatRelation::where(['user_id' => $user_id, 'shop_id' => $shop_id])->findOrEmpty();

        $user = User::where(['id' => $user_id])->findOrEmpty();

        if ($relation->isEmpty()) {
            $relation = ChatRelation::create([
                'shop_id' => $shop_id,
                'user_id' => $user_id,
                'kefu_id' => $kefu_id,
                'nickname' => $user['nickname'],
                'avatar' => $user['avatar'],
                'client' => $data['client'] ?? 0,
                'msg' => $data['msg'] ?? '',
                'msg_type' => $data['msg_type'] ?? ChatMsgEnum::TYPE_TEXT,
                'is_read' => 1, // 新创建关系都算已读
                'create_time' => time(),
                'update_time' => time(),
            ]);
        } else {
            ChatRelation::update(
                [
                    'kefu_id' => $kefu_id,
                    'nickname' => $user['nickname'],
                    'avatar' => $user['avatar'],
                    'client' => $data['client'] ?? 0,
                    'msg' => $data['msg'] ?? '',
                    'msg_type' => $data['msg_type'] ?? ChatMsgEnum::TYPE_TEXT,
                    'update_time' => time(),
                    'is_read' => $is_read
                ],
                ['id' => $relation['id']]
            );
        }

        return $relation['id'];
    }


    /**
     * @notes 后台客服配置
     * @param $shop_id
     * @return array|int|mixed|string|null
     * @author 段誉
     * @date 2021/12/20 11:51
     */
    public static function getConfigSetting($shop_id)
    {
        // 后台客服配置 1->人工客服; 2->在线客服
        if ($shop_id > 0) {
            $config = ConfigServer::get('shop_customer_service', 'type', 1, $shop_id);
        } else {
            $config = ConfigServer::get('customer_service', 'type', 1);
        }
        return $config;
    }


    /**
     * @notes 当前缓存驱动
     * @return mixed
     * @author 段誉
     * @date 2021/12/20 11:51
     */
    public static function getCacheDrive()
    {
        return config('cache.default');
    }

    /**
     * @notes 聊天前缀
     * @return mixed
     * @author 段誉
     * @date 2022/4/14 17:42
     */
    public static function getChatPrefix()
    {
        return config('default.websocket_prefix');
    }


    /**
     * @notes 禁用客服
     * @param $shop_id
     * @param $kefu_id
     * @author 段誉
     * @date 2022/4/14 17:42
     */
    public static function setChatDisable($shop_id, $kefu_id)
    {
        $cache = new Redis();
        $prefix = self::getChatPrefix();
        $key = $prefix . 'shop_' . $shop_id . '_kefu';

        $result = $cache->getSmembersArray($key);
        $fds = $cache->getSmembersArray($prefix . 'kefu_' . $kefu_id);

        if (in_array($kefu_id, $result) && $fds) {
            $cache->srem($key, $kefu_id);
            foreach ($fds as $fd) {
                $cache->srem($prefix . 'kefu_' . $kefu_id, $fd);
                $cache->del($prefix . 'fd_' . $fd);
            }
        }
    }


}