<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\admin\validate\decoration;
use app\common\basics\Validate;
use think\facade\Db;

class AdValidate extends Validate{
    protected $rule = [
        'id'            => 'require',
        'title'         => 'require|unique:ad,title^del^terminal',
        'pid'           => 'require|checkPid|checkCategory',
        'image'         => 'require',

    ];

    protected $message = [
        'id.require'        => '请选择广告',
        'title.require'     => '请输入广告名称',
        'title.unique'      => '广告名称已存在',
        'pid.require'       => '请选择广告位',
        'image.require'     => '请上传广告图',
    ];


    public function sceneAdd(){
        return $this->remove('id',['require']);
    }

    public function sceneDel(){
        return $this->only(['id']);
    }

    public function sceneSwtich(){
        return $this->only(['id']);
    }

    public function checkPid($value,$rule,$data){
        $ad_position = Db::name('ad_position')
                    ->where(['id'=>$value,'del'=>0])
                    ->find();

        if(empty($ad_position)){
            return '广告位不存在';
        }



        return true;
    }

    public function checkCategory($value,$rule,$data){
        //选择了分类广告位，验证分类
        if(in_array($value,[3,4])){

            if(empty($data['category_id'])){
                return '请选择商品分类';
            }
            $category = Db::name('goods_category')
                ->where(['id'=>$data['category_id'],'del'=>0])
                ->find();

            if(empty($category)){
                return '商品分类不存在';
            }
        }

        return true;
    }



}