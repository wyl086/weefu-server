<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | multshop团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshopTeam
// +----------------------------------------------------------------------
namespace app\api\validate;
use app\common\basics\Validate;
use app\common\model\bargain\BargainLaunch;
use app\common\model\goods\Goods;

/**
 * 二维码验证器
 * Class MakeMnQrcode
 * @package app\api\validate
 */
class MakeMnQrcode extends Validate
{
    protected $rule = [
        'url'       =>  'require',
        'type'      =>  'require|in:0,1,2|checkId',
    ];

    protected $message = [
        'url.require'       => 'url不能为空',
        'type.require'      => '类型不能为空',
        'type.in'           => '类型错误',
    ];

    //验证商品、活动
    public function checkId($value,$rule,$data){
        $type = $data['type'] ?? 0;
        if(0 != $type && empty($data['id'])){
            return '缺少id';
        }

        if(1 == $type){
            $goods = Goods::where(['id'=>$data['id']])
                    ->find();
            if(empty($goods)){
                return '商品不存在';
            }
        }
        if(2 == $type){

            $bargain_launch = new BargainLaunch();
            $bargain_launch = $bargain_launch
                ->where(['id'=>$data['id']])
                ->find();

            if(empty($bargain_launch) || $bargain_launch['launch_end_time'] <= time()){
                return '该砍价已结束';
            }
        }
        return true;


    }
}