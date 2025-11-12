<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\admin\logic\kefu;

use app\common\basics\Logic;
use app\common\logic\ChatLogic;
use app\common\model\Admin;
use app\common\model\Client_;
use app\common\model\kefu\Kefu;
use app\common\model\Role;
use app\common\server\UrlServer;
use app\kefuapi\logic\LoginLogic;

/**
 * 客服逻辑
 * Class KefuLogic
 * @package app\admin\logic\index
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
    public static function getLists($get)
    {
        $result = (new Kefu())->alias('k')
            ->field("k.*,a.account")
            ->join('admin a', 'a.id = k.admin_id')
            ->where(['a.del' => 0, 'k.del' => 0, 'shop_id' => 0])
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
     * @return Kefu|false|\think\Model
     * @author 段誉
     * @date 2021/11/27 10:43
     */
    public static function add($post)
    {
        try {
            return (new Kefu())->insertKefu($post);
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 编辑客服
     * @param $post
     * @return Kefu|false
     * @author 段誉
     * @date 2021/11/27 10:44
     */
    public static function edit($post)
    {
        try {

            if ($post['disable'] == 1) {
                ChatLogic::setChatDisable(0, $post['id']);
            }

            return (new Kefu())->updateKefu($post['id'], $post);

        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 详情
     * @param $id
     * @return mixed
     * @author 段誉
     * @date 2021/11/27 10:44
     */
    public static function detail($id)
    {
        $detail = (new Kefu())->alias('k')
            ->field("k.*, a.account, a.name")
            ->join('admin a', 'a.id = k.admin_id')
            ->where(['k.id' => $id, 'k.shop_id' => 0])
            ->findOrEmpty();

        $detail['avatar'] = !empty($detail['avatar']) ? UrlServer::getFileUrl($detail['avatar']) : '';
       return $detail;
    }


    /**
     * @notes 删除客服
     * @param $post
     * @return Kefu
     * @author 段誉
     * @date 2021/11/27 10:48
     */
    public static function del($post)
    {
        return (new Kefu())->delKefu($post['id']);
    }


    /**
     * @notes 管理员列表
     * @param $get
     * @return array
     * @throws \think\db\exception\DbException
     * @author 段誉
     * @date 2021/11/26 18:00
     */
    public static function getAdminLists($get)
    {
        // 角色名称
        $role_column = (new Role())->getNameColumn();

        // 已有客服列表
        $kefu = (new Kefu())->where(['del' => 0, 'shop_id' => 0])->column("admin_id");

        // 查询条件
        $where[] = ['del', '=', 0];
        $where[] = ['id', 'not in', $kefu];
        if (isset($get['role_id']) && $get['role_id'] != '') {
            $where[] = ['role_id', '=', $get['role_id']];
        }
        if (isset($get['name']) && $get['name'] != '') {
            $where[] = ['name', 'like', "%{$get['name']}%"];
        }

        $result = (new Admin())->where($where)
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
     * @return Kefu
     * @author 段誉
     * @date 2021/11/26 18:32
     */
    public static function setStatus($post)
    {
        if ($post['disable'] == 1) {
            ChatLogic::setChatDisable(0, $post['id']);
        }
        return (new Kefu())->updateStatus($post['id'], $post['disable']);
    }


    /**
     * @notes 返回登录链接
     * @param $id
     * @return bool|string
     * @author 段誉
     * @date 2021/12/15 19:52
     */
    public static function login($id)
    {
        try{
            $kefu = (new Admin())->alias('a')
                ->field(['k.id', 'k.nickname', 'k.avatar', 'k.shop_id', 'a.account'])
                ->join('kefu k', 'a.id = k.admin_id')
                ->where(['k.id' => $id, 'k.shop_id' => 0, 'k.del' => 0])
                ->findOrEmpty()->toArray();

            if(empty($kefu)) {
                throw new \Exception('该客服信息缺失');
            }

            $token = LoginLogic::createSession($kefu['id'], $kefu['shop_id'], Client_::pc);

            return request()->domain() . '/kefu?token='. $token;

        } catch(\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

}