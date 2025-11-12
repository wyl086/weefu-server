<?php


namespace app\shop\controller\team;


use app\common\basics\ShopBase;
use app\common\server\JsonServer;
use app\shop\logic\team\ActivityLogic;
use app\shop\validate\TeamValidate;
use think\facade\View;

class Activity extends ShopBase
{
    /**
     * @Notes: 拼团活动列表
     * @Author: 张无忌
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $lists = ActivityLogic::lists($get, $this->shop_id);
            return JsonServer::success('获取成功', $lists);
        }

        View::assign('statistics', ActivityLogic::statistics($this->shop_id));
        return view();
    }

    /**
     * @Notes: 选择拼团商品
     * @Author: 张无忌
     */
    public function select()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $lists = ActivityLogic::select($get, $this->shop_id);
            return JsonServer::success('获取成功', $lists);
        }

        return view();
    }

    /**
     * @Notes: 数据统计
     * @Author: 张无忌
     */
    public function statistics()
    {
        if ($this->request->isAjax()) {
            $detail = ActivityLogic::statistics($this->shop_id);
            return JsonServer::success('获取成功', $detail);
        }
        return JsonServer::error('异常');
    }

    /**
     * @Notes: 拼团活动详细
     * @Author: 张无忌
     */
    public function detail()
    {
        $id = $this->request->get('id');
        View::assign('detail', ActivityLogic::detail($id));
        return view();
    }

    /**
     * @Notes: 新增拼团活动
     * @Author: 张无忌
     */
    public function add()
    {
        if ($this->request->isAjax()) {
            (new TeamValidate())->goCheck('add');
            $post = $this->request->post();
            $lists = ActivityLogic::add($post, $this->shop_id);
            if ($lists === false) {
                $message = ActivityLogic::getError() ?: '新增失败';
                return JsonServer::error($message);
            }
            return JsonServer::success('新增成功');
        }

        return view();
    }

    /**
     * @Notes: 编辑拼团活动
     * @Author: 张无忌
     */
    public function edit()
    {
        if ($this->request->isAjax()) {
            (new TeamValidate())->goCheck('edit');
            $post = $this->request->post();
            $lists = ActivityLogic::edit($post, $this->shop_id);
            if ($lists === false) {
                $message = ActivityLogic::getError() ?: '编辑失败';
                return JsonServer::error($message);
            }
            return JsonServer::success('编辑成功');
        }

        $id = $this->request->get('id');
        View::assign('detail', ActivityLogic::detail($id));
        return view();
    }

    /**
     * @Notes: 删除拼团活动
     * @Author: 张无忌
     */
    public function del()
    {
        if ($this->request->isAjax()) {
            (new TeamValidate())->goCheck('id');
            $id = $this->request->post('id');
            $lists = ActivityLogic::del($id);
            if ($lists === false) {
                $message = ActivityLogic::getError() ?: '删除失败';
                return JsonServer::error($message);
            }
            return JsonServer::success('删除成功');
        }
        return JsonServer::error('请求异常');
    }

    /**
     * @Notes: 停止活动
     * @Author: 张无忌
     */
    public function stop()
    {
        if ($this->request->isAjax()) {
            (new TeamValidate())->goCheck('id');
            $id = $this->request->post('id');
            $lists = ActivityLogic::stop($id);
            if ($lists === false) {
                $message = ActivityLogic::getError() ?: '停止失败';
                return JsonServer::error($message);
            }
            return JsonServer::success('停止成功');
        }
        return JsonServer::error('请求异常');
    }

    /**
     * @Notes: 开启拼团活动
     * @Author: 张无忌
     */
    public function open()
    {
        if ($this->request->isAjax()) {
            $id = $this->request->post('id');
            $lists = ActivityLogic::open($id);
            if ($lists === false) {
                $message = ActivityLogic::getError() ?: '开启失败';
                return JsonServer::error($message);
            }
            return JsonServer::success('开启成功');
        }
        return JsonServer::error('请求异常');
    }
}