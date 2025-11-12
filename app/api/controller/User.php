<?php
namespace app\api\controller;

use app\common\basics\Api;
use app\api\logic\UserLogic;
use app\common\enum\NoticeEnum;
use app\common\server\JsonServer;
use app\api\validate\UpdateUserValidate;
use app\api\validate\SetWechatUserValidate;
use app\api\validate\WechatMobileValidate;
use app\api\validate\ChangeMobileValidate;
use think\exception\ValidateException;

class  User extends Api
{
    /***
     * 个人中心
     */
    public function center()
    {
        $config = UserLogic::center($this->user_id);
        return JsonServer::success('', $config);
    }

    /**
     * 用户信息
     */
    public function info()
    {
        return JsonServer::success('', UserLogic::getUserInfo($this->user_id));
    }

    /**
     * Notes:设置用户信息
     */
    public function setInfo()
    {
        try{
            $post = $this->request->post();
            $post['user_id'] = $this->user_id;
            validate(UpdateUserValidate::class)->scene('set')->check($post);
        }catch(ValidateException $e) {
            return JsonServer::error($e->getError());
        }
        $result = UserLogic::setUserInfo($post);
        if($result === true) {
            return JsonServer::success('设置成功');
        }
        return JsonServer::error(UserLogic::getError());
    }



    /**
     * 财户流水
     */
    public function accountLog(){
        // 来源类型 1-余额 2-积分 3-成长值
        $source = $this->request->get('source', '');
        if(empty($source)) {
            return JsonServer::error('请传入来源类型');
        }
        // 变动类型
        $type = $this->request->get('type');
        $data = UserLogic::accountLog($this->user_id, $source,$type, $this->page_no, $this->page_size);
        return JsonServer::success('', $data);
    }

    /***
     * 会员中心 - 会员等级
     */
    public function getUserLevelInfo() {
        $data = UserLogic::getUserLevelInfo($this->user_id);
        return JsonServer::success('', $data);
    }


    /**
     * 成长值记录
     */
    public function getGrowthList()
    {
        $get = $this->request->get();
        $get['page_no'] = $this->page_no;
        $get['page_size'] = $this->page_size;
        $get['user_id'] = $this->user_id;
        $data = UserLogic::getGrowthList($get);
        return JsonServer::success('', $data);
    }

    /**
     * 我的钱包
     */
    public function myWallet(){
        $result = UserLogic::myWallet($this->user_id);
        if($result === false) {
            return JsonServer::error(UserLogic::getError());
        }
        return JsonServer::success('获取成功', $result);
    }

    /**
     * Notes: 更新微信的用户信息
     */
    public function setWechatInfo()
    {
        $data = $this->request->post();
        try{
            validate(SetWechatUserValidate::class)->check($data);
        }catch(ValidateException $e) {
            return JsonServer::error($e->getError());
        }
        $result = UserLogic::updateWechatInfo($this->user_id, $data);
        if($result === true) {
            return JsonServer::success('更新成功');
        }
        return JsonServer::error(UserLogic::getError());
    }

    //获取微信手机号
    public function getMobile()
    {
        try{
            $post = $this->request->post();
            $post['user_id'] = $this->user_id;
            validate(WechatMobileValidate::class)->check($post);
        }catch(ValidateException $e) {
            return JsonServer::error($e->getError());
        }
        $result = UserLogic::getMobileByMnp($post);
        if($result === false) {
            return JsonServer::error(UserLogic::getError());
        }
        return JsonServer::success('操作成功', [],1,1);
    }



    /**
     * Notes: 更换手机号 / 绑定手机号
     * @author 段誉(2021/6/23)
     * @return \think\response\Json
     */
    public function changeMobile()
    {
        $data = $this->request->post();
        $data['client'] = $this->client;
        $data['user_id'] = $this->user_id;
        if(isset($data['action']) && 'change' == $data['action']) {
            //变更手机号码
            $data['message_key'] = NoticeEnum::CHANGE_MOBILE_NOTICE;
            (new ChangeMobileValidate())->goCheck('', $data);
        } else {
            //绑定手机号码
            $data['message_key'] = NoticeEnum::BIND_MOBILE_NOTICE;
            (new ChangeMobileValidate())->goCheck('binding', $data);
        }
        $result = UserLogic::changeMobile($this->user_id, $data);
        if(false === $result) {
            return JsonServer::error(UserLogic::getError());
        }
        if(is_object($result)){
            $result = $result->toArray();
        }
        return JsonServer::success('操作成功',$result);
    }

    //我的粉丝
    public function fans()
    {
        $get = $this->request->get();
        $page = $this->request->get('page_no', $this->page_no);
        $size = $this->request->get('page_size', $this->page_size);
        return JsonServer::success('', UserLogic::fans($this->user_id, $get, $page, $size));
    }


    /**
     * @notes 用户聊天记录
     * @return \think\response\Json
     * @author 段誉
     * @date 2021/12/20 11:29
     */
    public function chatRecord()
    {
        $shop_id = $this->request->get('shop_id/d', 0);
        $result = UserLogic::getChatRecord($this->user_id, $shop_id, $this->page_no, $this->page_size);
        return JsonServer::success('', $result);
    }

}