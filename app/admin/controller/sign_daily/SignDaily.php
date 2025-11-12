<?php


namespace app\admin\controller\sign_daily;


use app\admin\logic\sign_daily\SignDailyLogic;
use app\admin\validate\sign_daily\SignDailyValidate;
use app\common\basics\AdminBase;
use app\common\server\JsonServer;

/**
 * 签到
 * Class SignDaily
 * @package app\admin\controller\shop
 */
class SignDaily extends AdminBase
{

    /**
     * @notes 连续签到列表
     * @return \think\response\Json|\think\response\View
     * @author 段誉
     * @date 2022/2/17 14:28
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $lists = SignDailyLogic::lists();
            return JsonServer::success('获取成功', $lists);
        }
        return view('sign_daily/lists', [
            'config' => SignDailyLogic::getSignRule()
        ]);
    }


    /**
     * @notes 签到记录
     * @return \think\response\Json|void
     * @author 段誉
     * @date 2022/2/17 14:29
     */
    public function record()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            return JsonServer::success('获取成功', SignDailyLogic::record($get));
        }
    }


    /**
     * @notes 每日签到奖励
     * @return \think\response\Json|void
     * @author 段誉
     * @date 2022/2/17 14:29
     */
    public function signRule()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $post['integral_status'] = isset($post['integral_status']) && $post['integral_status'] == 'on' ? 1 : 0;
            $post['growth_status'] = isset($post['growth_status']) && $post['growth_status'] == 'on' ? 1 : 0;
            (new SignDailyValidate())->goCheck('sign', $post);
            $result = SignDailyLogic::setSignRule($post);
            if (true === $result) {
                return JsonServer::success('设置成功');
            }
            return JsonServer::error(SignDailyLogic::getError() ?: '操作失败');
        }
    }


    /**
     * @notes 添加连续签到奖励
     * @return \think\response\Json|\think\response\View
     * @author 段誉
     * @date 2022/2/17 14:29
     */
    public function add()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $post['integral_status'] = isset($post['integral_status']) && $post['integral_status'] == 'on' ? 1 : 0;
            $post['growth_status'] = isset($post['growth_status']) && $post['growth_status'] == 'on' ? 1 : 0;
            (new SignDailyValidate())->goCheck('add', $post);
            SignDailyLogic::add($post);
            return JsonServer::success('添加成功');
        }
        return view('sign_daily/add');
    }


    /**
     * @notes 编辑连续签到奖励
     * @param $id
     * @return \think\response\Json|\think\response\View
     * @author 段誉
     * @date 2022/2/17 14:30
     */
    public function edit($id)
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $post['integral_status'] = isset($post['integral_status']) && $post['integral_status'] == 'on' ? 1 : 0;
            $post['growth_status'] = isset($post['growth_status']) && $post['growth_status'] == 'on' ? 1 : 0;
            (new SignDailyValidate())->goCheck('edit', $post);
            SignDailyLogic::edit($post);
            return JsonServer::success('修改成功');
        }
        return view('sign_daily/edit', ['info' => SignDailyLogic::getSignDaily($id)]);
    }


    /**
     * @notes 删除连续签到奖励
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/2/17 14:30
     */
    public function del()
    {
        if ($this->request->isAjax()) {
            $id = $this->request->post('id');
            SignDailyLogic::del($id);
            return JsonServer::success('删除成功');
        }
        return JsonServer::error('请求异常');
    }


}