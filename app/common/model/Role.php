<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\common\model;


use app\common\basics\Models;

/**
 * 角色 模型
 * Class Menu
 * @package app\common\model
 */
class Role extends Models
{
    protected $name = 'role';

    /**
     * Notes: 获取角色名称
     * @param $role_id
     * @author 段誉(2021/4/9 15:40)
     * @return mixed|string
     */
    public function getRoleName($role_id)
    {
        $role_name = $this
            ->where(['id' => $role_id])
            ->value('name');
        
        return empty($role_name) ? '系统管理员' : $role_name;
    }


    /**
     * Notes: 获取全部角色名称(以角色id为键,值为名称)
     * @param array $contidion
     * @author 段誉(2021/4/10 10:46)
     * @return array
     */
    public function getNameColumn($contidion = [])
    {
        $role_name = $this
            ->where($contidion)
            ->where('del', 0)
            ->column('name', 'id');
        return $role_name;
    }


    /**
     * Notes:
     * @param array $where
     * @param string $field
     * @author 段誉(2021/4/10 11:13)
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getRoleLists($where = [], $field = "*")
    {
        $where[] = ['del', '=', 0];
        return $this->where($where)->field($field)->select();
    }

}