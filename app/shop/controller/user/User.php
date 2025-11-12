<?php
namespace app\shop\controller\user;

use app\common\basics\ShopBase;
use app\common\enum\ClientEnum;
use app\common\server\JsonServer;
use app\shop\logic\user\UserLogic;

class User extends ShopBase
{
    public function lists()
    {
        if($this->request->isAjax()) {
            $get = $this->request->get();
            $get['shop_id'] = $this->shop_id;
            $data = UserLogic::lists($get);
            return JsonServer::success('', $data);
        }

        return view('', [
            'client_list' => ClientEnum::getClient(true)
        ]);
    }

    public function info()
    {
        $id = $this->request->get('id', '', 'intval');
        $detail = UserLogic::getInfo($id);
        return view('', [
            'detail' => $detail
        ]);
    }
}