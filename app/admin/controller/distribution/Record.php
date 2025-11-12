<?php
namespace app\admin\controller\distribution;

use app\admin\logic\distribution\RecordLogic;
use app\common\basics\AdminBase;
use app\common\server\JsonServer;
use app\common\utils\Time;

class Record extends AdminBase
{
    public function lists()
    {
        if($this->request->isAjax()) {
            $get = $this->request->get();
            $data = RecordLogic::lists($get);
            return JsonServer::success('', $data);
        }
        return view('', [
            'time' => Time::getTime()
        ]);
    }
}