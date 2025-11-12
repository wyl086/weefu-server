<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\api\controller;

use app\admin\logic\WechatMerchantTransferLogic;
use app\common\basics\Api;
use app\common\model\WithdrawApply;


/**
 * 测试
 * Class Test
 * @package app\api\controller
 */
class Test extends Api
{
    public $like_not_need_login = ['test'];

    public function test()
    {
        $withdraw = WithdrawApply::where('id', 111)->findOrEmpty()->toArray();
//        $result = WechatMerchantTransferLogic::transfer($withdraw);
        $result = WechatMerchantTransferLogic::details($withdraw);
        halt($result);

    }
}