<?php


namespace app\shop\controller\finance;


use app\common\basics\ShopBase;
use app\common\enum\ShopWithdrawEnum;
use app\common\logic\SettingLogic;
use app\common\model\shop\Shop;
use app\common\server\ConfigServer;
use app\common\server\JsonServer;
use app\shop\logic\AlipayLogic;
use app\shop\logic\BankLogic;
use app\shop\logic\finance\WithdrawalLogic;
use think\facade\View;

class Withdrawal extends ShopBase
{
    /**
     * @Notes: 提现列表
     * @Author: 张无忌
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $lists = WithdrawalLogic::lists($get, $this->shop_id);
            return JsonServer::success('获取成功', $lists);
        }

        View::assign('statistics', WithdrawalLogic::statistics($this->shop_id));
        
        return view();
    }

    /**
     * @Notes: 选项卡数据统计
     * @Author: 张无忌
     */
    public function statistics()
    {
        if ($this->request->isAjax()) {
            $statistics = WithdrawalLogic::statistics($this->shop_id);
            return JsonServer::success('获取成功', $statistics);
        }
        return JsonServer::error('异常');
    }

    /**
     * @Notes: 申请提现
     * @Author: 张无忌
     */
    public function add()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $res = WithdrawalLogic::add($post, $this->shop_id);
            if ($res === false) {
                $error = BankLogic::getError() ?: '申请失败';
                return JsonServer::error($error);
            }
            return JsonServer::success('申请成功');
        }
    
        $shopWithdrawConfig = SettingLogic::getShopWithdraw();
        
        return view('', [
            'withdrawal_service_charge' => $shopWithdrawConfig['withdrawal_service_charge'],
            'shop'                      => (new Shop())->findOrEmpty($this->shop_id)->toArray(),
            'types'                     => ShopWithdrawEnum::type_text_arr3($shopWithdrawConfig['withdrawal_type']),
        ]);
    }
    
    /**
     * @notes 申请提现 账号选择
     * @return \think\response\Json|void
     * @author lbzy
     * @datetime 2023-06-09 19:00:31
     */
    function add_accounts()
    {
        $type = input('type/d', -999);
        
        if ($type == ShopWithdrawEnum::TYPE_BANK) {
            return JsonServer::success('', BankLogic::getBankByShopId($this->shop_id));
        }
    
        if ($type == ShopWithdrawEnum::TYPE_ALIPAY) {
            return JsonServer::success('', AlipayLogic::getAlipayByShopId($this->shop_id));
        }
    }

    /**
     * @Notes: 申请详细
     * @Author: 张无忌
     */
    public function detail()
    {
        $id = $this->request->get('id');
        View::assign('detail', WithdrawalLogic::detail($id, $this->shop_id));
        return view();
    }


    /**
     * @notes 导出Excel
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/4/24 11:57
     */
    public function export()
    {
        $params = $this->request->get();
        $result = WithdrawalLogic::lists($params, $this->shop_id, true);
        if(false === $result) {
            return JsonServer::error(WithdrawalLogic::getError() ?: '导出失败');
        }
        return JsonServer::success('', $result);
    }
}