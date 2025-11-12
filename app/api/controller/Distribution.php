<?php
namespace app\api\controller;

use app\api\logic\DistributionLogic;
use app\common\basics\Api;
use app\common\server\JsonServer;
use think\exception\ValidateException;
use app\api\validate\DistributionValidate;
use app\common\model\user\User;
use think\response\Json;
use app\common\model\distribution\Distribution as DistributionModel;

class Distribution extends Api
{
    public $like_not_need_login = ['fixAncestorRelation'];
    /**
     * 申请分销会员
     */
    public function apply()
    {
        if($this->request->isPost()){
            $post = $this->request->post();
            $post['user_id'] = $this->user_id;
            try{
                validate(DistributionValidate::class)->scene('apply')->check($post);
            }catch(ValidateException $e) {
                return JsonServer::error($e->getError());
            }
            $result = DistributionLogic::apply($post);
            if($result) {
                return JsonServer::success('申请成功');
            }
            return JsonServer::error('申请失败');
        }else{
            return JsonServer::error('请求方式错误');
        }
    }

    /**
     * 判断是否为分销会员
     */
    public function check()
    {
        $distribution = DistributionModel::where('user_id', $this->user_id)->findOrEmpty()->toArray();
        if (!empty($distribution) && $distribution['is_distribution'] == 1) {
            return JsonServer::success('分销会员', [], 10001);
        }
        return JsonServer::success('非分销会员', [], 20001);
    }

    /**
     * 最新分销申请详情
     */
    public function applyDetail()
    {
        return JsonServer::success('获取成功', DistributionLogic::applyDetail($this->user_id));
    }

    /**
     * 分销主页
     */
    public function index()
    {
        return JsonServer::success('获取成功', DistributionLogic::index($this->user_id));
    }

    /**
     * 填写邀请码
     */
    public function code()
    {
        $code = $this->request->post('code');
        $data = [
            'user_id' => $this->user_id,
            'code' => $code,
        ];

        try{
            validate(DistributionValidate::class)->scene('code')->check($data);
        }catch(ValidateException $e){
            return JsonServer::error($e->getError(), [], 0, 0);
        }
        $result = DistributionLogic::code($data);
        if($result) {
            return JsonServer::success('绑定上级成功');
        }
        return JsonServer::error(DistributionLogic::getError(), [], 0, 0);
    }

    /**
     * 分销订单
     */
    public function order()
    {
        $get = $this->request->get();
        $get['user_id'] = $this->user_id;
        $get['page_no'] = $this->page_no;
        $get['page_size'] = $this->page_size;
        return JsonServer::success('获取成功', DistributionLogic::order($get));
    }

    /**
     * 月度账单
     */
    public function monthBill()
    {
        $get = $this->request->get();
        $get['page_no'] = $this->page_no;
        $get['page_size'] = $this->page_size;
        $get['user_id'] = $this->user_id;
        return JsonServer::success('获取成功', DistributionLogic::monthBill($get));
    }

    /**
     * 月度账单明细
     */
    public function monthDetail()
    {
        $get = $this->request->get();
        if(!isset($get['year'])) {
            return JsonServer::error('年份参数不存在');
        }
        if(!isset($get['month'])) {
            return JsonServer::error('月份参数不存在');
        }
        $get['page_no'] = $this->page_no;
        $get['page_size'] = $this->page_size;
        $get['user_id'] = $this->user_id;
        return JsonServer::success('获取成功', DistributionLogic::monthDetail($get));
    }

    /**
     * 自身及上级信息
     */
    public function myLeader()
    {
        return JsonServer::success('获取成功', DistributionLogic::myLeader($this->user_id));
    }

    /**
     * @Notes: 佣金明细
     * @Author: 张无忌
     */
    public function commission()
    {
        $get = $this->request->get();
        $lists = DistributionLogic::commission($get, $this->user_id);
        if ($lists === false) {
            $message = DistributionLogic::getError() ?: '获取失败';
            return JsonServer::error($message);
        }

        return JsonServer::success('获取成功', $lists);
    }

    /**
     * 修复旧的关系链
     */
    public function fixAncestorRelation()
    {
        $result = DistributionLogic::fixAncestorRelation();
        if ($result) {
            return JsonServer::success('修复成功');
        }
        return JsonServer::error(DistributionLogic::getError());
    }

    /**
     * @notes 获取背景海报
     * @return Json
     * @author cjhao
     * @date 2021/11/29 11:35
     */
    public function getPoster()
    {
        $result = DistributionLogic::getPoster();
        return JsonServer::success('',$result);
    }

}