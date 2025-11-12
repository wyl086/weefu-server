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

namespace app\shop\logic\kefu;

use app\common\basics\Logic;
use app\common\enum\KefuEnum;
use app\common\logic\ChatLogic;
use app\common\model\Client_;
use app\common\model\kefu\Kefu;
use app\common\model\shop\ShopAdmin;
use app\common\model\shop\ShopRole;
use app\common\server\UrlServer;
use app\kefuapi\logic\LoginLogic;


/** 客服逻辑
 * Class KefuLogic
 * @package app\shop\logic\kefu
 */
class KefuLogic extends Logic
{

    /**
     * @notes 客服列表
     * @param $get
     * @return array
     * @author 段誉
     * @date 2021/11/26 18:44
     */
    public static function getLists($get, $shop_id)
    {
        $result = (new Kefu())->alias('k')
            ->field("k.*,a.account")
            ->join('shop_admin a', 'a.id = k.admin_id')
            ->where(['a.del' => 0, 'k.del' => 0, 'k.shop_id' => $shop_id])
            ->order('sort asc')->paginate([
                'list_rows' => $get['limit'],
                'page' => $get['page'],
            ]);

        foreach ($result as $value) {
            $value['avatar'] = empty($value['avatar']) ? "" : UrlServer::getFileUrl($value['avatar']);
        }

        return ['count' => $result->total(), 'lists' => $result->getCollection()];
    }


    /**
     * @notes 添加客服
     * @param $post
     * @param $shop_id
     * @return Kefu|false|\think\Model
     * @author 段誉
     * @date 2021/11/27 11:38
     *
     */
    public static function add($post, $shop_id)
    {
        try {
            return (new Kefu())->insertKefu($post, $shop_id);
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 编辑客服
     * @param $post
     * @param $shop_id
     * @return Kefu|false
     * @author 段誉
     * @date 2021/11/27 10:44
     */
    public static function edit($post, $shop_id)
    {
        try {

            if ($post['disable'] == 1) {
                ChatLogic::setChatDisable($shop_id, $post['id']);
            }

            return (new Kefu())->updateKefu($post['id'], $post, $shop_id);

        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 详情
     * @param $id
     * @param $shop_id
     * @return mixed
     * @author 段誉
     * @date 2021/11/27 10:44
     */
    public static function detail($id, $shop_id)
    {
        $detail = (new Kefu())->alias('k')
            ->field("k.*, a.account, a.name")
            ->join('shop_admin a', 'a.id = k.admin_id')
            ->where(['k.id' => $id, 'k.shop_id' => $shop_id])
            ->findOrEmpty();

        $detail['avatar'] = !empty($detail['avatar']) ? UrlServer::getFileUrl($detail['avatar']) : '';
       return $detail;
    }


    /**
     * @notes 删除客服
     * @param $postq
     * @param $shop_id
     * @return Kefu
     * @author 段誉
     * @date 2021/11/27 10:48
     */
    public static function del($post, $shop_id)
    {
        return (new Kefu())->delKefu($post['id'], $shop_id);
    }


    /**
     * @notes 管理员列表
     * @param $get
     * @param $shop_id
     * @return array
     * @throws \think\db\exception\DbException
     * @author 段誉
     * @date 2021/11/26 18:00
     */
    public static function getAdminLists($get, $shop_id)
    {
        // 角色名称
        $role_column = (new ShopRole())->getNameColumn();

        // 已有客服列表
        $kefu = (new Kefu())->where(['del' => 0, 'shop_id' => $shop_id])->column("admin_id");

        // 查询条件
        $where[] = ['del', '=', 0];
        $where[] = ['id', 'not in', $kefu];
        $where[] = ['shop_id', '=', $shop_id];
        if (isset($get['role_id']) && $get['role_id'] != '') {
            $where[] = ['role_id', '=', $get['role_id']];
        }
        if (isset($get['name']) && $get['name'] != '') {
            $where[] = ['name', 'like', "%{$get['name']}%"];
        }

        $result = (new ShopAdmin())->where($where)
            ->hidden(['password', 'salt'])
            ->paginate([
                'list_rows' => $get['limit'],
                'page' => $get['page'],
            ]);

        foreach ($result as $k => $item) {
            if ($item['root'] == 1) {
                $role = '超级管理员';
            } else {
                $role = $role_column[$item['role_id']] ?? '';
            }
            $result[$k]['role'] = $role;
        }
        return ['count' => $result->total(), 'lists' => $result->getCollection()];
    }


    /**
     * @notes 设置客服状态
     * @param $post
     * @param $shop_id
     * @return Kefu
     * @author 段誉
     * @date 2021/11/26 18:32
     */
    public static function setStatus($post, $shop_id)
    {
        if ($post['disable'] == 1) {
            ChatLogic::setChatDisable($shop_id, $post['id']);
        }
        return (new Kefu())->updateStatus($post['id'], $post['disable'], $shop_id);
    }


    /**
     * @notes 登录工作台
     * @param $id
     * @param $shop_id
     * @return false|string
     * @author 段誉
     * @date 2021/12/20 10:45
     */
    public static function login($id, $shop_id)
    {
        try{
            $kefu = (new ShopAdmin())->alias('a')
                ->field(['k.id', 'k.nickname', 'k.avatar', 'k.shop_id', 'a.account'])
                ->join('kefu k', 'a.id = k.admin_id and a.shop_id = k.shop_id')
                ->where(['k.id' => $id, 'k.del' => 0, 'k.shop_id' => $shop_id])
                ->findOrEmpty()->toArray();

            if(empty($kefu)) {
                throw new \Exception('该客服信息缺失');
            }

            $token = LoginLogic::createSession($kefu['id'], $shop_id, Client_::pc);

            return request()->domain() . '/kefu?token='. $token . '&type=' . KefuEnum::TYPE_SHOP;

        } catch(\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }


}