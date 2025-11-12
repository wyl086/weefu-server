<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\admin\logic;


use app\common\enum\NoticeEnum;
use app\common\model\Notice;
use app\common\model\NoticeSetting;
use app\common\basics\Logic;
use app\common\model\user\User;
use think\Db;

/**
 * 通知设置逻辑
 * Class NoticeSettingLogic
 * @package app\admin\logic
 */
class NoticeSettingLogic extends Logic
{


    /**
     * Notes: 列表
     * @param $type
     * @author 段誉(2021/4/26 16:18)
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function lists($type)
    {
        $count = NoticeSetting::where('type', $type)->count();
        $lists = NoticeSetting::where('type', $type)->select();
        return ['lists' => $lists, 'count' => $count];
    }


    /**
     * Notes: 设置通知消息
     * @param $post
     * @author 段誉(2021/4/26 16:18)
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public static function set($post)
    {
        switch ($post['type']) {
            case 'system':
                $setting = [
                    'title' => $post['title'],
                    'content' => $post['content'],
                    'is_push' => $post['is_push'],
                    'status' => $post['status'],
                ];
                break;
            case 'sms':
                $setting = [
                    'template_code' => $post['template_code'],
                    'content' => $post['content'],
                    'status' => $post['status'],
                ];
                break;
            case 'oa':
                $setting = [
                    'name'     => $post['name'],
                    'first'    => $post['first'],
                    'template_sn' => $post['template_sn'],
                    'template_id' => $post['template_id'],
                    'remark'   => $post['remark'],
                    'status'   => $post['status'],
                    'tpl'      => self::formatTplData($post)
                ];
                break;
            case 'mnp':
                $setting = [
                    'name'     => $post['name'],
                    'template_sn' => $post['template_sn'],
                    'template_id' => $post['template_id'],
                    'status' => $post['status'],
                    'tpl'   => self::formatTplData($post),
                ];
                break;
            default:
                $setting = [];
        }

        NoticeSetting::where('id', $post['id'])
            ->update([
                $post['type'].'_notice' => json_encode($setting, JSON_UNESCAPED_UNICODE)
            ]);

        return true;
    }


    /**
     * Notes: 详情
     * @param $id
     * @param $type
     * @author 段誉(2021/6/4 15:01)
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function info($id, $type)
    {
        return NoticeSetting::where('id', $id)->find();
    }



    /**
     * Notes: 格式化模板数据
     * @param $post
     * @author 段誉(2021/4/26 14:55)
     * @return array
     */
    public static function formatTplData($post)
    {
        foreach ($post as &$value) {
            if (is_array($value)) {
                $value = array_values($value);
            }
        }
        return form_to_linear($post);
    }


    /**
     * Notes: 通知记录
     * @param $get
     * @author 段誉(2021/6/4 15:21)
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function record($get)
    {
        $where = [
            ['send_type', '=', $get['send_type']]
        ];

        if(isset($get['content']) && !empty(trim($get['content']))) {
            $where[] = ['content', 'like', '%' . trim($get['content']) . '%'];
        }
        if(isset($get['create_time']) && !empty(trim($get['create_time']))) {
            $createTimeArr = explode('#', $get['create_time']);
            $start_time = strtotime(trim($createTimeArr[0]));
            $end_time = strtotime(trim($createTimeArr[1]));
            $where[] = ['create_time', '>=', $start_time];
            $where[] = ['create_time', '<=', $end_time];
        }

        $scene = NoticeSetting::where(['id'=>$get['id']])->value('scene');
        $where[] = ['scene', '=', $scene];

        $lists = Notice::where($where)
            ->order('id desc')
            ->paginate([
                'list_rows'=> $get['limit'],
                'page'=> $get['page']
            ]);

        foreach($lists as &$item) {
            switch ($item['receive_type']) {
                case NoticeEnum::NOTICE_USER:
                    $user = User::find($item['user_id']);
                    $item['user_info'] = empty($user) ? '' : $user;
                    break;
                case NoticeEnum::NOTICE_PLATFORM:
                    $item['user_info'] = '平台';
                    break;
                case NoticeEnum::NOTICE_OTHER:
                    $item['user_info'] = '游客';
                    break;
                case NoticeEnum::NOTICE_SHOP:
                    $item['user_info'] = '商家';
                    break;
                default:
                    $item['user_info'] = '未知';
            }
        }
        return ['count' => $lists->total(), 'lists' => $lists->getCollection()];
    }
}