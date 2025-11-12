<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\admin\validate;


use app\common\basics\Validate;
use app\common\model\Admin;

class RoleValidate extends Validate
{

    protected $rule = [
        'id' => 'adminExistRole',
        'name' => 'require',
        'auth_ids' => 'array'
    ];

    protected $message = [
        'name.require' => '角色名不能为空',
        'auth_ids.auth' => '权限错误',
        'id.adminExistRole' => '管理员列表存在该角色，无法删除',
    ];

    protected function sceneAdd()
    {
        $this->remove('id', ['adminExistRole']);
    }

    protected function sceneEdit()
    {
        $this->remove('id', ['adminExistRole']);
    }

    public function sceneDel()
    {
        $this->remove('name', ['require'])
            ->remove('auth_ids', ['auth']);
    }




    /**
     * 判断管理列表是否存在该角色
     * @param $id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function adminExistRole($value, $rule, $data)
    {
        $result = Admin::where(['role_id' => $value, 'del' => 0])->find();
        if ($result) {
            return false;
        }
        return true;
    }
}