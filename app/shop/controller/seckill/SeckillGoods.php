<?php
namespace app\shop\controller\seckill;

use app\common\basics\ShopBase;
use app\common\server\JsonServer;
use app\shop\logic\seckill\SeckillGoodsLogic;
use think\exception\ValidateException;
use app\shop\validate\SeckillGoodsValidate;

class SeckillGoods extends ShopBase
{
    public function lists()
    {
        $statistics = SeckillGoodsLogic::statistics($this->shop_id);
        $seckill_time = SeckillGoodsLogic::getTimeAll();
        return view('', [
            'statistics' => $statistics,
            'seckill_time' => $seckill_time
        ]);
    }

    public function addGoods(){
        if($this->request->isAjax()){
            $post = $this->request->post();
            $post['item'] = form_to_linear($post);
            $post['shop_id'] = $this->shop_id;
            try{
                validate(SeckillGoodsValidate::class)->scene('add')->check($post);
            }catch(ValidateException $e) {
                return JsonServer::error($e->getError());
            }
            $result = SeckillGoodsLogic::addGoods($post);
            if($result === true) {
                return JsonServer::success('新增成功');
            }
            return JsonServer::error(SeckillGoodsLogic::getError());
        }

        $seckill_time = SeckillGoodsLogic::getTimeAll();
        return view('', [
            'seckill' => $seckill_time
        ]);
    }

    public function goodsLists(){
        if($this->request->isAjax()){
            $get = $this->request->get();
            $get['shop_id'] = $this->shop_id;
            $list = SeckillGoodsLogic::goodsList($get);
            return JsonServer::success('', $list);
        }
    }

    public function editGoods(){
        if($this->request->isAjax()){
            $post = $this->request->post();
            $post['item'] = form_to_linear($post);
            $post['shop_id'] = $this->shop_id;
            try{
                validate(SeckillGoodsValidate::class)->scene('edit')->check($post);
            }catch(ValidateException $e) {
                return JsonServer::error($e->getError());
            }
            $result = SeckillGoodsLogic::editGoods($post);
            if($result === true) {
                return JsonServer::success('编辑成功');
            }
            return JsonServer::error(SeckillGoodsLogic::getError());
        }


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

    public function delGoods(){
        if($this->request->isAjax()){
            $id = $this->request->post('id');
            $seckill_id = $this->request->post('seckill_id');
            $start_date = $this->request->post('start_date');
            $end_date = $this->request->post('end_date');
            $shop_id = $this->shop_id;

            $result = SeckillGoodsLogic::delGoods($id,$seckill_id,$start_date,$end_date,$shop_id);
            if($result === true) {
                return JsonServer::success('删除成功');
            }
            return JsonServer::error(SeckillGoodsLogic::getError());
        }
    }

    /**
     * @notes 获取统计数据
     * @return \think\response\Json
     * @author Tab
     * @date 2021/7/29 10:00
     */
    public function totalCount()
    {
        if ($this->request->isAjax()) {
            return JsonServer::success('获取成功', SeckillGoodsLogic::statistics($this->shop_id));
        }
    }
}