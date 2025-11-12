<?php
namespace app\admin\controller\seckill;

use app\common\basics\AdminBase;
use app\common\server\JsonServer;
use app\admin\logic\seckill\SeckillGoodsLogic;

class SeckillGoods extends AdminBase
{
    public function lists()
    {
        $statistics = SeckillGoodsLogic::statistics();
        $seckill_time = SeckillGoodsLogic::getTimeAll();
        return view('', [
            'statistics' => $statistics,
            'seckill_time' => $seckill_time
        ]);
    }


    public function goodsLists(){
        if($this->request->isAjax()){
            $get = $this->request->get();
            $list = SeckillGoodsLogic::goodsList($get);
            return JsonServer::success('', $list);
        }
    }

    public function editGoods(){
        $id = $this->request->get('id');
        $seckill_id = $this->request->get('seckill_id');
        $start_date = $this->request->get('start_date');
        $end_date = $this->request->get('end_date');

        $detail = SeckillGoodsLogic::getSeckillGoods($id,$seckill_id,$start_date,$end_date);
        $seckill_time = SeckillGoodsLogic::getTimeAll();

        return view('', [
            'seckill' => $seckill_time,
            'detail' => $detail
        ]);
    }

    /**
     * 违规重审
     */
    public function reAudit()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $result = SeckillGoodsLogic::reAudit($post);
            if ($result === true) {
                return JsonServer::success('操作成功');
            }
            return JsonServer::error(SeckillGoodsLogic::getError());
        }

        $get = $this->request->get();
        return view('re_audit', [
            'get' => $get
        ]);
    }

    /**
     * 审核
     */
    public function audit()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $result = SeckillGoodsLogic::audit($post);
            if ($result) {
                return JsonServer::success('操作成功');
            }
            return JsonServer::error(SeckillGoodsLogic::getError());
        }
        $get = $this->request->get();
        return view('audit', [
            'get' => $get
        ]);
    }
}