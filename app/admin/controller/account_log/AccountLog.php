<?php
namespace app\admin\controller\account_log;

use app\common\basics\AdminBase;
use app\admin\logic\account_log\AccountLogLogic;
use app\common\model\AccountLog as AccountLogModel;
use app\common\server\JsonServer;

class AccountLog extends AdminBase
{
    public function growthLists()
    {
        if($this->request->isAjax()) {
            $get = $this->request->get();
            $data = AccountLogLogic::growthLists($get);
            return JsonServer::success('', $data);
        }

        return view('', [
            'typeDescArr' => AccountLogLogic::getTypeDesc(AccountLogModel::growth_change),
            'time' => AccountLogLogic::getTime()
        ]);
    }
}