<?php
namespace app\admin\controller\coupon;

use app\common\basics\AdminBase;
use app\admin\logic\coupon\ShopCouponLogic;
use app\common\server\JsonServer;

class ShopCoupon extends AdminBase
{
    public function lists(){
        if($this->request->isAjax()){
            $get = $this->request->get();
            $data = ShopCouponLogic::lists($get);
            return JsonServer::success('', $data);
        }
        return view();
    }

    public function detail(){
        $id = $this->request->get('id');
        $detail = ShopCouponLogic::getCoupon($id,true);
        return view('', [
            'detail' => json_encode($detail,JSON_UNESCAPED_UNICODE)
        ]);
    }

    public function record()
    {
        if($this->request->isAjax()){
            $get = $this->request->get();
            $data = ShopCouponLogic::record($get);
            return JsonServer::success('', $data);
        }

        $id = $this->request->get('id');
        return view('', [
            'id' => $id
        ]);
    }
}
