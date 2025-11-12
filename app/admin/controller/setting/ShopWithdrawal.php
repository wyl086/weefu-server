<?php


namespace app\admin\controller\setting;


use app\common\basics\AdminBase;
use app\common\enum\ShopWithdrawEnum;
use app\common\logic\SettingLogic;
use app\common\server\ConfigServer;
use app\common\server\JsonServer;
use think\facade\View;

class ShopWithdrawal extends AdminBase
{
    /**
     * @Notes: 商家提现配置页
     * @Author: 张无忌
     */
    public function index()
    {
        return view('', [
            'detail'    => SettingLogic::getShopWithdraw(),
            'type_list' => ShopWithdrawEnum::TYPE_TEXT_ARR2,
        ]);
    }

    /**
     * @Notes: 设置商家提现
     * @Author: 张无忌
     */
    public function set()
    {
        $post = $this->request->post();
        
        $withdrawal_type = $post['withdrawal_type'] ?? [];
        if (empty($withdrawal_type)) {
            return JsonServer::error('提现方式至少选择一种');
        }
    
        SettingLogic::setShopWithdraw($post);
        
        return JsonServer::success('设置成功');
    }
}