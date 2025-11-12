<?php
namespace app\admin\controller\seckill;

use app\admin\logic\seckill\SeckillTimeLogic;
use app\common\basics\AdminBase;
use app\common\server\JsonServer;
use think\exception\ValidateException;
use app\admin\validate\seckill\SeckillTimeValidate;

class SeckillTime extends AdminBase
{
    public function lists()
    {
        return view();
    }

    public function addTime()
    {
        if($this->request->isAjax()) {
            $post = $this->request->post();
            try{
                validate(SeckillTimeValidate::class)->check($post);
            }catch(ValidateException $e) {
                return JsonServer::error($e->getError());
            }
            $result = SeckillTimeLogic::addTime($post);
            if($result === true) {
                return JsonServer::success('新增成功');
            }
            return JsonServer::error(SeckillTimeLogic::getError());
        }
        return view();
    }

    public function timeLists(){
        if($this->request->isAjax()){
            $get= $this->request->get();
            $list = SeckillTimeLogic::timeList($get);
            return JsonServer::success('', $list);
        }
    }

    public function editTime(){
        if($this->request->isAjax()){
            $post = $this->request->post();
            try{
                validate(SeckillTimeValidate::class)->check($post);
            }catch(ValidateException $e) {
                return JsonServer::error($e->getError());
            }
            $result = SeckillTimeLogic::editTime($post);
            if($result === true) {
                return JsonServer::success('编辑成功');
            }
            return JsonServer::error(SeckillTimeLogic::getError());
        }

        $id = $this->request->get('id', '', 'intval');
        return view('', [
            'detail' => SeckillTimeLogic::getTime($id)
        ]);
    }

    public function delTime(){
        if($this->request->isAjax()){
            $id = $this->request->post('id');
            $result = SeckillTimeLogic::delTime($id);

            if($result === true) {
                return JsonServer::success('删除成功');
            }
            return JsonServer::error(SeckillTimeLogic::getError());
        }
    }
}