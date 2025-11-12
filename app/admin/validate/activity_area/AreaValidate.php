<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\admin\validate\activity_area;
use app\common\basics\Validate;
use think\facade\Db;

class AreaValidate extends Validate{
    protected $rule = [
        'id'        => 'require',
        'name'      => 'require|unique:activity_area,name^del',
        'synopsis'  => 'require',
        'image'     => 'require',
     ];

    protected $message = [
        'name.require'      => '请输入专区名称',
        'name.unique'       => '专区名称重复',
        'image.require'       => '封面图不能为空',
        'synopsis.require'  => '请专区简介',
    ];
    protected $scene = [
        'add' => ['name','synopsis','image'],
        'edit' => [['id','checkArea'],'name','synopsis','image'],
        'del' => ['id']
    ];
    //验证活动专区(多商户)
//    public function checkArea($value,$rule,$data){
//
//        $goods = Db::name('activity_area_goods')
//                ->where(['del'=>0,'activity_area_id'=>$value])
//                ->find();
//        halt($goods);
//        if($goods){
//            return '该活动专区已被使用，无法删除';
//        }
//        return true;
//
//    }
}
