<?php


namespace app\admin\controller;


use app\admin\logic\FootprintLogic;
use app\common\basics\AdminBase;
use app\common\server\ConfigServer;
use app\common\server\JsonServer;
use think\facade\View;

class Footprint extends AdminBase
{
    /**
     * @Notes: 足迹气泡配置页
     * @Author: 张无忌
     * @return \think\response\View
     */
    public function index()
    {
        $config['footprint_status']   = ConfigServer::get('footprint','footprint_status',0);
        $config['footprint_duration'] = ConfigServer::get('footprint','footprint_duration',60);
        View::assign('config', $config);
        View::assign('footprint', FootprintLogic::lists());
        return view();
    }

    /**
     * @Notes: 编辑足迹气泡
     * @Author: 张无忌
     */
    public function edit()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $result = FootprintLogic::edit($post);
            if ($result === false) {
                $message = FootprintLogic::getError() ?: '编辑失败';
                return JsonServer::error($message);
            }
            return JsonServer::success('编辑成功');
        }

        $id = $this->request->get('id');
        View::assign('detail', FootprintLogic::detail($id));
        return view();
    }

    /**
     * @Notes: 设置足迹
     * @Author: 张无忌
     */
    public function set()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $result = FootprintLogic::set($post);
            if ($result === false) {
                $message = FootprintLogic::getError() ?: '设置失败';
                return JsonServer::error($message);
            }
            return JsonServer::success('设置成功');
        }
        return JsonServer::error('请求异常');
    }
}