<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\admin\controller;

use app\admin\validate\ExpressValidate;
use app\common\basics\AdminBase;
use app\common\server\ConfigServer;
use app\common\logic\ExpressLogic;
use app\common\server\JsonServer;
use think\db;
use think\exception\ValidateException;

class Express extends AdminBase
{
    /**
     * lists
     * @return mixed
     * @throws \think\exception\DbException
     * @throws db\exception\DataNotFoundException
     * @throws db\exception\ModelNotFoundException
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            return JsonServer::error('', ExpressLogic::lists($get));
        }
    }

    /**
     * 添加
     * @return mixed
     */
    public function add()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $post['del'] = 0;
            try {
                validate(ExpressValidate::class)->scene('add')->check($post);
                $result = ExpressLogic::addExpress($post);
                if ($result) {
                    return JsonServer::success('添加成功');
                }
                return JsonServer::error($result);
            } catch (ValidateException $e) {
                return JsonServer::error($e->getMessage());
            }
        }
        return view();
    }

    /**
     * 编辑
     * @param $id
     * @return mixed
     */
    public function edit($id)
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $post['del'] = 0;
            try {
                validate(ExpressValidate::class)->scene('edit')->check($post);
                $result = ExpressLogic::editExpress($post);
                if ($result) {
                    return JsonServer::success('修改成功');
                }
                return JsonServer::error($result);
            } catch (ValidateException $e) {
                return JsonServer::error($e->getMessage());
            }
        }
        return view('', [
            'info' => ExpressLogic::info($id)
        ]);
    }


    /**
     * 删除
     * @param $delData
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function del($delData)
    {
        if ($this->request->isAjax()) {
            $result = ExpressLogic::delExpress($delData);
            if ($result) {
                return JsonServer::success('删除成功');
            }
            return JsonServer::error('删除失败');
        }
    }

    //查询配置
    public function setExpress()
    {
        $post = $this->request->post();
        if ($post) {
            ConfigServer::set('express', 'way', $post['way']);

            ConfigServer::set('kd100', 'appkey', $post['kd100_appkey']);
            ConfigServer::set('kd100', 'appsecret', $post['kd100_customer']);

            ConfigServer::set('kdniao', 'appkey', $post['kdniao_appkey']);
            ConfigServer::set('kdniao', 'appsecret', $post['kdniao_ebussinessid']);
            ConfigServer::set('kdniao', 'type', $post['kdniao_type']);
        }
        return JsonServer::success('操作成功');
    }
}