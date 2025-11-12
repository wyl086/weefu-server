<?php
namespace app\admin\validate\user;

use app\common\model\user\User;
use think\Validate;

class UserValidate extends Validate
{
    protected $rule = [
        'tag_ids' => 'require',
        'user_ids' => 'require|checkIds',
        'nickname' => 'require',
        'avatar' => 'require',
        'mobile' => 'mobile',
        'id' => 'require|checkId',
        'type' => 'require|checkData',
        'integral_remark' => 'max:100',
        'earnings_remark' => 'max:100',
        'money_remark' => 'max:100',
        'growth_remark' => 'max:100',
        'disable'=> 'require|in:0,1'
    ];

    protected $message = [
        'tag_ids.require' => '请选择会员标签',
        'user_ids.require' => '请先选择用户',
        'nickname.require' => '请填写用户昵称',
        'avatar.require' => '请选择用户头像',
        'mobile.mobile' => '手机号码格式错误',
        'id.require' => '请选择要调整的用户',
        'type.require' => '调整类型参数缺失',
        'integral_remark.max' => '备注不能超过100个字符',
        'earnings_remark.max' => '备注不能超过100个字符',
        'money_remark.max' => '备注不能超过100个字符',
        'growth_remark.max' => '备注不能超过100个字符',
        'disable.require'  => '请选择禁用状态',
        'disable.in'       => '禁用状态参数错误',
    ];

    public function sceneSetTag()
    {
        return $this->only(['tag_ids', 'user_ids']);
    }

    public function sceneEdit()
    {
        return $this->only(['id','nickname', 'avatar', 'mobile', 'disable']);
    }

    public function sceneAdjustAccount()
    {
        return $this->only([
             'type','id', 'money_remark', 'growth_remark', 'integral_remark', 'earnings_remark',
        ]);
    }
    
    function checkId($value, $rule, $data)
    {
        if (User::UserIsDelete($value) && (request()->isAjax() || request()->isPost())) {
            return '用户已注销，不能操作';
        }
        
        return true;
    }
    
    function checkIds($ids, $rule, $data)
    {
        foreach ($ids as $id) {
            if (User::UserIsDelete($id) && (request()->isAjax() || request()->isPost())) {
                return '用户已注销，不能操作';
            }
        }
        
        return true;
    }


    /**
     * @notes 验证调整数据
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/3/16 10:49
     */
    protected function checkData($value, $rule, $data)
    {
        $user = User::where(['del' => 0, 'id' => $data['id']])->find();
        if (empty($user)) {
            return '该用户不存在';
        }

        if (!isset($data['money_handle']) && !isset($data['integral_handle'])
            && !isset($data['growth_handle']) && !isset($data['earnings_handle'])) {
            return '请选择调整的类型';
        }

        switch ($value) {
            case 'money':
                $result = $this->checkMoney($data, $user);
                break;
            case 'integral':
                $result = $this->checkIntegral($data, $user);
                break;
            case 'growth':
                $result = $this->checkGrowth($data, $user);
                break;
            case 'earnings':
                $result = $this->checkEarnings($data, $user);
                break;
            default:
                $result = '账户调整类型错误';
        }
        return $result;
    }




    /**
     * @notes 验证金额
     * @param $data
     * @param $user
     * @return bool|string
     * @author 段誉
     * @date 2022/3/16 10:49
     */
    protected function checkMoney($data, $user)
    {
        if (empty($data['money'])) {
            return '请输入大于0的调整余额';
        }
        if ($data['money'] < 0) {
            return '调整余额必须大于零';
        }
        //验证扣减余额操作
        if ($data['money_handle'] == 0) {
            //用户余额不足
            if ($data['money'] > $user['user_money']) {
                return '用户余额仅剩下' . $user['user_money'] . '元';
            }
        }
        if (empty($data['money_remark'])) {
            return '请输入调整余额备注';
        }
        return true;
    }

    /**
     * @notes 验证积分
     * @param $data
     * @param $user
     * @return bool|string
     * @author 段誉
     * @date 2022/3/16 10:49
     */
    protected function checkIntegral($data, $user)
    {
        if (empty($data['integral'])) {
            return '请输入大于0的调整积分';
        }
        if ($data['integral'] < 0) {
            return '调整积分必须大于零';
        }
        //验证扣减积分操作
        if ($data['integral_handle'] == 0) {
            //用户积分不足
            if ($data['integral'] > $user['user_integral']) {
                return '用户积分仅剩下' . $user['user_integral'] . '分';
            }
        }

        if (empty($data['integral_remark'])) {
            return '请输入调整积分备注';
        }
        return true;
    }

    /**
     * @notes 验证成长值
     * @param $data
     * @param $user
     * @return bool|string
     * @author 段誉
     * @date 2022/3/16 10:50
     */
    protected function checkGrowth($data, $user)
    {
        if (empty($data['growth'])) {
            return '请输入大于0的调整成长值';
        }
        if ($data['growth'] < 0) {
            return '成长值必须大于零';
        }
        //验证扣减成长值操作
        if ($data['growth_handle'] == 0) {
            //用户成长值不足
            if ($data['growth'] > $user['user_growth']) {
                return '用户成长值仅剩下' . $user['user_growth'];
            }
        }
        if (empty($data['growth_remark'])) {
            return '请输入调整成长值备注';
        }
        return true;
    }

    /**
     * @notes 验证佣金
     * @param $data
     * @param $user
     * @return bool|string
     * @author 段誉
     * @date 2022/3/16 10:50
     */
    protected function checkEarnings($data, $user)
    {
        if (empty($data['earnings'])) {
            return '请输入大于0的调整佣金';
        }
        if ($data['earnings'] < 0) {
            return '调整佣金必须大于零';
        }
        if (empty($user['earnings'])) {
            $user['earnings'] = 0;
        }
        //验证扣减余额操作
        if ($data['earnings_handle'] == 0) {
            if ($data['earnings'] > $user['earnings']) {
                return '用户佣金仅剩下' . $user['earnings'] . '元';
            }
        }
        if (empty($data['earnings_remark'])) {
            return '请输入调整佣金备注';
        }
        return true;
    }
}