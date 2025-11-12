<?php
declare (strict_types = 1);

namespace app\shop\controller;

use app\common\basics\ShopBase;
use think\Request;
use app\shop\logic\SeckillLogic;

class Seckill extends ShopBase
{
    public function lists(){
        //  xxxx/shop/controller/method
        $seckill_time = SeckillLogic::getTimeAll();
        $this->assign('seckill',$seckill_time);
        return $this->fetch();
    }
    /**
     * note 秒杀商品
     * create_time 2020/11/13 16:01
     */
    public function goodsLists(){
        if($this->request->isAjax()){
            $get = $this->request->get();
            $list = SeckillLogic::goodsList($get);
            $this->_success('',$list);
        }
    }
    /**
     * note 秒杀时间
     * create_time 2020/11/13 16:01
     */
    public function timeLists(){
        if($this->request->isAjax()){
            $get= $this->request->get();
            $list = SeckillLogic::timeList($get);
            $this->_success('',$list);
        }
    }
    /**
     * note 添加秒杀时间段
     * create_time 2020/11/13 16:01
     */
    public function addTime(){
        if($this->request->isAjax()){
            $post = $this->request->post();
            $result = $this->validate($post, 'app\admin\validate\SeckillTime');
            if($result === true){
                SeckillLogic::addTime($post);
                $this->_success('新增成功','');
            }
            $this->_error($result,'');


        }
        return $this->fetch();
    }

    /**
     * note 编辑秒杀时间段
     * create_time 2020/11/13 16:02
     */
    public function editTime($id){
        if($this->request->isAjax()){
            $post = $this->request->post();
            $result = $this->validate($post, 'app\admin\validate\SeckillTime');
            if($result === true){
                SeckillLogic::editTime($post);
                $this->_success('编辑成功','');
            }
            $this->_error($result,'');
        }
        $this->assign('detail',SeckillLogic::getTime($id));
        return $this->fetch();
    }

    /**
     * note 删除秒杀时间段
     * create_time 2020/11/13 16:02
     */
    public function delTime(){
        if($this->request->isAjax()){
            $id = $this->request->post('id');
            $result = SeckillLogic::delTime($id);

            if($result == true){
                $this->_success('删除成功','');
            }
            return $this->_error('删除失败','');


        }
    }
    /**
     * note 添加秒杀商品
     * create_time 2020/11/13 16:02
     */
    public function addGoods(){
        if($this->request->isAjax()){
            $post = $this->request->post();
            $post['item'] = form_to_linear($post);
            $result = $this->validate($post,'app\admin\validate\SeckillGoods.add');
            if($result === true){
                $result = SeckillLogic::addGoods($post);
                if($result){
                    $this->_success('新增成功','');
                }
                $result = '新增失败';
            }
            $this->_error($result);

        }

        $seckill_time = SeckillLogic::getTimeAll();
        $this->assign('seckill',$seckill_time);
        return $this->fetch();
    }
    /**
     * note 编辑秒杀商品
     * create_time 2020/11/13 16:02
     */
    public function editGoods(){
        if($this->request->isAjax()){
            $post = $this->request->post();
            $post['item'] = form_to_linear($post);
            $result = $this->validate($post,'app\admin\validate\SeckillGoods.edit');
            if($result === true){
                $result = SeckillLogic::editGoods($post);
                if($result){
                    $this->_success('编辑成功','');
                }
                $result = '编辑失败';
            }
            $this->_error($result);


        }
        $id = $this->request->get('id');
        $seckill_id = $this->request->get('seckill_id');

        $detail = SeckillLogic::getSeckillGoods($id,$seckill_id);
        $seckill_time = SeckillLogic::getTimeAll();
        $this->assign('seckill',$seckill_time);
        $this->assign('detail',$detail);
        return $this->fetch();
    }

    /**
     * note 删除秒杀商品
     * create_time 2020/11/13 16:05
     */
    public function delGoods(){
        if($this->request->isAjax()){
            $id = $this->request->post('id');
            $seckill_id = $this->request->post('seckill_id');
            $result = SeckillLogic::delGoods($id,$seckill_id);

            if($result == true){
                $this->_success('删除成功','');
            }
            return $this->_error('删除失败','');
        }
    }

}
