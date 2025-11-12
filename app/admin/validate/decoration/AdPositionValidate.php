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

class AdPositionValidate extends Validate{
    protected $rule = [
        'id'        => 'require|checkAd',
        'name'      => 'require|unique:ad_position,name^del',
    ];

    protected $message = [
        'id.require'        => '请选择广告位',
        'unique.require'    => '请输入广告位名称',
        'name.unique'       => '广告位名称已存在',
    ];

    public function sceneAdd()
    {
        return $this->remove('id', ['require','checkAd']);
    }

    public function sceneEdit()
    {
        return $this->remove('id', ['checkAd']);
    }

    public function sceneDel(){
        return $this->only(['id']);
    }


    public function sceneSwtich(){
        return $this->only(['id'])
                    ->remove('id',['checkAd']);
    }

    public function checkAd($value,$rule,$data){
        $ad_position = Db::name('ad_position')
                ->where(['id'=>$value,'del'=>0])
                ->find();

        if(empty($ad_position)) {
            return '广告位已被删除';
        }

        if(0 == $ad_position['attr']){
            return '系统默认广告位不允许删除';
        }

        $ad = Db::name('ad')
                ->where(['pid'=>$value,'del'=>0])
                ->find();

        if($ad){
            return '该广告位已被使用';
        }
        return true;
    }
}