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

namespace app\shop\validate\goods;


use app\common\basics\Validate;
use app\common\model\goods\Goods;


/**
 * 供货商
 * Class SupplierValidate
 * @package app\admin\validate
 */
class SupplierValidate extends Validate
{

    protected $rule = [
        'id'        => 'require',
        'name'      => 'require|unique:supplier,name&del',
        'contact'   => 'require',
        'mobile'    => 'require|mobile',
        'address'   => 'require',
    ];

    protected $message = [
        'id.require'        => '参数缺失',
        'name.require'      => '参数缺失',
        'name.unique'       => '该名称已被使用',
        'contact.require'   => '请填写联系人',
        'mobile.require'    => '请填写联系电话',
        'mobile.mobile'     => '请填写正确联系电话',
        'address.require'   => '请填写联系地址',
    ];

    protected $scene = [
        'add'  =>  ['name', 'contact', 'mobile', 'address'],
        'edit' =>  ['name', 'contact', 'mobile', 'address'],
    ];

    public function sceneDel()
    {
        return $this->only(['id'])
            ->append('id','checkDel');
    }


    protected function checkDel($value,$rule,$data)
    {
        $check = Goods::where('supplier_id', $value)->find();
        if ($check) {
            return '供货商已经关联商品，无法删除';
        }
        return true;
    }

}