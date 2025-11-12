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


use app\common\basics\Logic;
use app\common\model\Admin;
use app\common\model\Role;

class AdminLogic extends Logic
{

    /**
     * Notes: 列表
     * @param $get
     * @author 段誉(2021/4/10 11:05)
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function lists($get)
    {
        $roleModel = new  Role();
        $adminModel = new Admin();

        $role_column = $roleModel->getNameColumn();

        $where[] = ['del', '=', 0];
        if (isset($get['role_id']) && $get['role_id']) {
            $where[] = ['role_id', '=', $get['role_id']];
        }
        if (isset($get['name']) && $get['name']) {
            $where[] = ['name', 'like', "%{$get['name']}%"];
        }
        if (isset($get['account']) && $get['account']) {
            $where[] = ['account', 'like', "%{$get['account']}%"];
        }

        $result = $adminModel->where($where)
            ->hidden(['password', 'salt'])
            ->paginate([
                'list_rows'=> $get['limit'],
                'page'=> $get['page'],
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
     * Notes: 添加管理员
     * @param $post
     * @author 段誉(2021/4/10 16:14)
     * @return Admin|\think\Model
     */
    public static function addAdmin($post)
    {
        $time = time();
        $salt = substr(md5($time . $post['name']), 0, 4);//随机4位密码盐
        $password = generatePassword($post['password'], $salt);//生成密码
        return Admin::create([
            'name'     => $post['name'],
            'root'     => 0,
            'account'  => $post['account'],
            'password' => $password,
            'salt'     => $salt,
            'role_id'  => $post['role_id'],
            'disable' => $post['disable']
        ]);
    }


    /**
     * Notes: 更新管理员
     * @param $post
     * @author 段誉(2021/4/10 17:11)
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function editAdmin($post)
    {
        $admin = Admin::find($post['id']);

        $data = [
            'name'          => $post['name'],
            'account'       => $post['account'],
            'role_id'       => $post['role_id'],
            'disable'       => $post['disable']
        ];

        //生成密码
        if ($post['password']) {
            $data['password'] = generatePassword($post['password'], $admin['salt']);
        }

        //TODO 禁用管理员并强制下线
        if (1 == $post['disable'] || $admin['role_id'] != $post['role_id']) {

        }

        return $admin->save($data);
    }


    /**
     * Notes: 删除
     * @param $id
     * @author 段誉(2021/4/10 17:30)
     * @return Admin
     */
    public static function delAdmin($id)
    {
        return Admin::update(['account' => time() . '_' . $id, 'del' => 1], ['id' => $id]);
    }


    /**
     * Notes: 修改密码
     * @param $password
     * @param $admin_id
     * @author 段誉(2021/4/30 11:59)
     * @return bool
     */
    public static function updatePassword($password, $admin_id)
    {
        try {
            $admin = Admin::find($admin_id);
            $admin->password = generatePassword($password, $admin['salt']);
            $admin->save();
            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }


}