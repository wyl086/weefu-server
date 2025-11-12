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


namespace app\shop\logic;


use app\common\basics\Logic;
use app\common\model\shop\ShopAuth;
use app\common\model\shop\ShopRole;
use app\common\model\shop\ShopRoleAuthIndex;
use think\facade\Db;

class RoleLogic extends Logic
{

    /**
     * Notes: 角色列表
     * @author 段誉(2021/4/13 10:35)
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function lists($shop_id, $get)
    {
        $relationModel = new ShopRoleAuthIndex();
        $result = $relationModel->alias('r')
            ->join('dev_shop_auth m', 'r.menu_auth_id=m.id')
            ->where(['m.del' => 0])
            ->order(['sort' => 'desc'])
            ->field(['m.name' => 'name', 'r.role_id' => 'role_id'])
            ->select();
        $role_id_menu_auth_names = [];

        foreach ($result as $k => $v) {
            if (isset($role_id_menu_auth_names[$v['role_id']])) {
                $role_id_menu_auth_names[$v['role_id']] .= $v['name'] . ',';
            } else {
                $role_id_menu_auth_names[$v['role_id']] = $v['name'] . ',';
            }
        }

        $lists = ShopRole::where(['del' => 0, 'shop_id' => $shop_id])
            ->paginate([
                'list_rows'=> $get['limit'],
                'page'=> $get['page']
            ]);

        foreach ($lists as $k => $v) {
            $lists[$k]['auth_str'] = isset($role_id_menu_auth_names[$v['id']]) ? $role_id_menu_auth_names[$v['id']] : '';
            $lists[$k]['auth_str'] = rtrim($lists[$k]['auth_str'], ',');
        }

        return ['lists' => $lists->getCollection(), 'count' => $lists->total()];
    }

    /**
     * Notes: 详情
     * @param $role_id
     * @author 段誉(2021/4/13 10:35)
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function roleInfo($role_id)
    {
        return ShopRole::where(['id' => $role_id])->field(['id', 'name', 'desc'])->find();
    }


    /**
     * Notes: 添加
     * @param $post
     * @author 段誉(2021/4/13 10:35)
     * @return bool
     */
    public static function addRole($shop_id, $post)
    {
        $data = [
            'name' => $post['name'],
            'desc' => $post['desc'],
            'shop_id' => $shop_id,
            'create_time' => time(),
        ];

        try {
            Db::startTrans();

            $roleModel = new ShopRole();
            $roleAuthIndexModel = new ShopRoleAuthIndex();

            $role_id = $roleModel->insertGetId($data);

            $data = [];
            $post['auth_ids'] = empty($post['auth_ids'])?[]:$post['auth_ids'];
            foreach ($post['auth_ids'] as $k => $v) {
                $data[] = [
                    'role_id' => $role_id,
                    'menu_auth_id' => $v,
                ];
            }
            $roleAuthIndexModel->insertAll($data);
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * Notes: 编辑
     * @param $post
     * @author 段誉(2021/4/13 10:36)
     * @return bool
     */
    public static function editRole($shop_id, $post)
    {
        $data = [
            'name' => $post['name'],
            'desc' => $post['desc'],
            'update_time' => time(),
        ];
        try {
            Db::startTrans();

            $roleModel = new ShopRole();
            $roleAuthIndexModel = new ShopRoleAuthIndex();

            $roleModel->where(['del' => 0, 'id' => $post['id'], 'shop_id' => $shop_id])->update($data);
            $roleAuthIndexModel->where(['role_id' => $post['id']])->delete();

            $data = [];
            $post['auth_ids'] = empty($post['auth_ids'])?[]:$post['auth_ids'];
            foreach ($post['auth_ids'] as $k => $v) {
                $data[] = [
                    'role_id' => $post['id'],
                    'menu_auth_id' => $v,
                ];
            }
            $roleAuthIndexModel->insertAll($data);

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * Notes: 删除
     * @param $role_id
     * @author 段誉(2021/4/13 10:36)
     * @return ShopRole
     */
    public static function delRole($shop_id, $role_id)
    {
        return ShopRole::where([
            'del' => 0,
            'id' => $role_id,
            'shop_id' => $shop_id
        ])->update(['del' => 1, 'update_time' => time()]);
    }


    /**
     * Notes: 获取菜单权限树
     * @param string $role_id
     * @author 段誉(2021/4/13 10:36)
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function authTree($role_id = '')
    {
        $lists = ShopAuth::where(['disable' => 0, 'del' => 0])->select();
        $pids = ShopAuth::where(['disable' => 0, 'type' => 1, 'del' => 0])->column('pid');

        foreach ($lists as $k => $v) {
            $lists[$k]['spread'] = in_array($v['id'], $pids) ? true : false;
        }

        $menu_auth_ids = [];
        if ($role_id) {
            $menu_auth_ids = ShopRoleAuthIndex::where(['role_id' => $role_id])
                ->column('menu_auth_id');
        }

        return self::authListToTree($lists, 0, $menu_auth_ids);
    }


    /**
     * Notes: 列表结构转换成树形结构
     * @param $lists
     * @param int $pid
     * @param array $menu_auth_ids
     * @author 段誉(2021/4/13 10:36)
     * @return array
     */
    public static function authListToTree($lists, $pid = 0, $menu_auth_ids = [])
    {
        $tree = [];
        foreach ($lists as $k => $v) {
            if ($v['pid'] == $pid) {
                $temp['id'] = $v['id'];
                $temp['field'] = 'auth_ids[' . $v['id'] . ']';
                $temp['title'] = $v['name'];
                $temp['children'] = self::authListToTree($lists, $v['id'], $menu_auth_ids);
                $temp['checked'] = in_array($v['id'], $menu_auth_ids) && empty($temp['children']) ? true : false;
                $temp['spread'] = $v['spread'];
                $tree[] = $temp;
            }
        }
        return $tree;
    }

}