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

/**
 * 管理员验证
 * Class AdminValidate
 * @package app\admin\validate
 */
class AdminValidate extends Validate
{

    protected $rule = [
        'account' => 'require|unique:admin|length:1,32',
        'password' => 'require|length:6,32｜confirm:re_password|edit',
        're_password' => 'confirm:password',
        'name' => 'require|length:1,16',
        'role_id' => 'require',
    ];

    protected $message = [
        'account.require' => '账号不能为空',
        'account.unique' => '账号名已存在，请使用其他账号名',
        'account.length' => '账号名的长度为1到32位之间',
        'password.require' => '密码不能为空',
        'password.length' => '密码长度必须为6到16位之间',
        'password.confirm' => '两次密码输入不一致',
        're_password.confirm' => '两次密码输入不一致',
        'name.require' => '名称不能为空',
        'name.length' => '账号名的长度为1到32位之间',
        'role_id.require' => '请选择角色',
    ];


    /**
     * Notes: 场景 - 添加
     * @author 段誉(2021/4/10 16:07)
     */
    public function sceneAdd()
    {
        $this->remove('password',['edit']);
    }

    /**
     * Notes: 场景 - 编辑
     * @author 段誉(2021/4/10 16:07)
     */
    public function sceneEdit()
    {
        $this->remove('password', ['require', 'password']);
    }

    /**
     * Notes: 编辑的时候自定义验证方法
     * @param $password
     * @param $other
     * @param $data
     * @author 段誉(2021/4/10 16:06)
     * @return bool|mixed
     */
    protected function edit($password, $other, $data)
    {
        //不填写验证
        if (empty($password) && empty($data['re_password'])) {
            return true;
        }

        //填写的时候验证
        $password_length = strlen($password);
        if ($password_length < 6 || $password_length > 16) {
            return $this->message['password.length'];
        }
        return true;
    }

}