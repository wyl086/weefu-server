<?php
namespace app\shop\controller\distribution;

use app\shop\logic\distribution\RecordLogic;
use app\common\basics\ShopBase;
use app\common\server\JsonServer;
use app\common\utils\Time;

class Record extends ShopBase
{
    public function lists()
    {
        if($this->request->isAjax()) {
            $get = $this->request->get();
            $get['shop_id'] = $this->shop_id;
            $data = RecordLogic::lists($get);
            return JsonServer::success('', $data);
        }
        return view('', [
            'time' => Time::getTime()
        ]);
    }
}