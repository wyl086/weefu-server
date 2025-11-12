<?php


namespace app\admin\controller\distribution;


use app\admin\logic\distribution\ApplyLogic;
use app\common\basics\AdminBase;
use app\common\server\JsonServer;
use think\facade\View;

class Apply extends AdminBase
{
    /**
     * @Notes: 分销申请列表
     * @Author: 张无忌
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $lists = ApplyLogic::lists($get);
            return JsonServer::success('获取成功', $lists);
        }

        return view();
    }

    /**
     * @Notes: 分销申请详细
     * @Author: 张无忌
     */
    public function detail()
    {
        $id = $this->request->get('id');
        View::assign('detail', ApplyLogic::detail($id));
        return view();
    }

    /**
     * @Notes: 审核分销申请
     * @Author: 张无忌
     */
    public function audit()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $res = ApplyLogic::audit($post);
            if ($res === false) {
                $message = ApplyLogic::getError() ?: '审核失败';
                return JsonServer::error($message);
            }
            return JsonServer::success('审核成功');
        }

        return view();
    }
}