<?php

namespace app\admin\validate\kefu;

use app\common\basics\Validate;
use app\common\model\kefu\Kefu;

/**
 * 客服验证逻辑
 * Class KefuValidate
 * @package app\admin\validate\content
 */
class KefuValidate extends Validate
{
    protected $rule = [
        'id' => 'require|number',
        'admin_id' => 'require|number|checkIsKefu',
        'avatar' => 'require',
        'nickname' => 'require',
        'disable' => 'require|in:0,1',
        'sort' => 'gt:0'
    ];

    protected $message = [
        'id.require' => 'id不可为空',
        'id.number' => 'id必须为数字',
        'admin_id.require' => '请选择管理员',
        'admin_id.number' => '管理员选择异常',
        'avatar.require' => '请选择头像',
        'nickname.require' => '请填写客服昵称',
        'disable.require' => '请选择状态',
        'disable.in' => '状态错误',
        'sort.gt' => '排序需大于0',
    ];


    public function sceneAdd()
    {
        $this->remove('id', true);
    }

    public function sceneEdit()
    {
        $this->remove('admin_id',true);
    }

    public function sceneDel()
    {
        $this->only(['id'])->append('id', 'checkIsDel');
    }


    /**
     * @notes 选中管理员是否已为客服
     * @param $value
     * @param $rule
     * @param array $data
     * @return bool|string
     * @author 段誉
     * @date 2021/11/26 18:56
     */
    protected function checkIsKefu($value, $rule, $data = [])
    {
        $check = Kefu::where([
            'admin_id' => $value,
            'shop_id' => 0,
            'del' => 0
        ])->findOrEmpty();

        if (!$check->isEmpty()) {
            return "该管理员已是客服";
        }

        return true;
    }


    /**
     * @notes 客服是否存在
     * @param $value
     * @param $rule
     * @param array $data
     * @return bool|string
     * @author 段誉
     * @date 2021/11/26 18:57
     */
    protected function checkIsDel($value, $rule, $data = [])
    {
        $check = Kefu::where(['id' => $value, 'del' => 0])->findOrEmpty();

        if ($check->isEmpty()) {
            return "该客服数据错误";
        }

        return true;
    }

}