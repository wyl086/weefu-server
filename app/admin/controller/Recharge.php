<?php
namespace app\admin\controller;

use app\common\basics\AdminBase;
use app\admin\logic\RechargeLogic;
use app\common\server\JsonServer;
use app\admin\validate\RechargeTemplateValidate;
use think\exception\ValidateException;

class Recharge extends AdminBase
{
    public function lists(){
        if($this->request->isAjax()){
            $get = $this->request->get();
            if($get['type'] == 1){ // 充值方案
                $list = RechargeLogic::templatelists($get['type']);
            }else{ // 充值配置
                $list = RechargeLogic::getRechargeConfig();
            }
            return JsonServer::success('', $list);

        }
        return view();
    }

    public function add(){

        if ($this->request->isAjax()){
            $post = $this->request->post();
            try{
                validate(RechargeTemplateValidate::class)->check($post);
            }catch(ValidateException $e) {
                return JsonServer::error($e->getError());
            }
            $result = RechargeLogic::add($post);
            if ($result === true){
                return JsonServer::success('新增成功');
            }
            return JsonServer::error(RechargeLogic::getError());

        }
        return view();
    }

    public function changeFields(){
        $table = 'recharge_template';

        $pk_name = 'id';
        $pk_value = $this->request->get('id');

        $field = $this->request->get('field');
        $field_value = $this->request->get('value');
        $result = RechargeLogic::changeTableValue($table,$pk_name,$pk_value,$field,$field_value);
        if($result){
            return JsonServer::success('修改成功');
        }
        return JsonServer::error('修改失败');
    }

    public function edit($id){
        if ($this->request->isAjax()){
            $post = $this->request->post();
            try{
                validate(RechargeTemplateValidate::class)->check($post);
            }catch(ValidateException $e) {
                return JsonServer::error($e->getError());
            }
            $result = RechargeLogic::edit($post);
            if ($result === true){
                return JsonServer::success('编辑成功');
            }
            return JsonServer::error('编辑失败');
        }

        $info = RechargeLogic::getRechargeTemplate($id);
        return view('', [
            'info' => $info
        ]);
    }

    public function del($id)
    {
        if ($this->request->isAjax()) {
            $result = RechargeLogic::del($id);
            if ($result) {
                return JsonServer::success('删除成功');
            }
            return JsonServer::error('删除失败');
        }
    }

    public function setRecharge(){
        if($this->request->isAjax()){
            $post = $this->request->post();
            if($post['give_growth'] < 0) {
                return JsonServer::error('赠送成长值不能小于0');
            }
            if($post['min_money'] < 0) {
                return JsonServer::error('最低充值金额不能小于0');
            }
            RechargeLogic::setRecharge($post);
            return JsonServer::success('设置成功');
        }
    }
}
